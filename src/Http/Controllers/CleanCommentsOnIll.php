<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Models\Elasticsearch;
use App\Models\Name;
use App\Models\Name_ref;
use App\Models\Ref;

class CleanCommentsOnIll extends Controller

{
    public function moveCommentsOnIllustrations()
    {
        $query = Ref::where('comments_on_preface_postface', 'LIKE', '%illustr%')
            ->where('comments_on_preface_postface', 'NOT LIKE', '%Eingeleitet%')
            ->orderBy('comments_on_preface_postface');

        $query->where(function ($query) {
            $query->where(function ($query) {
                $query->where('comments_on_preface_postface', 'LIKE', '%illustr%');
                $query->where('comments_on_preface_postface', 'NOT LIKE', '%Eingeleitet%');
            });
        })->orWhere(function ($query) {
            $query->orWhere('comments_on_preface_postface', 'LIKE', '%Zeichnu%');
            $query->orWhere('comments_on_preface_postface', 'LIKE', '%Bilder%');
            $query->orWhere('comments_on_preface_postface', 'LIKE', '%Zeichnu%');
        });

        $refs = $query->get();

        foreach ($refs as $ref) {
            if (empty($ref->comments_on_illustrations) and !empty($ref->comments_on_preface_postface)) {
                $ref->comments_on_illustrations = $ref->comments_on_preface_postface;
                $ref->comments_on_preface_postface = '';
                $ref->save();
            }
        }


    }

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

    private function checkAndAddNames ($names,$key,$ref,$pos) {
        $inNames = Name::where('name', '=', $names[$key][0])
            ->where('first_name', '=', $names[$key][1])
            ->first();

        if (!isset($inNames)) {
            echo('Name: ' . $names[$key][0] . 'Vorname: '. $names[$key][1]) . '<br>';
            $nameId = $this->addNameToNames($names[$key][0], $names[$key][1]);
        } else {
            $nameId = $inNames->id;
        }

        $inNameRefs = Name_ref::where('ref_id', '=', $ref->id)
            ->where('name_id', '=', $nameId)
            ->where('role_id', '=', 8)
            ->first();

        if (!isset($inNameRefs)) {
            $newNameRefId = $this->addNameRoleAndPosition2NameRef($ref->id, $nameId, 8, $pos);
        }

    }

