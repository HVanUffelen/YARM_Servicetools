<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Http\Controllers\ImportController;
use App\Models\Elasticsearch;
use App\Models\Ref;
use Illuminate\Support\Facades\Storage;
use const App\Http\Controllers\Admin\PHP_EOL;

class convertDataLFtoRIS extends Controller

{

    function import_dletterenfonds()
    {

        $columns = "Blank;Auteur;Som;Nationaliteit;ID;Titel;Uitgever;Plaats;Vertalers;Illustratoren;Jaar;OT_Titel;OT_Uitgever;OT_Plaats;Genre;Sorteernaam;OT_Jaar;Commentaar;NLF;VFL;Subsidiegever;Bindwijze;Status;Taal;Cont_jaargang;Cont_naam;Cont_pagina;Reeks;Heruitgave;";
        $columnsArray = explode(';', $columns);

        $languageTrans = $this->createDictionaryLT();

        $dataArray = $this->getDataFromJson($languageTrans);

        $languages = ["Czech", "English", "French", "German", "Hungarian", "Italian", "Polish", "Romanian", "Serbian", "Serbo-Croatian", "Slovak", "Slovenian", "Swedish"];
        //$languages = ["Italian"];

        $RISData = '';
        $RISDataPresent = '';
        $i = 1;
        $p = 0;

        foreach ($languages as $language) {
            foreach ($dataArray as $dataSet) {
                if ($dataSet[22] == 'Gepubliceerd'
                    && !empty($dataSet[4])
                    && $dataSet[23] == $language
                    && $dataSet[5] != 'Intern'
                    && $dataSet[16] != 'nog niet verschenen'
                    && strpos($dataSet[16], 'Ongepubliceerde uitgave') == false
                ) {

                    //check if title is already in DB
                    $title = $dataSet[5];
                    $year = $dataSet[10];
                    if ($dataSet[28] == 'FALSCH')
                        $edition = 1;
                    else
                        $edition = '';

                    $check = $this->checkIfAlreadyPresent($title, $year, $edition, $dataSet);

                    if (!$check) {
                        $RISData .= $this->makeRisData($dataSet, $language, $edition);
                        $i++;
                    } else {
                        $RISDataPresent .= $this->makeRisData($dataSet, $language, $edition);
                        $p++;
                    }

                }
            }

            //Storage::put($language . 'import.ris', $RISData);
            //Storage::put($language . 'present.ris', $RISDataPresent);
            //echo($RISData);
            $RISData = '';
            $RISDataPresent = '';
        }
        echo('Exported: ' . $i . ' - ');
        echo('Present: ' . $p);
    }

    private function createDictionaryLT()
    {

        $languageTrans = [
            'German' => 'Duits',
            'English' => 'Engels',
            'French' => 'Frans',
            'Serbo-Croatian' => 'Servokroatisch',
            'Swedish' => 'Zweeds',
            'Italian' => 'Italiaans',
            'Polish' => 'Pools',
            'Romanian' => 'Roemeens',
            'Hungarian' => 'Hongaars',
            'Serbian' => 'Servisch',
            'Czech' => 'Tsjechisch',
            'Slovak' => 'Slowaaks',
            'Slovenian' => 'Sloweens',
            //'Croatian' => 'Croatisch'
        ];
        return $languageTrans;
    }

    private function getDataFromJson($languageTrans)
    {

        if (Storage::exists('/' . 'DataLFonds.json')) {
            $dataJson = Storage::get('/' . 'DataLFonds.json');
            $dataArray1 = json_decode(($dataJson));
        }

        $name = '';
        $language = '';

        $dataArray = [];
        foreach ($dataArray1 as $dataSet) {
            if ($dataSet[4] == '') {
                $name = '';
                continue;
            } else {
                if ($dataSet[1] != '') {
                    $name = $dataSet[1];
                    $dataSet[1] = $name;
                } else
                    $dataSet[1] = $name;

                if ($dataSet[23] != '') {
                    $language = array_search($dataSet[23], $languageTrans);
                    $dataSet[23] = $language;
                } else {
                    $dataSet[23] = 'Unknown';
                }

                $dataArray[] = $dataSet;
            }
        }
        return $dataArray;
    }

