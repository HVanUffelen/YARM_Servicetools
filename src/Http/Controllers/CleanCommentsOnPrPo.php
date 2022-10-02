<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Models\Elasticsearch;
use App\Models\Name;
use App\Models\Name_ref;
use App\Models\Ref;

class CleanCommentsOnPrPo extends Controller

{


    private function addNameRoleAndPosition2NameRef($ref_id, $name_id, $role_id, $position)
    {
        $newNameRef = new Name_ref();
        $newNameRef->ref_id = $ref_id;
        $newNameRef->name_id = $name_id;
        $newNameRef->position = $position;
        $newNameRef->role_id = $role_id;
        $newNameRef->save();
        return $newNameRef->id;
    }

    private function addNameToNames($name, $first_name)
    {
        $newName = new Name();
        $newName->name = $name;
        $newName->first_name = $first_name;
        $newName->save();
        return $newName->id;
    }

    private function checkAndAddNames($names, $key, $ref, $pos)
    {
        $inNames = Name::where('name', '=', $names[$key][0])
            ->where('first_name', '=', $names[$key][1])
            ->first();

        if (!isset($inNames)) {
            echo ('Name: ' . $names[$key][0] . 'Vorname: ' . $names[$key][1]) . '<br>';
            $nameId = $this->addNameToNames($names[$key][0], $names[$key][1]);
        } else {
            $nameId = $inNames->id;
        }

        $inNameRefs = Name_ref::where('ref_id', '=', $ref->id)
            ->where('name_id', '=', $nameId)
            ->where('role_id', '=', 9)
            ->first();

        if (!isset($inNameRefs)) {
            $newNameRefId = $this->addNameRoleAndPosition2NameRef($ref->id, $nameId, 9, $pos);
        }

    }