    public function moveNamesIllustrator()
    {
        $this->moveCommentsOnIllustrations();

        $arrayExeptions = [
            'de Boeck prints', 'grecos', 'afrika', 'zahlreichen zeichnungen', 'maravic',
            'beilage', 'u.a.', 'kapralik', 'rembrands', 'meulenhoff', 'Sturzkopf',
            '1 karte', 'vorlagen', 'von Dombrowski; 1 Titelbild', 'schreibschrift',
            'Holzschnitt-Vollbildern', '135 Tafeln', 'von F. Masereel und 8 Linolschnitten von P.P. Piech',
            'Amsterdam', 'Heymans; M. Heymans', 'mixtvision', 'Doeve; 1 Übersichtskarte',
            'Bohem Press', 'Borrebach und ', 'Stanleys', 'M. Müller; ill', 'Beuys', 'Lézin; J.C. Lézin',
            'vignetten', 'de Jonge; 1 Übersichtskarte; 1 Titelbild%', 'Doeve; 1 Tafel', 'Flor; 1 Titelbild',
            'Schulz', 'A.E.Inkle', 'artists', 'hardy and others', 'Timmermans and a portrait', 'Guide ill',
            'compositions', 'gallait', 'phot. de', 'plus de 1000 pl', 'autrice', 'des meilleurs dessinateurs',
            'Ill. De Beer', 'Couv. ill. de E. van Offel', 'Holzschnitt von Dirk van Gelder', 'Tresling &', 'ill. 32', 'ill. 5plts.',
            'ILL. F. KAFKA', 'ILL. J. KUIPER', 'ILL. K. LOEB', 'ILL. L. SHAFIR', 'ILL. P.G. RUETER', 'Ill. W. von Kaulbach und L. Hofmann',
            'Ill.: Haan, Linda de; Nijland, Stern', 'auteur', 'le texte d', ' Werken Rembrandts', 'Ill.: Schubert, Ingrid; Schubert, Dieter',
            'de Haan &amp; Stern Nijland', 'Gaab; 1 Titelbild', 'Popper; 12 Tafeln', 'Sch&auml;ffer', 'Sekr&egrave;ve', 'plans', 'map', 'auteur'
        ];

        $arrayElements = ['Mit Ill. von ', 'Ill. von ', ' von ', ' by ', 'ill. af ', 'ill. av ', 'ill. de ', 'ill. af ', 'ill. av ', 'ill. de ',
            'Avec dessins de ', 'Dessin de ', 'Dessins de ', 'Dessins par ', 'ill. par ',
            'Die Bilder zeichnete ', 'Il. af ', 'Ill. by ', 'Il. de ', 'Il. di ', 'Ill. di ', 'Ill. ', 'ILL. ', 'Il. ', 'Il.: ', 'Ill.: ',
            'Illustraties: ', 'Illustrasies: ', 'Illustrations de ', 'Illustrasies deur ', 'Illustr. av ', 'ill..: ', 'Ill. de ',
            'Teckn. av', 'Tegnet af ', 'Tegningen af ', 'Tegninger af ', 'Tegninger av ', 'Tegninger: ', 'Tek.: ', 'Tekeninge deur ',
            'Illustr. ', 'Illustration de ', 'illustrations: ', 'Illustrazioni de ', 'Illustré par ', 'Illustrée par ', 'Illustrées par ',
            'Illustreret af ', 'Illustrés par ', 'Illustriral ', 'Illustrovala ', 'Ilustr. de ', 'Ilustr. ', 'Ilustración ', 'Ilustraciones de ', 'Ilustraciones',
            'Ilustrationer: ', 'Ilustrations de ', 'Ilustrirao ', 'Bilder von '
        ];
        $arrayNoStart = [' von ', ' by '];
        $arraySpecialNames = ['Dominique und E. Robin', 'D. und I. Schubert', 'H. und M. Langenberg', 'H. und M. Mannhart',
            'R. und M. Rettich', 'I. und P. Zimmermann', 'Ingrid et Dieter Schubert', 'Ingrid en Dieter Schubert'
        ];

        foreach ($arrayElements as $Element) {

            $query = Ref::select('id', 'comments_on_illustrations');
            if (in_array($Element, $arrayNoStart)) {
                $query->where('comments_on_illustrations', 'LIKE', '%' . $Element . '%');
                //$query->whereIn('id',array('5633'));
                //$query->where('comments_on_illustrations', 'LIKE','% Ingrid et Dieter Schubert%');
            } else {
                $query->where('comments_on_illustrations', 'LIKE', $Element . '%');
                //$query->where('comments_on_illustrations', 'LIKE','% Ingrid et Dieter Schubert%');
                //$query->whereIn('id',array('5633'));
            }

            foreach ($arrayExeptions as $Exeption) {
                $query->where('comments_on_illustrations', 'NOT LIKE', '%' . $Exeption . '%');
            }

            $refs = $query->get();

            foreach ($refs as $ref) {

                $names = [];
                $nameIllustrator = [];

                $arrayIllustrations = explode(ucfirst($Element), ucfirst($ref->comments_on_illustrations), 2);

                if (empty($arrayIllustrations[0]) && empty($arrayIllustrations[1]))
                    continue;
                if (!empty($arrayIllustrations[0]) && strpos($arrayIllustrations[0],'Ill. ') !== false)
                    continue;

                if (isset ($arrayIllustrations[1])) {
                    if (strpos($arrayIllustrations[1], ' ') !== false) {
                        $nameIllustrator = explode(' ', trim($arrayIllustrations[1]), 2);
                    } else {
                        $nameIllustrator[0] = '';
                        $nameIllustrator[1] = $arrayIllustrations[1];
                    }

                } else {
                    $nameIllustrator[0] = '';
                    $nameIllustrator[1] = $arrayIllustrations[0];
                }

                if (strpos($nameIllustrator[0], ',') !== false) {
                    $name = rtrim($nameIllustrator[0], ',');
                    $nameIllustrator[0] = $nameIllustrator[1];
                    $nameIllustrator[1] = $name;
                }

                $names[0][1] = $nameIllustrator[0];

                //check if name illustrator exists and remove trailing dot
                if (isset($nameIllustrator[1])) {
                    if (strlen($nameIllustrator[1]) > 4)
                        $names[0][0] = rtrim($nameIllustrator[1], '.');
                    else
                        $names[0][0] = $nameIllustrator[1];
                }


                if (strpos($names[0][0], ' und ') !== false
                    && (isset($arrayIllustrations[1]) && !empty($arrayIllustrations[1]))) {

                    $arrayNames = explode(' ', $arrayIllustrations[1]);

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
                    && (isset($arrayIllustrations[1]) && !empty($arrayIllustrations[1]))
                    && (in_array($arrayIllustrations[1], $arraySpecialNames))) {

                    $arrayNames = explode(' ', $arrayIllustrations[1]);

                    $names[0][0] = $arrayNames[3];
                    $names[0][1] = $arrayNames[0];
                    $names[1][0] = $arrayNames[3];
                    $names[1][1] = $arrayNames[2];
                }


                $pos = 1;
                foreach ($names as $key => $name) {
                   if (strpos($names[$key][0],'Rien') === false
                       && strpos($names[$key][0],'van der Linden') === false
                       && strpos($names[$key][0],'author') === false
                       && strpos($names[$key][0],'Dierckx') === false
                       && strpos($names[$key][0],'Ambrus') === false
                       && strpos($names[$key][1],'Simone (foto') === false
                       && strpos($names[$key][0],'B.Horsthuis') === false
                       && strpos($names[$key][0],'regretté') === false
                       && strpos($names[$key][0],'Wilharm') === false
                       && strpos($names[$key][0],'Dendooven') === false
                       && $names[$key][1] != 'van'
                       && $names[$key][1] != 'Van'
                       && $names[$key][1] != 'v.'
                       && $names[$key][0] != 'port'
                       && $names[$key][0] != 'E.'
                       && $names[$key][0] != 'E'
                       && $names[$key][0] != 'F. Schäfer'
                       && $names[$key][0] != 'P. Weber'
                       && $names[$key][0] != 'J.'
                       && $names[$key][0] != 'G. B.)'
                   ) {
                       $this->checkAndAddNames($names,$key,$ref,$pos);
                       if (in_array(strtoupper($arrayIllustrations[0]), [strtoupper('Il.'), strtoupper('Illustrationen'),
                           strtoupper('ill.'), strtoupper('Ill.')
                       ])) {
                           $arrayIllustrations[0] = 'Ill.';
                       }

                       $ref->comments_on_illustrations = $arrayIllustrations[0];

                       $ref->save();
                       $pos++;
                   }
                }

            }
        }

        $arrayUpdateColumnsIll = ['Ill.', 'Il.', 'Ill.', 'ILL. ', 'Il.: ', 'Ill.: ', 'Illustrated', 'Illustrations', 'Illustration',
            'Illustraties', 'Illustriert', 'Illustr.', 'ill..: ', 'Illus.', 'Illustr.', 'Abb.', 'Afbb.', 'afbn.', 'Bilder', 'Buchschmuck',
            'Drawings', 'Farbillustrationen', 'Federzeichn.', 'Federzeichungen', 'ILL:', 'Mit Bildern', 'Mit Zeichnungen', 'Mit Illustrationen',
            'Mit Zeichn.', 'Pictures', 'Textillustrationen', 'Textzeichnungen', 'Innenillustrationen', 'Innenzeichnungen', 'Innenbilder'

        ];

        $updateQuery = Ref::where('comments_on_illustrations', '=', 'Ill.');

        foreach ($arrayUpdateColumnsIll as $update) {
            $updateQuery->orWhere('comments_on_illustrations', '=', $update);
        }

        $affectedRows = $updateQuery->update(array('comments_on_illustrations' => 'Ill.'));
    }
}