    private function checkIfAlreadyPresent($title, $year, $edition, $dataSet)
    {
        $query = Ref::where('title', '=', $title);
        $query->where('year', '=', $year);
        $check = $query->count();
        if ($check == 0)
            return null;
        else {
            if ($check == 1) {
                $ref = $query->first();
                $source_library = $ref->source_library;
                if ($check == 1 && !empty($source_library)) {
                    if (strpos($source_library, $dataSet[4]) !== false)
                        return true;
                    else {
                        $this->updateRef($ref, $dataSet, false);
                        return true;
                    }
                } else
                    if ($check == 1 && empty($source_library)) {
                        $ref->source_library = 'Data imported from Database Nederlands Letterenfonds';
                        $this->updateRef($ref, $dataSet, false);
                    }
            } else if ($check > 1) {
                if ($edition == 1)
                    $query->where('edition', '=', 1);
                $query->orderBy('edition', 'asc');
                $check = $query->count();

                if ($check == 0)
                    return null;

                $ref = $query->first();
                $source_library = $ref->source_library;
                if (!empty($source_library)) {
                    if (strpos($source_library, $dataSet[4]) !== false)
                        return true;
                    else {
                        $this->updateRef($ref, $dataSet, true);
                        return true;
                    }
                } else
                    if (empty($source_library)) {
                        $ref->source_library = 'Data imported from Database Nederlands Letterenfonds';
                        $this->updateRef($ref, $dataSet, true);
                    }
            }
            return true;
        }


    }

    private
    function updateRef($ref, $dataSet, $imported)
    {
        $ref->source_library .= ' - ID_NLF = ' . $dataSet[4];
        if (!empty($dataSet[18]) && $dataSet[18] == 'WAHR') {
            if (strpos($ref->notes, 'Present in Library LPF') === false || strpos($ref->notes, 'Present in Library NLF') === false) {
                $ref->notes = $ref->notes . ' - Present in Library NLF';
                if ($imported)
                    $ref->status_id = 3;
            }

        }
        if (!empty($dataSet[19]) && $dataSet[19] == 'WAHR') {
            if (strpos($ref->notes, 'Present in Library VFL') === false) {
                $ref->notes = $ref->notes . ' - Present in Library VFL';
                if ($imported)
                    $ref->status_id = 3;

            }

        }
        if (!empty($dataSet[20]) && strpos($ref->keywords, 'Subsidy: ' . $dataSet[20]) === false) {
            $ref->keywords .= '; Subsidy: ' . $dataSet[20];
            if ($imported)
                $ref->status_id = 3;
        }

        //add ref to DlitGroup if not already done
        ImportController::addIdToDLITGroup($ref);
        $ref->save();
    }

    private
    function splitNames($data)
    {
        if (strpos($data, ' ') != false) {
            $nameArray = $array = explode(' ', $data, 2);
            $name = trim($nameArray[1]) . ', ' . trim($nameArray[0]);
        } else
            $name = $data;
        return $name;

    }