    public function moveNamesPrefacePostface()
    {
        $arrayExeptions = [
            'Gaëtan Picon; Nawoord: Jean Weisgerber', 'Jean Cassou; Introd.: Karel Jockheere',
            'Selección, introducción y notas por F. Carrasquer', 'Selectie, compilatie en inleiding van Bernáth István', 'With short introduction',
            'Mit notenWith short introduction', 'Kleine Selbstbiographie von F.T', 'Gerichtslaboratoriums des Justizministeriums', 'der Übersetzerin. Erläuterungen ',
            'State Institute for War Documentation; introduced by', 'Preface: Barycz, Henryk; Postface: Herbst, Stanisław', 'Huizinga; postface by Jiří Černý', 'Lebene (p. 497-498)',
            'Lékay; biographical data is written by László Maráz and Márton Mesterházi', 'M&ouml;hlmann, bevat ook een essay van Wiljan van den Akker', 'Mülfarth. Angabe der Unterschiede zu den flämischen Ausgaben',
            'Padel. Inl. en interview door Victor Schiferli (vert. door Paul Vincent)', 'poems ("Bevezető a Magyar Rádió Kilátó műsorában 1989. március 3-án elhangzott fordításokhoz")',
            'Heraus mit der Sprache', 'data about Huiznga', 'de Belder; J. Vercammen - kurze Angaben über die Autoren', 'Digest)', 'Essy von R.E. Schierenberg',
            'ff. rev. u. bearb. von Enrique Beck', 'Hillner. Mit biographischen Anmerkungen auf den Seiten 157-160', 'Hooker and an introduction by the translator',
            'Intr. By Lillian Faderman', 'Jonkheere; kurze Angaben zu den Autoren', 'Karrer; Vorwort von Jacques Maritain', 'Kirsch, Worterklärungen von Gea Oosting',
            'Lawrence. Afterword by E.M. Beekman', 'Meckel ; Bibliographie', 'Morgan Ayres and Adriaan Jacob Barnouw', 'Noble, postface de l’auteur', 'publisher, afterword by writer',
            'Rehs; Einführung von M. Gijsen', 'R. P.Deschaepdryver, S. J', 'Reichert; Bibliographie', 'Rops; Nawoord: Isabelle Rosselin-Bobulesco', 'Rüttgers; Anmerkungen',
            'Schneeweiß; Bio-bibliographische Notizen', 'Schönwiese; Bio-bibliographische Tafel', 'Schröder; Geleitwort von Prinz Philip', 'Schulte-Kemmingshausen; R. van Roosbroeck. Nachwort des Verlages',
            'Seghers; Nachwort von E. Antoni', 'Siegel; Nachwort von Hans Ester', 'Sierman. Vorwort von A. Jongstra', 'Simons. Nachwort von F. Wippermann', 'Sommerfeld and note by translator',
            'Thelen. Mit einer Einführung von Orlando Grossegesse', 'translator; postface by the author]; [Notes at the end of the book]', 'Troß; biographisch-literarische Notizen', 'the translator',
            'v. Schenck. Mit einem Vorwort', 'van de Walle: Cyriel Verschaeve : Flanderns geistiger Führer', 'van Gelder. Einführung von C.W. Sangster-Warnaars',
            'Vidrányi; postface by Gábor Klaniczay', 'Vincent, afterword by Luk De Vos', 'von Vossole. Vorwort von Anton van Wilderode', 'Gerard; Jacobs, Karl', 'the writer',
            'author (Zurich, 1942), Preface to the English edition by the author (Chicago, 1949)', 'Beekman. Introduction by Gerrit Borgers', 'Bilska [Enthält auch das 1. Nachwort von Max Brod - 1927]',
            'Bloem; Bio-bibliographische Notizen', 'Bloy; Nachwort von H. v. d. Mark', 'Canetti, Nachwort von Helmut Göbel', 'Combecher; zum Geleit F. Hofmann', 'Coster. Mit Beiträgen von P.C. Boutens',
            'Csollány. Mit einem Nachw. von Hugo Brems', 'de Jong; J. Hildebrandt', 'Elsschot; Nachwort von C. ter Haar', 'Florin, über Hugo Claus von Per Holmer',
            'H. Carpenter, G. Kalff and translator', 'Heissenbüttel; Nachwort von L. Kunz', 'Holm, Naw. Per Holmer', 'Hermanowski; Typenregister', '>N.','Huizinga written for the Czech','Simond','Préface de Willemien B. De Vries','Magnus Enzensberger'
        ];
        $arrayElements = [
            'preface by ', ' par ', 'Préface de ', 'Préface de ', ' von ', 'preface de ', 'Préface: ', 'Preface: ', ' by ', 'voorw. ', 'voorw.: ', ' af ', ' av ', 'Préf. de '
        ];
        $arrayNoStart = [' von ', ' par ', ' af ', ' av ', ' by '];
        $arraySpecialNames = ['Dominique und E. Robin', 'D. und I. Schubert', 'H. und M. Langenberg', 'H. und M. Mannhart',
            'R. und M. Rettich', 'I. und P. Zimmermann', 'Ingrid et Dieter Schubert', 'Ingrid en Dieter Schubert'
        ];

        foreach ($arrayElements as $Element) {

            $query = Ref::select('id', 'comments_on_preface_postface');
            if (in_array($Element, $arrayNoStart)) {
                $query->where('comments_on_preface_postface', 'LIKE', '%' . $Element . '%');
                //$query->whereIn('id',array('42112'));
                //$query->where('comments_on_illustrations', 'LIKE','% Ingrid et Dieter Schubert%');
            } else {
                $query->where('comments_on_preface_postface', 'LIKE', $Element . '%');
                //$query->where('comments_on_illustrations', 'LIKE','% Ingrid et Dieter Schubert%');
                //$query->whereIn('id',array('42112'));
            }

            foreach ($arrayExeptions as $Exeption) {
                $query->where('comments_on_preface_postface', 'NOT LIKE', '%' . $Exeption . '%');
            }

            $refs = $query->get();

            foreach ($refs as $ref) {

                $names = [];
                $namePrefacePostface = [];

                $arrayPrefacePostface = explode(ucfirst($Element), ucfirst($ref->comments_on_preface_postface), 2);

                if (empty($arrayPrefacePostface[0]) && empty($arrayPrefacePostface[1]))
                    continue;
                if (!empty($arrayPrefacePostface[0]) && strpos('Ill. ', $arrayPrefacePostface[0]) !== false)
                    continue;

                if (isset ($arrayPrefacePostface[1])) {
                    if (strpos($arrayPrefacePostface[1], ' ') !== false) {
                        $namePrefacePostface = explode(' ', trim($arrayPrefacePostface[1]), 2);
                    } else {
                        $namePrefacePostface[0] = '';
                        $namePrefacePostface[1] = $arrayPrefacePostface[1];
                    }

                } else {
                    $namePrefacePostface[0] = '';
                    $namePrefacePostface[1] = $arrayPrefacePostface[0];
                }

                if (strpos($namePrefacePostface[0], ',') !== false) {
                    $name = rtrim($namePrefacePostface[0], ',');
                    $namePrefacePostface[0] = $namePrefacePostface[1];
                    $namePrefacePostface[1] = $name;
                }

                $names[0][1] = $namePrefacePostface[0];

                //check if name illustrator exists and remove trailing dot
                if (isset($namePrefacePostface[1])) {
                    if (strlen($namePrefacePostface[1]) > 4)
                        $names[0][0] = rtrim($namePrefacePostface[1], '.');
                    else
                        $names[0][0] = $namePrefacePostface[1];
                }


                if (strpos($names[0][0], ' und ') !== false
                    && (isset($arrayPrefacePostface[1]) && !empty($arrayPrefacePostface[1]))) {

                    $arrayNames = explode(' ', $arrayPrefacePostface[1]);

                    if (count($arrayNames) == 4) {
                        $names[0][0] = $arrayNames[3];
                        $names[0][1] = $arrayNames[0];
                        $names[1][0] = $arrayNames[3];
                        $names[1][1] = $arrayNames[2];
                    } elseif (count($arrayNames) == 5) {
                        $names[0][0] = $arrayNames[1];
                        $names[0][1] = $arrayNames[0];
                        $names[1][0] = $arrayNames[4];
                        $names[1][1] = $arrayNames[3];
                    } else {
                        continue;
                    }
                } elseif ((strpos($names[0][0], 'und ') !== false
                        or strpos($names[0][0], 'et ') !== false
                        or strpos($names[0][0], 'en ') !== false)
                    && (isset($arrayPrefacePostface[1]) && !empty($arrayPrefacePostface[1]))
                    && (in_array($arrayPrefacePostface[1], $arraySpecialNames))) {

                    $arrayNames = explode(' ', $arrayPrefacePostface[1]);

                    $names[0][0] = $arrayNames[3];
                    $names[0][1] = $arrayNames[0];
                    $names[1][0] = $arrayNames[3];
                    $names[1][1] = $arrayNames[2];
                }


                $pos = 1;
                foreach ($names as $key => $name) {
                    if (strpos($names[$key][0], 'Voborský') === false
                        && strpos($names[$key][1], 'magický') === false
                        && strpos($names[$key][0], 'and Kadlec') === false
                        && strpos($names[$key][0], 'H.Wielek') === false
                        && strpos($names[$key][0], 'C.J.E.DinauxV') === false
                        && strpos($names[$key][1], 'Dr.Med.') === false
                        && strpos($names[$key][0], 'G. Gaarland') === false
                        && strpos($names[$key][0], 'G. Gaarlandt') === false
                        && strpos($names[$key][0], 'R. von Salis') === false
                        && strpos($names[$key][0], 'Meng u. Elisabeth Rotten') === false
                        && strpos($names[$key][0], 'Hugo Gryn') === false
                        && strpos($names[$key][0], 'bevat ook') === false
                        && $names[$key][1] != 'M. Beekman'
                        && $names[$key][1] != 'Willinger'
                        && $names[$key][0] != 'S. Haasse'
                        && $names[$key][0] != 'J. Thoms'
                        && $names[$key][0] != 'M. Hinkle'
                        && $names[$key][0] != 'Markus Hübner'
                        && $names[$key][0] != 'H. Wolff'
                        && $names[$key][0] != 'Franz'
                        && $names[$key][0] != 'S. Hartman'
                        && $names[$key][0] != 'Jan van Houtum'
                        && $names[$key][0] != 'H. Denier van der Gon'
                        && $names[$key][0] != 'József'
                        && $names[$key][0] != 'Papen'
                        && $names[$key][0] != 'Obst'
                        && $names[$key][0] != 'B. De Vries'
                        && $names[$key][0] != 'Karl Heiland'
                        && $names[$key][0] != 'Carel de Rover'
                        && $names[$key][0] != 'Brod]'
                        && $names[$key][0] != 'Essen'
                        && $names[$key][0] != 'Marcel'
                        && $names[$key][0] != 'HerausgebernG. B.)'
                    ) {
                        $this->checkAndAddNames($names, $key, $ref, $pos);
                        /*if (in_array(strtoupper($arrayPrefacePostface[0]), [strtoupper('Il.'), strtoupper('Illustrationen'),
                            strtoupper('ill.'), strtoupper('Ill.')
                        ])) {
                            $arrayIllustrations[0] = 'Ill.';
                        }*/

                        $ref->comments_on_preface_postface = $arrayPrefacePostface[0];

                        $ref->save();
                        $pos++;
                    }
                }
            }
        }

        $refs = Ref::where('comments_on_preface_postface', 'like', '%uit het %')->get();
        foreach ($refs as $ref) {
            $ref->comments_on_translation = $ref->comments_on_preface_postface;
            $ref->comments_on_preface_postface = '';
            $ref->save();
        }
        /*$refs = Ref::where('comments_on_preface_postface', '=', 'Ill.')->get();
        foreach ($refs as $ref) {
            $ref->comments_on_translation = $ref->comments_on_preface_postface;
            $ref->comments_on_preface_postface = '';
            $ref->save();
        }*/
    }


}