    private
    function makeRisData($dataSet, $language, $edition)
    {


        $CrLf = PHP_EOL;
        $RISData = '';
        $authors = explode(', ', $dataSet[1]);
        $translators = explode(', ', $dataSet[8]);
        $illustrators = explode(', ', $dataSet[9]);

        if (!empty($dataSet[25]))
            $RISData .= 'TY  - JOUR' . $CrLf;
        else
            $RISData .= 'TY  - BOOK' . $CrLf;

        $RISData .= 'AV  - Data imported from Database Nederlands Letterenfonds. (ID_NLF = ' . $dataSet[4] . ')' . $CrLf;

        foreach ($authors as $author) {
            if ($author != 'div. auteurs') {
                $author = $this->splitNames($author);
                $RISData .= 'AU  - ' . $author . $CrLf;
            }

        }
        foreach ($translators as $translator) {
            if (!empty($translator && $translator != 'n.n.')) {
                $translator = $this->splitNames($translator);
                $RISData .= 'TR  - ' . $translator . $CrLf;
            }

        }
        foreach ($illustrators as $illustrator) {
            if (!empty($illustrator && $illustrator != 'n.n.')) {
                $illustrator = $this->splitNames($illustrator);
                $RISData .= 'IL  - ' . $illustrator . $CrLf;
            }
        }
        /*if ($dataSet[5] == '(Hair, it\'s a family affair!)')
            echo('xxx');*/
        if (!empty($dataSet[5])) $RISData .= 'TI  - ' . $dataSet[5] . $CrLf;
        if (!empty($dataSet[23])) $RISData .= 'LA  - ' . $dataSet[23] . $CrLf;
        $RISData .= 'LA2  - ' . 'Dutch' . $CrLf;

        if (!empty($dataSet[10])) {
            if (is_numeric($dataSet[10]) && $dataSet[10] != 's.a.') {
                $RISData .= 'Y1  - ' . $dataSet[10] . $CrLf;
            }
        }


        if (!empty($dataSet[6])) $RISData .= 'PB  - ' . $dataSet[6] . $CrLf;
        if (!empty($dataSet[7]) && $dataSet[7] != 's.l.') $RISData .= 'CY  - ' . $dataSet[7] . $CrLf;

        if (!empty($edition)) $RISData .= 'ET  - ' . $edition . $CrLf;

        if (!empty($dataSet[11])) $RISData .= 'OP  - ' . $dataSet[11] . $CrLf;

        if (!empty($dataSet[16])) {
            if (is_numeric($dataSet[16]))
                $RISData .= 'C3  - ' . $dataSet[16] . $CrLf;
            else {
                if (strpos($dataSet[16], 'ca. ') !== false) {
                    $RISData .= 'C3  - ' . str_replace('ca. ', '', $dataSet[16]) . $CrLf;
                    $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                } else if (strpos($dataSet[16], '-') !== false) {
                    if (is_numeric(substr($dataSet[16], 0, 4)))
                        $RISData .= 'C3  - ' . substr($dataSet[16], 0, 4) . $CrLf;
                    $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                } else if (strpos($dataSet[16], '; ') !== false) {
                    $RISData .= 'C3  - ' . substr($dataSet[16], 0, 4) . $CrLf;
                    $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                } else if (strpos($dataSet[16], 'e.v.') !== false) {
                    $RISData .= 'C3  - ' . str_replace(' e.v.', '', $dataSet[16]) . $CrLf;
                    $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                } else if (strpos($dataSet[16], 's.a.') !== false) {
                    $RISData .= 'C3  - ' . str_replace('s.a.', '', $dataSet[16]) . $CrLf;
                    $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                } else {
                    if (!is_numeric($dataSet[16]))
                        $RISData .= 'N1  - ' . 'Year original publicaton (NLF) = ' . $dataSet[16] . $CrLf;
                    else
                        $RISData .= 'C3  - ' . $dataSet[16] . $CrLf;
                }

            }

        }


        if (!empty($dataSet[12]) && $dataSet[12] != 'n.n.') $RISData .= 'C5  - ' . $dataSet[12] . $CrLf;
        if (!empty($dataSet[13]) && $dataSet[13] != 's.l.') $RISData .= 'CY1  - ' . $dataSet[13] . $CrLf;

        if (!empty($dataSet[21])) $RISData .= 'U2  - ' . $dataSet[21] . $CrLf;

        if (!empty($dataSet[25])) $RISData .= 'JO  - ' . $dataSet[25] . $CrLf;
        if (!empty($dataSet[24])) $RISData .= 'VL  - ' . $dataSet[24] . $CrLf;
        if (!empty($dataSet[26])) $RISData .= 'SP  - ' . $dataSet[26] . $CrLf;

        if (!empty($dataSet[27])) $RISData .= 'C8  - ' . $dataSet[27] . $CrLf;

        if (!empty($dataSet[14])) {
            if (strpos($dataSet[14], 'Poëzie') !== false) {
                $RISData .= 'M3  - ' . 'Poetry' . $CrLf; //Todo split genre to M3
                $RISData .= 'KW  - ' . $dataSet[14] . $CrLf; //Todo split genre to M3
            } else if (strpos($dataSet[14], 'Toneelstuk') !== false) {
                $RISData .= 'M3  - ' . 'Drama' . $CrLf; //Todo split genre to M3
                $RISData .= 'KW  - ' . $dataSet[14] . $CrLf; //Todo split genre to M3
            } else if (strpos($dataSet[14], 'Fictie') !== false || strpos($dataSet[14], 'Non-fictie') !== false
                || strpos($dataSet[14], 'Reisliteratuur') !== false) {
                $RISData .= 'M3  - ' . 'Prose' . $CrLf; //Todo split genre to M3
                $RISData .= 'KW  - ' . $dataSet[14] . $CrLf; //Todo split genre to M3
            } else if (strpos($dataSet[14], 'Kinder- en jeugdliteratuur') !== false) {
                $RISData .= 'M3  - ' . 'Child and Youth Literature' . $CrLf; //Todo split genre to M3
                $RISData .= 'KW  - ' . $dataSet[14] . $CrLf; //Todo split genre to M3
            } else
                $RISData .= 'KW  - ' . $dataSet[14] . $CrLf; //Todo split genre to M3


        }

        //$RISData .= 'M3  - ' . $dataSet[14] . $CrLf;

        if (!empty($dataSet[18]) && $dataSet[18] == 'WAHR') {
            $RISData .= 'N1  - ' . 'Present in Library DFL' . $CrLf;
        }

        if (!empty($dataSet[19]) && $dataSet[19] == 'WAHR') {
            $RISData .= 'N1  - ' . 'Present in Library VFL' . $CrLf;
        }

        if (!empty($dataSet[20])) $RISData .= 'N1  - ' . 'Subsidy ' . $dataSet[20] . $CrLf;

        if (!empty($dataSet[17])) {
            $RISData .= 'N1  - ' . trim(str_replace("\n", " - ", $dataSet[17])) . $CrLf;
        }
        $RISData .= 'ER  -' . $CrLf . $CrLf;

        return $RISData;
    }
}
