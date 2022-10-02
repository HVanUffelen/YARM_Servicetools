<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Http\Controllers\RefController;
use App\Models\Elasticsearch;
use App\Models\Name;
use App\Models\Name_ref;
use App\Models\Place_ref;
use App\Models\Ref;
use Illuminate\Support\Facades\Storage;
use function App\Http\Controllers\Admin\now;
use const App\Http\Controllers\Admin\PHP_EOL;

class upload4dlbtController extends Controller
{

    private function getGeoplace($placeName, $ref)
    {
        $geoPlace = RefController::look4GeoPlace($placeName);
        if (isset($geoPlace)) {
            $found = Place_ref::where('ref_id', '=', $ref->id)->first();
            if (isset($found)) {
                $found->place_original_id = $geoPlace->id;
                $found->save();
            } else {
                $newPlaceRef = new Place_ref();
                $newPlaceRef->ref_id = $ref->id;
                $newPlaceRef->place_original_id = $geoPlace->id;
                $newPlaceRef->save();
            }
            return $geoPlace;
        }
    }

    /*public function addRefs2Groups()
    {
        //$language_target_ids = [59];
        //$group_ids = [35];
        $language_target_ids = [29, 53, 17, 33, 55, 58, 39, 20, 25, 61, 57, 59];
        $group_ids = [22, 23, 24, 25, 26, 27, 28, 29, 31, 32, 33, 35];
        //$user_ids = [2, 20, 19, 12, 64, 28, 65, 66, 70, 69, 73, 73];

        foreach ($language_target_ids as $key => $language_id) {
            $query = Ref::where(function ($query) use ($language_id) {
                $query->where('language_target_id', '=', $language_id);
                $query->where('language_source_id', '=', 19);
            })
                ->orWhere(function ($query) use ($language_id) {
                    $query->where('language_source_id', '=', $language_id);
                    $query->where('language_target_id', '=', '');
                });
            $ids = $query->pluck('id');
            foreach ($ids as $id) {
                //$ref = Ref::find($id);
                //$ref->user_id = $user_ids[$key];
                //$ref->save();
                $found = Group_ref::where('ref_id', '=', $id)->where('group_id', '=', $group_ids[$key])->first();
                if (!isset($found)) {
                    $group_ref = new Group_ref();
                    $group_ref->ref_id = $id;
                    $group_ref->group_id = $group_ids[$key];
                    $group_ref->save();
                }
            }
        }
    }*/

    /*public
    function import_djack()
    {
        $columns = "TTTitle;AU_Nat1;AU_Nat2;AUGender;AU;TTLanguage;TR;TRGender;ILL;ILL_G;TTPlace;TTCenter;TTCountry;TTregion"
            . ";PublisherDeveloped;WorldSysCentral;TargetCentral;TTLatitude;TTLongitude;TTpublisher;TTyear;TTedition;TTedition;Genre;GenreSimple"
            . ";TTbindingtype;STtitle;STplace;STcenter?;STpublisher_nat;STlatitude;STlongitude;STpublisher;STyear of publication"
            . "TTyearFirstEdition;TTSubsidy;Information;Verified;RelayTranslation;First edition";

        $columnsArray = explode(';', $columns);

        if (Storage::exists('/' . 'convertcsv.json')) {
            $dataJson = Storage::get('/' . 'convertcsv.json');
            $dataArray = json_decode(($dataJson));
        }

        $RISData = '';
        $CrLf = PHP_EOL;
        $languages = ["Afrikaans", "Albanian", "Amharic", "Arabic", "Armenian", "Azeri", "Basque", "Bengali", "Bosnian", "Bulgarian", "Catalan", "Chinese", "Croatian", "Czech", "Danish", "English", "Esperanto", "Estonian", "Faeroese", "Farsi", "Finnish", "French", "Frisian", "Friulian", "Galician", "Georgian", "German", "Greek", "Gujarati", "Hebrew", "Hindi", "Hungarian", "Icelandic", "Indonesian", "Irish", "Italian", "Japanese", "Korean", "Kurdish", "Latin", "Latvian", "Lithuanian", "Luxembourgian", "Macedonian", "Marathi", "Nepalees", "Noord-Sotho", "Norwegian", "Odia", "Papiamento", "Papiamentu", "Persian", "Polish", "Portuguese", "Punjabi", "Romani", "Romanian", "Russian", "Sardijns", "Serbian", "Serbo-Croatian", "Slovak", "Slovenian", "Somali", "Spanish", "Swazi", "Swedish", "Tamil", "Thai", "Tsonga", "Tswana", "Turkish", "Ukrainian", "Urdu", "Venda", "Vietnamese", "Xhosa", "Zoeloe", "Zuid-Ndebele", "Zuid-Sotho", "Zwitserduits"];
        //$languages = ['Polish'];
        $i = 1;

        foreach ($languages as $language) {
            foreach ($dataArray as $dataSet) {
                if ($dataSet[5] == $language) {
                    $RISData .= 'TY  - BOOK' . $CrLf;
                    $RISData .= 'AV  - Data imported/adapted with Database Jack Mc Martin' . $CrLf;

                    if ($dataSet[4] != 'auteurs, div' && !empty($dataSet[4])) {
                        $RISData .= 'AU  - ' . $dataSet[4] . $CrLf;
                    }

                    $RISData .= 'TI  - ' . $dataSet[0] . $CrLf;
                    $RISData .= 'LA  - ' . $dataSet[5] . $CrLf;
                    $RISData .= 'LA2  - ' . 'Dutch' . $CrLf;

                    if (strpos($dataSet[6], ' ') != false) {
                        $nameTrArray = $array = explode(' ', $dataSet[6], 2);
                        $nameTR = $nameTrArray[1] . ', ' . $nameTrArray[0];
                    } else
                        $nameTR = $dataSet[6];

                    if (!empty($nameTR))
                        $RISData .= 'TR  - ' . $nameTR . $CrLf;

                    if (strpos($dataSet[8], ' ') != false) {
                        $nameIlArray = $array = explode(' ', $dataSet[8], 2);
                        $nameIL = $nameIlArray[1] . ', ' . $nameIlArray[0];
                    } else
                        $nameIL = $dataSet[8];

                    if (!empty($nameIL))
                        $RISData .= 'IL  - ' . $nameIL . $CrLf;

                    $RISData .= 'PB  - ' . $dataSet[19] . $CrLf;
                    $RISData .= 'CY  - ' . $dataSet[10] . $CrLf;

                    if (is_numeric($dataSet[20])) {
                        $RISData .= 'Y1  - ' . $dataSet[20] . $CrLf;
                    }

                    if ($dataSet[39] == 'Yes') {
                        $RISData .= 'ET  - 1' . $CrLf;
                    }

                    if (!empty($dataSet[21]) && !empty($dataSet[22])) {
                        $RISData .= 'C8  - ' . $dataSet[21] . ':' . $dataSet[22] . $CrLf;
                    } elseif (!empty($dataSet[21]) || !empty($dataSet[22])) {
                        if (!empty($dataSet[21]))
                            $RISData .= 'C8  - ' . $dataSet[21] . $CrLf;
                        else
                            $RISData .= 'C8  - ' . $dataSet[22] . $CrLf;
                    }

                    if (in_array($dataSet[24], array("Poetry", "Fiction", "Children's Literature", "Non-fiction", "Travel Literature"))) {
                        $RISData .= 'M3  - ' . $dataSet[24] . $CrLf;
                        $RISData .= 'KW  - ' . $dataSet[23] . $CrLf;
                    } else {
                        $RISData .= 'KW  - ' . $dataSet[23] . $CrLf;
                    }

                    $RISData .= 'U2  - ' . $dataSet[25] . $CrLf;
                    $RISData .= 'OP  - ' . $dataSet[26] . $CrLf;

                    if (!empty($dataSet[27]) && $dataSet[27] != 'No single NL derivative')
                        $RISData .= 'CY1  - ' . $dataSet[27] . $CrLf;

                    if (is_numeric($dataSet[33])) {
                        $RISData .= 'C3  - ' . $dataSet[33] . $CrLf;
                    }

                    if (!empty($dataSet[32]) && $dataSet[32] != 'No single NL derivative')
                        $RISData .= 'C5  - ' . $dataSet[32] . $CrLf;

                    if (!empty($dataSet[35])) {
                        $RISData .= 'KW  - ' . 'Subsidy: ' . $dataSet[35] . $CrLf;
                    }

                    if (!empty($dataSet[36])) {
                        $RISData .= 'N1  - ' . str_replace('  / .', '', trim($dataSet[36])) . $CrLf;
                    }

                    if (!empty($dataSet[38])) {
                        $RISData .= 'N1  - ' . trim($dataSet[38]) . $CrLf;
                    }

                    $RISData .= 'ER  -' . $CrLf . $CrLf;
                    $i++;
                }
            }
            Storage::put($language . '.ris', $RISData);
            //echo($RISData);
            $RISData = '';
        }

        echo($i);
    }*/

    function reloadTranslations()
    {
        $columns = "TTTitle;AU_Nat1;AU_Nat2;AUGender;AU;TTLanguage;TR;TRGender;ILL;ILL_G;TTPlace;TTCenter;TTCountry;TTregion"
            . ";PublisherDeveloped;WorldSysCentral;TargetCentral;TTLatitude;TTLongitude;TTpublisher;TTyear;TTedition;TTedition;Genre;GenreSimple"
            . ";TTbindingtype;STtitle;STplace;STcenter?;STpublisher_nat;STlatitude;STlongitude;STpublisher;STyear of publication"
            . "TTyearFirstEdition;TTSubsidy;Information;Verified;RelayTranslation;First edition";

        $columnsArray = explode(';', $columns);

        if (Storage::exists('/' . 'convertcsv_utf8.json')) {
            $dataJson = Storage::get('/' . 'convertcsv_utf8.json');
            $dataArray = json_decode(($dataJson));
        }

        $RISData = '';
        $CrLf = PHP_EOL;
        $languages = ["Croatian", "Czech", "English", "French", "German", "Hungarian", "Italian", "Polish", "Romanian", "Serbian", "Serbo-Croatian", "Slovak", "Slovenian", "Swedish"];
        $i = 1;

        foreach ($languages as $language) {
            foreach ($dataArray as $dataSet) {
                if ($dataSet[5] == $language
                    && ((!empty($dataSet[6]) && strpos($dataSet[6], ', ') != false)
                        || (!empty($dataSet[8]) && strpos($dataSet[8], ', ') != false)
                        || (!empty($dataSet[4]) && strpos($dataSet[4], ', ') != false)
                    )
                ) {
                    $author = $dataSet[4];
                    $title = $dataSet[0];
                    $year = $dataSet[10];
                    $publisher = $dataSet[19];
                    $translators = $dataSet[6];
                    $illustrators = $dataSet[8];

                    $author1 = explode(' and ', $dataSet[4]);
                    $translator1 = explode(', ', $dataSet[6]);
                    $illustrator1 = explode(', ', $dataSet[8]);

                    $authors = [];
                    $translators = [];
                    $illustrators = [];
                    $z = 0;

                    if (count($author1) > 1) {
                        foreach ($author1 as $author) {
                            $authors[$z][0] = explode(', ', $author)[0];
                            if (!empty($author[1])) {
                                if (isset(explode(', ', $author)[1]))
                                    $authors[$z][1] = explode(', ', $author)[1];
                                else
                                    $authors[$z][1] = '';
                            }

                            $z++;
                        }
                    }

                    $z = 0;
                    if (count($translator1) > 1) {
                        foreach ($translator1 as $translator) {
                            if (isset(explode(' ', $translator, 2)[1])) {
                                $translators[$z][0] = explode(' ', $translator, 2)[1];
                                $translators[$z][1] = explode(' ', $translator, 2)[0];
                            } else {
                                $translators[$z][0] = explode(' ', $translator, 2)[0];
                                $translators[$z][1] = '';
                            }
                            $z++;
                        }
                    }

                    $z = 0;
                    if (count($illustrator1) > 1) {
                        foreach ($illustrator1 as $illustrator) {
                            if (isset(explode(' ', $illustrator, 2)[1])) {
                                $illustrators[$z][0] = explode(' ', $illustrator, 2)[1];
                                $illustrators[$z][1] = explode(' ', $illustrator, 2)[0];
                            } else {
                                $illustrators[$z][0] = explode(' ', $illustrator, 2)[0];
                                $illustrators[$z][1] = '';
                            }
                            $z++;
                        }
                    }

                    if (!empty($translators)
                        || !empty($illustrators)
                        || !empty($authors)) {
                        $ref = Ref::where('title', '=', $title)
                            ->whereIn('status_id', [2])
                            ->where('modifier_id', '=', 2)
                            ->first();
                        if (isset($ref)) {
                            $nameIds = $ref->names->pluck('id');
                            if (!empty($translators)) {
                                $ref->names()->wherePivot('role_id', '=', 11)->detach();
                                $this->upDateNamesJMM($ref->id, $translators, 11);
                            }
                            if (!empty($illustrators)) {
                                $ref->names()->wherePivot('role_id', '=', 8)->detach();
                                $this->upDateNamesJMM($ref->id, $illustrators, 8);
                            }
                            if (!empty($authors)) {
                                $ref->names()->wherePivot('role_id', '=', 2)->detach();
                                $this->upDateNamesJMM($ref->id, $authors, 2);
                            }
                            foreach ($nameIds as $nameId) {
                                if (count(Name_ref::where('name_id', '=', $nameId)->pluck('id')) == 0) {
                                    Name::destroy($nameId);
                                }
                            }
                            $i++;
                        }
                    }
                }
            }
        }

        echo($i);
    }

    private function upDateNamesJMM($id, $persons, $role)
    {
        $i = 1;
        foreach ($persons as $person) {
            $newName_ref = new Name_ref;
            if (Name::where('name', $person[0])
                    ->where('first_name', $person[1])
                    ->first() == null) {
                $newName = new Name;
                $newName->name = $person[0];
                $newName->first_name = $person[1];
                $newName->save();
                $newName_ref->name_id = $newName->id;
            } else
                $newName_ref->name_id = Name::
                where('name', $person[0])
                    ->where('first_name', $person[1])
                    ->first()['id'];

            $newName_ref->role_id = $role;
            $newName_ref->ref_id = $id;;
            $newName_ref->position = $i + 1;
            $newName_ref->save();
            $i++;
        }


    }




    /*public function uploadPlaces(){
        $placesCsv = utf8_encode(Storage::get('Places2.csv'));
        $placesArray = explode('|', $placesCsv);
        $placesArray = array_values( array_unique( $placesArray, SORT_REGULAR ));
        $noplaces = ['Places','unknown','s.l.'];
        foreach ($placesArray as $place){
            if (strpos($place,';') != false);{
                $place = explode(';',$place)[0];
            }
            if (strpos($place,';') != false);{
                $place = explode(';',$place)[0];
            }
            if (strpos($place,' etc.') != false);{
                $place = explode(' etc.',$place)[0];
            }
            if (!in_array($place,$noplaces))
                $placesReduced[] = $place;
        }
        $placesReduced = array_values( array_unique( $placesReduced, SORT_REGULAR ));

        foreach ($placesReduced as $place){
            $foundInPlace2Geoplace = PlaceRefs2GeoPlaces::where('placeOriginal','=',$place)->pluck('placeOriginal');
            if (count($foundInPlace2Geoplace) == 0 ) {
                $placesToImport[] = $place;
            }
        }

        foreach ($placesToImport as $place){
            $foundInPlace = Place::where('name','=',$place)->pluck('name');
            if (count($foundInPlace) == 0 ) {
                $placesToImport2[] = $place;
            }
        }
        $sql= '';
        foreach ($placesToImport2 as $place){
            $sql.= 'INSERT INTO placerefs2geoplaces (placeOriginal) VALUES (' . $place .')#';
        }

        return;
    }*/


    public
    static function hashRefs()
    {
        //$allIds = Ref::select('id')->get()->take(5);

        for ($i = 0; $i <= 48570; $i++) {
            $ref = Ref::find($i);
            if (isset($ref)) {
                try {
                    $data = $ref->prepareDataset();
                } catch (\Throwable $e) {
                    dd($e);
                }
                try {
                    $ref->hash = $ref->hash();
                    $ref->save();
                    echo("Hash: " . $i . " created<br>");
                } catch (\Throwable $e) {
                    echo("Error: " . $e . "with: " . $i);
                    //dd($e);
                    exit;
                }
            }

        }
        echo("Hash - last id: " . $i . " created<br>");
    }


    public
    function upload4dlbt()
    {

        $connDlbt = mysqli_connect("localhost", "root", "superpass222");
        $dbName = 'dlbtproduction';
        $dbName = 'dlbtdevelopment';

        if ($connDlbt) {
            //set dbName;
            if (mysqli_select_db($connDlbt, $dbName)) {
                $sql1 = "SELECT * from dlbt";
                $result1 = mysqli_query($connDlbt, $sql1);
                if (!$result1) {
                    echo "Could not successfully run query ($sql1) from DB: " . mysqli_error($connDlbt);
                    exit;
                }
                if (mysqli_num_rows($result1) == 0 | mysqli_num_rows($result1) == NULL) {
                    echo "No rows found, nothing to print so am exiting";
                    exit;
                }
            } else {
                echo "Unable to select mydbname: " . mysqli_error($connDlbt);
                exit;
            }
        } else {
            echo "Unable to connect to DB: " . mysqli_error($connDlbt);
            exit;
        }

        $connYarm = mysqli_connect("localhost", "root", "superpass222");
        if ($connYarm) {
            if (!mysqli_select_db($connYarm, "yarm4dlbt")) {
                echo "Unable to select mydbname: " . mysqli_error($connYarm);
                exit;
            }
        } else {
            echo "Unable to connect to DB: " . mysqli_error($connYarm);
            exit;
        }

        //fill tables genres, statuses, types, identifier_types, languages, licenses
        //toDo formats, options, permissions, user_roles??, styles
        //$this->fillTables($connDlbt,$connYarm);

        if ($dbName == 'dlbtproduction')
            $this->fillTablePlO2Ple($connYarm);

        //load users if not already in table
        $this->loadUsers($connDlbt, $connYarm, $dbName);

        while ($row = mysqli_fetch_assoc($result1)) {

            $row = array_map(function ($e) use ($connYarm) {
                return mysqli_escape_string($connYarm, $e);
            }, $row);

            $userMail = substr($row['created_by'], strpos($row['created_by'], '(') + 1, strlen($row['created_by']) - strpos($row['created_by'], '(') - 2);
            $modifierMail = substr($row['modified_by'], strpos($row['modified_by'], '(') + 1, strlen($row['modified_by']) - strpos($row['modified_by'], '(') - 2);

            $user_id = $this->selectID('users', $userMail, $connYarm);
            $modifier_id = $this->selectID('users', $modifierMail, $connYarm);
            $statusID = $this->selectID('statuses', $row['imported'], $connYarm);
            $languageIDTarget = $this->selectID('languages', $row['language'], $connYarm);
            $languageIDSource = $this->selectID('languages', $row['language_original'], $connYarm);
            $genreID = $this->selectID('genres', $row['genre'], $connYarm);
            $typeID = $this->selectID('types', $row['TYPE'], $connYarm);

            if ($row['primarytxt'] == 'Yes' or strpos($row['keywords'], 'primary'))
                $primary = 'true';
            elseif ($row['primarytxt'] == '' and strpos($row['keywords'], 'primary'))
                $primary = 'true';
            elseif ($row['primarytxt'] == 'No' and strpos($row['keywords'], 'secondary'))
                $primary = 'false';
            elseif ($row['primarytxt'] == '' and strpos($row['keywords'], 'secondary'))
                $primary = 'false';
            else
                $primary = '';


            if ($row['literature'] == 'Yes' or strpos($row['keywords'], 'primary'))
                $literature = 'true';
            elseif ($row['literature'] == '' and strpos($row['keywords'], 'primary'))
                $literature = 'true';
            elseif ($row['literature'] == 'No' and strpos($row['keywords'], 'secondary'))
                $literature = 'false';
            elseif ($row['literature'] == '' and strpos($row['keywords'], 'secondary'))
                $literature = 'false';
            else
                $literature = '';

            if ($row['parentRef'] == '')
                $parentRef = '';
            else
                $parentRef = $row['parentRef'];

            $insertFieldsDLBT = "(literature,primarytxt,user_id,modifier_id,language_target_id,language_source_id, genre_id,type_id, status_id,old_serial_id,old_parent_id,abstract,comments_on_illustrations,comments_on_publication,comments_on_preface_postface,comments_on_translation,conference,container,edition,issue,issue_title,keywords,orig_title,pages,parent_id,physical_description,place,preface_postface,publisher,notes,title,year,year_original_title,volume,volume_numeric,series_editor,series_issue,series_title,series_volume,signature,series_volume_numeric,source_library,location, created_at, updated_at)";
            $insertValuesDLBT = "('" . $literature . "' , '" . $primary . "' , " . $user_id . ' , ' . $modifier_id . ' , ' . $languageIDTarget . ' , ' . $languageIDSource . ' , ' . $genreID . ' , ' . $typeID . ' , ' . $statusID . ' , ' . $row['serial'] . ' , "' . $parentRef . '", "' . $row['abstract'] . '", "' . $row['comments_on_illustrations'] . '", "' . $row['comments_on_publication'] . '", "' . $row['comments_on_preface_postface'] . '", "' . $row['comments_on_translation'] . '", "' . $row['conference'] . '", "' . $row['publication'] . '", "' . $row['edition'] . '", "' . $row['issue']
                . '", "' . $row['issue_title'] . '", "' . $row['keywords'] . '", "' . $row['orig_title'] . '", "' . $row['pages'] . '", "' . $row['parentRef'] . '", "' . $row['physical_description'] . '", "' . $row['place'] . '", "' . $row['preface_postface'] . '", "' . $row['publisher'] . '", "' . $row['notes'] . '", "' . $row['title'] . '", "' . $row['year'] . '", "' . $row['year_original_title'] . '", "' . $row['volume'] . '", "' . $row['volume_numeric'] . '", "' . $row['series_editor'] . '", "' . $row['series_issue'] . '", "' . $row['series_title'] . '", "' . $row['series_volume'] . '", "' . $row['signature'] . '", "' . $row['series_volume_numeric'] . '", "' . $row['source_library'] . '", "' . $row['location'] . '", "' . now() . '", "' . now() . '")';
            $sqlUpdateYarmRefs = "INSERT INTO refs " . $insertFieldsDLBT . " VALUES " . $insertValuesDLBT;

            if (mysqli_query($connYarm, $sqlUpdateYarmRefs)) {
                $last_id = $connYarm->insert_id;
                echo($last_id . ": Records inserted successfully. <br>");
                $this->look4Names($row, $last_id, $connYarm);
                $this->addFiles($row['serial'], $last_id, $connDlbt, $connYarm);
                $this->addIdentifiers($row['serial'], $last_id, $connDlbt, $connYarm);
                $idPlaceEnglish = $this->setIdsInplace_ref('placerefs2geoplaces', $row['place'], $last_id, $connYarm);
            } else {
                $last_id = $connYarm->insert_id;
                echo "ERROR: Could not execute $sqlUpdateYarmRefs . " . mysqli_error($connYarm) . "<br>";
                dd($last_id);
            };

        }
        echo("Records inserted successfully. Add parentIds<br>");

        $this->look4parentIds($connYarm);

        echo("ParentIds added. Create Hash-values<br>");

        $this->hashRefs();
        dd($last_id);
    }


    /**
     * @param $connYarm
     */
    function look4parentIds($connYarm)
    {
        $sql = "select * from refs where old_parent_id != 0 and old_parent_id != ''";
        $result = mysqli_query($connYarm, $sql);
        if ($result->num_rows > 0) {
            foreach ($result as $ref) {
                $oldParentID = $ref['old_parent_id'];
                $oldParentID = str_replace(';', ',', $oldParentID);
                $oldParentID = explode(',', $oldParentID);
                $oldSerialId = $ref['old_serial_id'];
                $newID = $ref['id'];
                foreach ($oldParentID as $PId) {
                    $sql_findeId = "select id from refs where old_serial_id = " . $PId;
                    $resultID = mysqli_query($connYarm, $sql_findeId);
                    $newPID = $resultID->fetch_assoc();
                    if ($newPID != null) {
                        $sql_insert2 = "INSERT INTO child_parent (child_tr_id, parent_id) VALUES (" . $newID . ", " . $newPID['id'] . ")";
                        $resultInsert2 = mysqli_query($connYarm, $sql_insert2);
                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $connDlbt
     * @param $connYarm
     */
    function loadUsers($connDlbt, $connYarm, $dbName)
    {
        $sql = "select * from users order by user_id";
        $result = mysqli_query($connDlbt, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                foreach ($result as $user) {

                    //check if user = already in table!
                    $userMail = $user['email'];
                    $sql = "select * from users where email = '" . $userMail . "'";
                    if ($userData = mysqli_query($connYarm, $sql)) {
                        $addUser = false;
                        $oldUser = $userData->fetch_assoc();
                        if ($oldUser == null)
                            $addUser = true;
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                        exit;
                    }

                    if ($addUser == true) {
                        $userName = $user['first_name'] . " " . $user['last_name'];
                        $sql = "INSERT INTO users (name,email,password,created_at,updated_at) VALUES ('" . $userName . "', '" . $user['email'] . "', '" . "\$2y\$10\$SSj8PAGFfap7KnGk8QdZQOLLtshmc0aVEufeGyKt6aNGYKxWH2k6K" . "', '" . now() . "', '" . now() . "')";
                        if (mysqli_query($connYarm, $sql)) {
                            $last_id = $connYarm->insert_id;
                            if ($user['email'] == 'herbert . van - uffelen@univie . ac . at')
                                $userRoleId = 1;
                            else
                                $userRoleId = 7;
                            $sql = "INSERT INTO roles4user_user (user_id,roles4user_id,created_at,updated_at) VALUES ('" . $last_id . "', '" . $userRoleId . "', '" . now() . "', '" . now() . "')";
                            if (!mysqli_query($connYarm, $sql)) {
                                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                                exit;
                            }
                            if ($dbName == 'dlbtproduction')
                                $sql = "INSERT INTO options (user_id,records_per_page,show_auto_completion,language,user_groups,language_target,created_at,updated_at) VALUES ('" . $last_id . "', '" . '5' . "', '" . 'true' . "' , '" . '20' . "', '" . '[2]' . "' , '" . '29' . "' , '" . now() . "', '" . now() . "')";
                            else
                                $sql = "INSERT INTO options (user_id,records_per_page,show_auto_completion,language,user_groups,language_target,created_at,updated_at) VALUES ('" . $last_id . "', '" . '5' . "', '" . 'true' . "' , '" . '20' . "', '" . '[3]' . "' , '" . '29' . "' , '" . now() . "', '" . now() . "')";
                            if (!mysqli_query($connYarm, $sql)) {
                                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                                exit;
                            }

                        } else {
                            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                            exit;
                        }

                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $row
     * @param $last_id
     * @param $connYarm
     */
    private
    function look4Names($row, $last_id, $connYarm)
    {
        if ($row['actor'] | $row['author'] | $row['choreographer'] | $row['composer'] | $row['corporate_author'] | $row['director'] | $row['editor'] | $row['illustrator'] | $row['producer'] | $row['translator'] | $row['txtwriter']) {

            if (isset($row['actor']) and $row['actor'] != '') {
                $role = $this->selectID('roles', 'actor', $connYarm);
                $this->splitAndSelectNames($row['actor'], $role, $last_id, $connYarm);
            }
            if (isset($row['author']) and $row['author'] != '') {
                $role = $this->selectID('roles', 'author', $connYarm);
                $this->splitAndSelectNames($row['author'], $role, $last_id, $connYarm);
            }
            if (isset($row['choreographer']) and $row['choreographer'] != '') {
                $role = $this->selectID('roles', 'choreographer', $connYarm);
                $this->splitAndSelectNames($row['choreographer'], $role, $last_id, $connYarm);
            }
            if (isset($row['composer']) and $row['composer'] != '') {
                $role = $this->selectID('roles', 'composer', $connYarm);
                $this->splitAndSelectNames($row['composer'], $role, $last_id, $connYarm);
            }
            if (isset($row['corporate_author']) and $row['corporate_author'] != '') {
                $role = $this->selectID('roles', 'corporate', $connYarm);
                $this->splitAndSelectNames($row['corporate_author'], $role, $last_id, $connYarm);
            }
            if (isset($row['director']) and $row['director'] != '') {
                $role = $this->selectID('roles', 'director', $connYarm);
                $this->splitAndSelectNames($row['director'], $role, $last_id, $connYarm);
            }
            if (isset($row['editor']) and $row['editor'] != '') {
                $role = $this->selectID('roles', 'editor', $connYarm);
                $this->splitAndSelectNames($row['editor'], $role, $last_id, $connYarm);
            }
            if (isset($row['illustrator']) and $row['illustrator'] != '') {
                $role = $this->selectID('roles', 'illustrator', $connYarm);
                $this->splitAndSelectNames($row['illustrator'], $role, $last_id, $connYarm);
            }
            if (isset($row['producer']) and $row['producer'] != '') {
                $role = $this->selectID('roles', 'producer', $connYarm);
                $this->splitAndSelectNames($row['producer'], $role, $last_id, $connYarm);
            }
            if (isset($row['translator']) and $row['translator'] != '') {
                $role = $this->selectID('roles', 'translator', $connYarm);
                $this->splitAndSelectNames($row['translator'], $role, $last_id, $connYarm);
            }
            if (isset($row['txtwriter']) and $row['txtwriter'] != '') {
                $role = $this->selectID('roles', 'txtwriter', $connYarm);
                $this->splitAndSelectNames($row['txtwriter'], $role, $last_id, $connYarm);
            }
        }
    }

    /**
     * @param $names
     * @param $role
     * @param $last_id
     * @param $connYarm
     */
    private
    function splitAndSelectNames($names, $role, $last_id, $connYarm)
    {
        $names = explode("; ", $names);
        $i = 1;
        foreach ($names as $name) {
            $arrayName = explode(', ', $name, 2);
            $familyName = $arrayName[0];
            if (isset($arrayName[1]))
                $first_name = $arrayName[1];
            else
                $first_name = '';
            $sql = "select id from names where name='" . $familyName . "' and first_name= '" . $first_name . "'";
            $result = mysqli_query($connYarm, $sql);
            if ($result) {
                if ($result->num_rows == 0) {
                    $last_id_name = $this->addNamesToTableYarm($name, $role, $last_id, $i, $connYarm);
                    $this->addToNameRef($last_id, $last_id_name, $role, $i, $connYarm);
                } else {
                    $row = mysqli_fetch_assoc($result);
                    $this->addToNameRef($last_id, $row['id'], $role, $i, $connYarm);
                }
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                exit;
            }

            $i++;
        }
    }

    /**
     * @param $lastRef_id
     * @param $last_NameId
     * @param $role
     * @param $position
     * @param $connYarm
     */
    private
    function addToNameRef($lastRef_id, $last_NameId, $role, $position, $connYarm)
    {
        $sql = 'INSERT INTO name_ref(name_id, ref_id, role_id, position, created_at, updated_at) VALUES(' . $last_NameId . ', ' . $lastRef_id . ', ' . $role . ', ' . $position . ", '" . now() . "', '" . now() . "')";
        if (mysqli_query($connYarm, $sql)) {
            $last_id = $connYarm->insert_id;
            //echo ($last_id . ": ID inserted successfully in name_ref. <br>");
        } else {
            $last_id = $connYarm->insert_id;
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            dd($last_id);
        };
    }

    /**
     * @param $name
     * @param $role
     * @param $last_id
     * @param $position
     * @param $connYarm
     * @return mixed $last_id
     */
    private
    function addNamesToTableYarm($name, $role, $last_id, $position, $connYarm)
    {
        $arrayName = explode(', ', $name, 2);
        $familyName = $arrayName[0];
        if (isset($arrayName[1]))
            $first_name = $arrayName[1];
        else
            $first_name = '';
        //write to Table
        $sql = "INSERT INTO names (name,first_name,created_at,updated_at) VALUES ('" . $familyName . "', '" . $first_name . "', '" . now() . "' , '" . now() . "')";
        if (mysqli_query($connYarm, $sql)) {
            $last_id = $connYarm->insert_id;
            //echo ($last_id . ": Name inserted successfully. <br>");
        } else {
            $last_id = $connYarm->insert_id;
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            dd($last_id);
        };

        return $last_id;
    }

    /**
     * @param $table
     * @param $name
     * @param $connYarm
     * @return string
     */
    private
    function selectID($table, $name, $connYarm)
    {
        if ($table == 'licenses')
            $sql = "select id from " . $table . " where link='" . $name . "'";
        else if ($table == 'users')
            $sql = "select id from " . $table . " where email='" . $name . "'";
        else
            $sql = "select id from " . $table . " where name='" . $name . "'";
        $result = mysqli_query($connYarm, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                $row = mysqli_fetch_assoc($result);
                return $row['id'];
            } else {
                //echo "ERROR: ID for " . $table . "= " . $name ." not found<br>";
                if ($table == 'languages')
                    return '0';
                elseif ($table == 'types')
                    return '20';
                elseif ($table == 'genres')
                    return '3';
                else
                    return '1';
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $table
     * @param $name
     * @param $refId
     * @param $connYarm
     * @return string|null
     */
    private
    function setIdsInplace_ref($table, $name, $refId, $connYarm)
    {
        $sql = "select * from " . $table . " where placeOriginal = '" . $name . "'";
        $result = mysqli_query($connYarm, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                $row = mysqli_fetch_assoc($result);
                $placeEnglish = $row['placeEnglish'];
                $sql = "select id from places where name = '" . $placeEnglish . "'";
                $result = mysqli_query($connYarm, $sql);
                if ($result) {
                    if ($result->num_rows > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $idPlaceEnglish = $row['id'];
                        $sql = "INSERT INTO place_ref (place_id, ref_id, created_at, updated_at) VALUES ('" . $idPlaceEnglish . "', '" . $refId . "', '" . now() . "' , '" . now() . "')";
                        $result = mysqli_query($connYarm, $sql);
                        /*$sql = "UPDATE REFS SET place_geodata_id = " . $idPlaceEnglish;
                        $result = mysqli_query($connYarm, $sql);*/
                        if ($result) {
                            return $idPlaceEnglish;
                        } else {
                            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                            exit;
                        }
                    }
                }
            } else {
                return null;
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $serial
     * @param $last_id
     * @param $connDlbt
     * @param $connYarm
     */
    private
    function addIdentifiers($serial, $last_id, $connDlbt, $connYarm)
    {
        $sql = "select * from identifiers where identifier_serial = " . $serial;
        $result = mysqli_query($connDlbt, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                foreach ($result as $row) {
                    $row = array_map(function ($e) use ($connYarm) {
                        return mysqli_escape_string($connYarm, $e);
                    }, $row);
                    $sql = "select file_path from files where file_id = " . $row['identifier_file_id'];
                    $result2 = mysqli_query($connDlbt, $sql);
                    if ($result2) {
                        if ($result2->num_rows > 0) {
                            $row2 = mysqli_fetch_assoc($result2);
                            $file_name = $row2['file_path'];
                            $file_id = $this->selectID('files', $file_name, $connYarm);
                        } else
                            $file_id = 0;
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                        exit;
                    }
                    $fileId = $file_id;
                    if (isset($row['identifier_phaidra_license']))
                        $licenseId = $this->selectID('licenses', $row['identifier_phaidra_license'], $connYarm);
                    else
                        $licenseId = 1;
                    $identifierTypeId = $this->selectID('identifier_types', $row['identifier_type'], $connYarm);
                    $value = $row['identifier_value'];
                    $comment = $row['identifier_comment'];
                    $conv_status = $row['identifier_teiconv_status'];
                    $conv_error = $row['identifier_teiconv_error'];
                    $sql = "Insert into identifiers (ref_id,file_id,license_id,identifier_type_id,value,comment,conv_status,conv_error,created_at,updated_at) VALUES (" . $last_id . ", " . $fileId . ", " . $licenseId . ", " . $identifierTypeId . ", '" . $value . "', '" . $comment . "', '" . $conv_status . "', '" . $conv_error . "', '" . now() . "' , '" . now() . "')";
                    if (mysqli_query($connYarm, $sql)) {
                        //echo('Identifier:  ' . $value . ' inserted successfully . <br > ');
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                        exit;
                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $serial
     * @param $last_id
     * @param $connDlbt
     * @param $connYarm
     */
    private
    function addFiles($serial, $last_id, $connDlbt, $connYarm)
    {
        $sql = "select * from files where file_serial = " . $serial;
        $result = mysqli_query($connDlbt, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                foreach ($result as $row) {
                    $row = array_map(function ($e) use ($connYarm) {
                        return mysqli_escape_string($connYarm, $e);
                    }, $row);
                    if (isset($row['file_license']))
                        $licenseId = $this->selectID('licenses', $row['file_license'], $connYarm);
                    else
                        $licenseId = 1;
                    $name = $row['file_path'];
                    $comment = $row['file_comment'];
                    if ($row['file_ready4phaidra'] == 'Y')
                        $r4Phaidra = 1;
                    else
                        $r4Phaidra = 0;
                    //$r4Phaidra = $row['file_ready4phaidra'];
                    $phaidraStatus = (int)$row['file_phaidra_status'];
                    if ($row['file_phaidra_error'] == '')
                        $phaidraError = '0';
                    else
                        $phaidraError = $row['file_phaidra_error'];
                    $tei = (int)$row['file_tei'];

                    $sql = "Insert into files (ref_id,license_id,name,comment,r4phaidra,phaidra_status,phaidra_error,tei,created_at,updated_at) VALUES (" . $last_id . ", " . $licenseId . ", '" . $name . "' , '" . $comment . "', '" . $r4Phaidra . "', '" . $phaidraStatus . "', '" . $phaidraError . "', '" . $tei . "', '" . now() . "' , '" . now() . "')";

                    if (mysqli_query($connYarm, $sql)) {
                        //echo('File ' . $name . ' inserted successfully . <br > ');
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                        exit;
                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
            exit;
        }
    }

    /**
     * @param $connYarm
     */
    private
    function fillTablePlO2Ple($connYarm)
    {
        $sql = "select * from placerefs2geoplaces";
        if ($result = mysqli_query($connYarm, $sql)) {
            if ($result->num_rows > 0) {
                return true;
            } else {
                $sql = "INSERT INTO `placerefs2geoplaces` VALUES (1,'placeOriginal','placeEnglish',now(),now()),(2,'Aachen','Aachen',now(),now()),(3,'Aalborg','Aalborg',now(),now()),(4,'Aalst','Aalst',now(),now()),(5,'Alost','Aalst',now(),now()),(6,'Aarau','Aarau',now(),now()),(7,'Aarau; Franfurt a . M . ','Aarau',now(),now()),(8,'Aarau; Frankfurt a . M . ','Aarau',now(),now()),(9,'Aarau; Frankfurt a . M .; Salzburg','Aarau',now(),now()),(10,'Aarau; Stuttgart','Aarau',now(),now()),(11,'Åarhus','Aarhus',now(),now()),(12,'Århus','Aarhus',now(),now()),(13,'Aartselaar','Aartselaar',now(),now()),(14,'Aartselaar; Utrecht','Aartselaar',now(),now()),(15,'Abu Dhabi','Abu Dhabi',now(),now()),(16,'Adelaide','Adelaide',now(),now()),(17,'Ahrensburg; Paris','Ahrensburg',now(),now()),(18,'Aire','Aire',now(),now()),(19,'Aix - en - Provence','Aix - en - Provence',now(),now()),(20,'Alabama','Alabama',now(),now()),(21,'Albi','Albi',now(),now()),(22,'Albolote','Albolote',now(),now()),(23,'Alcabideche','Alcabideche',now(),now()),(24,'Alcoy','Alcoy',now(),now()),(25,'Aleppo','Aleppo',now(),now()),(26,'Alfortville','Alfortville',now(),now()),(27,'Alkmaar','Alkmaar',now(),now()),(28,'Alkmaar; Amsterdam','Alkmaar',now(),now()),(29,'Allendorf an der Werra; Leipzig','Allendorf on the Werra',now(),now()),(30,'Almaty','Almaty',now(),now()),(31,'Almelo','Almelo',now(),now()),(32,'Almere','Almere',now(),now()),(33,'Alphen a . d . Rijn','Alphen aan den Rijn',now(),now()),(34,'Alphen aan den Rijn','Alphen aan den Rijn',now(),now()),(35,'Alsemberg','Alsemberg',now(),now()),(36,'Wedel - Holstein, Alster','Alster',now(),now()),(37,'Altamura','Altamura',now(),now()),(38,'Altea','Altea',now(),now()),(39,'Altena','Altena',now(),now()),(40,'Altenburg','Altenburg',now(),now()),(41,'Alzira','Alzira',now(),now()),(42,'Amadora','Amadora',now(),now()),(43,'Amersfoort','Amersfoort',now(),now()),(44,'Amersfoort; North Yorkshire','Amersfoort',now(),now()),(45,'Amersfoort / Leuven','Amersfoort',now(),now()),(46,'Amstersfoort','Amersfoort',now(),now()),(47,'Amherst','Amherst',now(),now()),(48,'Amstelveen','Amstelveen',now(),now()),(49,'[Amsterdam ?]','Amsterdam',now(),now()),(50,'Amsterdam','Amsterdam',now(),now()),(51,'Amsterdam [?]','Amsterdam',now(),now()),(52,'Amsterdam [fingiert]','Amsterdam',now(),now()),(53,'Amsterdam / Budapest','Amsterdam',now(),now()),(54,'Amsterdam / Rotterdam, Antwerpen','Amsterdam',now(),now()),(55,'Amsterdam / Antwerpen','Amsterdam',now(),now()),(56,'Amsterdam etc . ','Amsterdam',now(),now()),(57,'Amsterdam u . a . ','Amsterdam',now(),now()),(58,'Amsterdam, Antwerpen','Amsterdam',now(),now()),(59,'Amsterdam, Arnhem','Amsterdam',now(),now()),(60,'Amsterdam, Brussel','Amsterdam',now(),now()),(61,'Amsterdam, Brussel / Amsterdam','Amsterdam',now(),now()),(62,'Amsterdam, Den Haag','Amsterdam',now(),now()),(63,'Amsterdam, Leuven','Amsterdam',now(),now()),(64,'Amsterdam, Mechelen','Amsterdam',now(),now()),(65,'Amsterdam, Paris','Amsterdam',now(),now()),(66,'Amsterdam, Rotterdam','Amsterdam',now(),now()),(67,'Amsterdam, Sint - Stevens - Woluwe','Amsterdam',now(),now()),(68,'Amsterdam, Woluwe','Amsterdam',now(),now()),(69,'Amsterdam; Antwerpen','Amsterdam',now(),now()),(70,'Amsterdam; Basel','Amsterdam',now(),now()),(71,'Amsterdam; Danzig; Leipzig','Amsterdam',now(),now()),(72,'Amsterdam; Frankfurt am Main','Amsterdam',now(),now()),(73,'Amsterdam; Frankfurt am Main; Leipzig','Amsterdam',now(),now()),(74,'Amsterdam; Leipzig','Amsterdam',now(),now()),(75,'Amsterdam; Moers','Amsterdam',now(),now()),(76,'Amsterdam; Rotterdam','Amsterdam',now(),now()),(77,'Amsterdam; Velsen','Amsterdam',now(),now()),(78,'Amsterdam / Antwerpen','Amsterdam',now(),now()),(79,'Amsterdam / Antwerpen / Sint - Stevens - Woluwe','Amsterdam',now(),now()),(80,'Amsterdam / Atlanta','Amsterdam',now(),now()),(81,'Amsterdam / Atlanta: Rodopi','Amsterdam',now(),now()),(82,'Amsterdam / Baarn','Amsterdam',now(),now()),(83,'Amsterdam / Deventer','Amsterdam',now(),now()),(84,'Amsterdam / Leuven','Amsterdam',now(),now()),(85,'Amsterdam / new York','Amsterdam',now(),now()),(86,'Amsterdam / Philadelphia','Amsterdam',now(),now()),(87,'Amsterdam / Sint - Steven - Woluwe / Brussel','Amsterdam',now(),now()),(88,'Amsterdam / Sint - Stevens - Woluwe','Amsterdam',now(),now()),(89,'fingiert [Amsterdam]','Amsterdam',now(),now()),(90,'Anheim','Anaheim',now(),now()),(91,'Anderlecht; Bruxelles','Anderlecht',now(),now()),(92,'Andernach','Andernach',now(),now()),(93,'Aneby','Aneby',now(),now()),(94,'Ankara','Ankara',now(),now()),(95,'Ann Arbor','Ann Arbor',now(),now()),(96,'Antony','Antony',now(),now()),(97,'Antverpeno','Antwerp',now(),now()),(98,'Antwerp','Antwerp',now(),now()),(99,'Antwerpen','Antwerp',now(),now()),(100,'Antwerpen - Amsterdam','Antwerp',now(),now()),(101,'Antwerpen - Apeldoorn','Antwerp',now(),now()),(102,'Antwerpen / Utrecht','Antwerp',now(),now()),(103,'Antwerpen, Amsterdam','Antwerp',now(),now()),(104,'Antwerpen; Amsterdam','Antwerp',now(),now()),(105,'Antwerpen; Bonn; Paris; Amsterdam','Antwerp',now(),now()),(106,'Antwerpen; Brussel','Antwerp',now(),now()),(107,'Antwerpen / Amsterdam','Antwerp',now(),now()),(108,'Antwerpen / Brussel / Gent / Leuven','Antwerp',now(),now()),(109,'Antwerpen / Bruxelles','Antwerp',now(),now()),(110,'Antwerpen / Bruxelles; Groningen / Den Haag','Antwerp',now(),now()),(111,'Antwerpen / Meppel / Boom','Antwerp',now(),now()),(112,'Antwerpen / Rotterdam','Antwerp',now(),now()),(113,'Anvers','Antwerp',now(),now()),(114,'Anvers, Bruxelles, Paris','Antwerp',now(),now()),(115,'Anvers; Bruxelles','Antwerp',now(),now()),(116,'Anvers; Paris','Antwerp',now(),now()),(117,'Apeldoorn','Apeldoorn',now(),now()),(118,'Argentina','Argentina',now(),now()),(119,'Arles','Arles',now(),now()),(120,'Arles; Québec','Arles',now(),now()),(121,'Arlon','Arlon',now(),now()),(122,'Arnheim','Arnhem',now(),now()),(123,'Arnhem','Arnhem',now(),now()),(124,'Arnhim','Arnhem',now(),now()),(125,'Aschaffenburg','Aschaffenburg',now(),now()),(126,'Aschaffenburg, München','Aschaffenburg',now(),now()),(127,'Asheville, NC','Asheville',now(),now()),(128,'Asheville; North Carolina','Asheville',now(),now()),(129,'Assen','Assen',now(),now()),(130,'Assen / Maastricht','Assen',now(),now()),(131,'Assmannshausen am Rhein','Assmannshausen',now(),now()),(132,'Athēna','Athena',now(),now()),(133,'Athènai','Athena',now(),now()),(134,'Athene','Athena',now(),now()),(135,'Auckland','Auckland',now(),now()),(136,'Augsburg','Augsburg',now(),now()),(137,'Augsburg; Ulm','Augsburg',now(),now()),(138,'Aurich','Aurich',now(),now()),(139,'Auvers sur Oise','Auvers - sur - Oise',now(),now()),(140,'Averbode','Averbode ',now(),now()),(141,'Averbode, Apeldoorn','Averbode ',now(),now()),(142,'Averbode; Paris','Averbode ',now(),now()),(143,'Avin','Avin',now(),now()),(144,'Baar','Baar',now(),now()),(145,'Baarle Nassau','Baarle - Nassau',now(),now()),(146,'Baarn','Baarn',now(),now()),(147,'Baarn, Amsterdam','Baarn',now(),now()),(148,'Baarn, Antwerpen','Baarn',now(),now()),(149,'Baarn / Antwerpen','Baarn',now(),now()),(150,'Bad Cannstatt','Bad',now(),now()),(151,'Bad Driburg','Bad',now(),now()),(152,'Bad Godesberg','Bad',now(),now()),(153,'Bad Honnef','Bad',now(),now()),(154,'Bad Liebenzell / Kreis Calw','Bad',now(),now()),(155,'Bad Nauheim','Bad',now(),now()),(156,'Bad Reichenhall','Bad',now(),now()),(157,'Bad Salzig','Bad',now(),now()),(158,'Bad Vöslau','Bad',now(),now()),(159,'Bad Wörishofen','Bad',now(),now()),(160,'Kreuznach','Bad Kreuznach',now(),now()),(161,'Oldesloe','Bad Oldesloe',now(),now()),(162,'Baden - Baden','Baden - Baden',now(),now()),(163,'Baden - Baden; Genf','Baden - Baden',now(),now()),(164,'Bagsvaerd','Bagsværd',now(),now()),(165,'Nagybánya','Baia Mare',now(),now()),(166,'Bakoe','Baku',now(),now()),(167,'Baku','Baku',now(),now()),(168,'Baltimore','Baltimore',now(),now()),(169,'Baltimore; new York','Baltimore',now(),now()),(170,'Baltimore; Philadelphia','Baltimore',now(),now()),(171,'Balve','Balve',now(),now()),(172,'Bamberg','Bamberg',now(),now()),(173,'Bandung','Bandung',now(),now()),(174,'Bandung; Den Haag','Bandung',now(),now()),(175,'Bangkok','Bangkok',now(),now()),(176,'Banholt','Banholt',now(),now()),(177,'Banská Bystrica','Banská Bystrica',now(),now()),(178,'Barcarena(Lisboa)','Barcarena',now(),now()),(179,'Barcelona','Barcelona',now(),now()),(180,'Barcelona, Buenos Aires, Mexico','Barcelona',now(),now()),(181,'Barcelona; Bogotá; Buenos Aires','Barcelona',now(),now()),(182,'Barcelona; Bogotá; Buenos Aires etc . ','Barcelona',now(),now()),(183,'Barcelona; Madrid','Barcelona',now(),now()),(184,'Bari','Bari',now(),now()),(185,'Barmen','Barmen',now(),now()),(186,'Barnstaple','Barnstaple',now(),now()),(187,'Bartenstein','Bartoszyce',now(),now()),(188,'Bâle; Genève - Annemasse','Basel',now(),now()),(189,'Basel','Basel',now(),now()),(190,'Basel; Brüssel; Köln; Wien','Basel',now(),now()),(191,'Basel; Hamburg','Basel',now(),now()),(192,'Basel; Tübingen','Basel',now(),now()),(193,'Batavia','Batavia',now(),now()),(194,'Batavia [Amsterdam]','Batavia',now(),now()),(195,'Bath','Bath',now(),now()),(196,'Baton Rouge','Baton Rouge',now(),now()),(197,'Bautzen','Bautzen',now(),now()),(198,'Bautzen; Rudolstadt','Bautzen',now(),now()),(199,'Bayreuth','Bayreuth',now(),now()),(200,'Beauvais','Beauvais',now(),now()),(201,'Bedum','Bedum',now(),now()),(202,'Beijing','Beijing',now(),now()),(203,'Peking','Beijing',now(),now()),(204,'Beirut','Beirut',now(),now()),(205,'Békéscsaba','Békéscsaba',now(),now()),(206,'Belgrado','Belgrade',now(),now()),(207,'Beograd','Belgrade',now(),now()),(208,'Belo Horizonte','Belo Horizonte',now(),now()),(209,'Belval','Belval',now(),now()),(210,'Bempflingen','Bempflingen',now(),now()),(211,'Bensheim','Bensheim',now(),now()),(212,'Bensheim; Düsseldorf','Bensheim',now(),now()),(213,'Bentheim','Bentheim',now(),now()),(214,'Berchem','Berchem',now(),now()),(215,'Berchem - Antwerpen','Berchem',now(),now()),(216,'Bergamo','Bergamo',now(),now()),(217,'Bergen','Bergen',now(),now()),(218,'Bergen op Zoom','Bergen op Zoom',now(),now()),(219,'Bergisch Gladbach','Bergisch Gladbach',now(),now()),(220,'Berkeley','Berkeley',now(),now()),(221,'Berkeley; Oxford','Berkeley',now(),now()),(222,'Berlin','Berlin',now(),now()),(223,'Berlin [Dahlem]','Berlin',now(),now()),(224,'Berlin / new York','Berlin',now(),now()),(225,'Berlin u . a . ','Berlin',now(),now()),(226,'Berlin - Lichterfelde','Berlin',now(),now()),(227,'Berlin - Charlottenburg','Berlin',now(),now()),(228,'Berlin - Dahlem','Berlin',now(),now()),(229,'Berlin - Friedenau','Berlin',now(),now()),(230,'Berlin - Lichterfelde','Berlin',now(),now()),(231,'Berlin - Schöneberg','Berlin',now(),now()),(232,'Berlin - Weissensee','Berlin',now(),now()),(233,'Berlin, Hamburg','Berlin',now(),now()),(234,'Berlin, Hamburg, München','Berlin',now(),now()),(235,'Berlin, München','Berlin',now(),now()),(236,'Berlin, new York','Berlin',now(),now()),(237,'Berlin, Stuttgart','Berlin',now(),now()),(238,'Berlin; (Leipzig)','Berlin',now(),now()),(239,'Berlin; Brüssel','Berlin',now(),now()),(240,'Berlin; Darmstadt','Berlin',now(),now()),(241,'Berlin; Darmstadt; Wien','Berlin',now(),now()),(242,'Berlin; Frankfurt a . M . ','Berlin',now(),now()),(243,'Berlin; Frankfurt a . M .; Hamburg','Berlin',now(),now()),(244,'Berlin; Hamburg','Berlin',now(),now()),(245,'Berlin; Hamburg; Münster','Berlin',now(),now()),(246,'Berlin; Leipzig','Berlin',now(),now()),(247,'Berlin; Leipzig; Wien','Berlin',now(),now()),(248,'Berlin; Leipzig; Wien; Zürich','Berlin',now(),now()),(249,'Berlin; Leipzih','Berlin',now(),now()),(250,'Berlin; München','Berlin',now(),now()),(251,'Berlin; Schlechtenwegen','Berlin',now(),now()),(252,'Berlin; Weimar','Berlin',now(),now()),(253,'Berlin; Wien','Berlin',now(),now()),(254,'Berlin; Wien; Leipzig','Berlin',now(),now()),(255,'Berlin; Zürich','Berlin',now(),now()),(256,'Berlin / Stettin','Berlin',now(),now()),(257,'Berlin / Hamburg','Berlin',now(),now()),(258,'Berlin / München','Berlin',now(),now()),(259,'Berlin / Weimar','Berlin',now(),now()),(260,'Bern','Bern',now(),now()),(261,'Bern, Berlin, Bruxelles','Bern',now(),now()),(262,'Bern; Leipzig','Bern',now(),now()),(263,'Bern; München','Bern',now(),now()),(264,'Bern; München; Wien','Bern',now(),now()),(265,'Bern / Frankfurt am Main / new York','Bern',now(),now()),(266,'Beroun','Beroun',now(),now()),(267,'Besançon','Besançon',now(),now()),(268,'Bethel','Bethel',now(),now()),(269,'Beuvry','Beuvry',now(),now()),(270,'Białystok','Bialystok',now(),now()),(271,'Bielefeld','Bielefeld',now(),now()),(272,'Bielsko','Bielsko',now(),now()),(273,'Bielsko Biala','Bielsko',now(),now()),(274,'Billère','Billère',now(),now()),(275,'Bilthoven','Bilthoven',now(),now()),(276,'Bindlach','Bindlach',now(),now()),(277,'Bingen','Bingen am Rhein',now(),now()),(278,'Blankenberge','Blankenberge',now(),now()),(279,'Blatná','Blatná',now(),now()),(280,'Blieskastel','Blieskastel ',now(),now()),(281,'Bloomington','Bloomington',now(),now()),(282,'Bnei Brak','Bnei Brak',now(),now()),(283,'Bnei Brak; Tel Aviv','Bnei Brak',now(),now()),(284,'Böblingen','Böblingen',now(),now()),(285,'Bocholt','Bocholt',now(),now()),(286,'Bochum','Bochum',now(),now()),(287,'Bochum - Linden','Bochum',now(),now()),(288,'Bologna','Bologna',now(),now()),(289,'Bolsward','Bolsward',now(),now()),(290,'Bozen','Bolzano',now(),now()),(291,'Bolzano','Bolzano ',now(),now()),(292,'Bonn','Bonn',now(),now()),(293,'Boom','Boom',now(),now()),(294,'Boppard','Boppard',now(),now()),(295,'Bordeaux','Bordeaux',now(),now()),(296,'Borgerhout','Borgerhout',now(),now()),(297,'Borken','Borken',now(),now()),(298,'Borlänge','Borlänge',now(),now()),(299,'Bornheim','Bornheim',now(),now()),(300,'Bornheim - Merten','Bornheim',now(),now()),(301,'Borsbeek / Amsterdam','Borsbeek',now(),now()),(302,'Boston','Boston',now(),now()),(303,'Bottna, Hamburgsund','Bottna',now(),now()),(304,'Boulder, Colorado','Boulder',now(),now()),(305,'Bourg - en - Bresse','Bourg - en - Bresse',now(),now()),(306,'Boxmeer','Boxmeer',now(),now()),(307,'Braamfontein','Braamfontein',now(),now()),(308,'Braine - le - Comte','Braine - le - Comte',now(),now()),(309,'Brakel','Brakel',now(),now()),(310,'Brasschaat','Brasschaat',now(),now()),(311,'Bratislava','Bratislava',now(),now()),(312,'Bratislava; Brno','Bratislava',now(),now()),(313,'Pozsony','Bratislava',now(),now()),(314,'Braunschweig','Braunschweig',now(),now()),(315,'Braunschweig; Berlin; Hamburg','Braunschweig',now(),now()),(316,'Breda','Breda',now(),now()),(317,'Breda, Berchem','Breda',now(),now()),(318,'Bredstedt','Bredstedt',now(),now()),(319,'Bregenz','Bregenz',now(),now()),(320,'Bremen','Bremen',now(),now()),(321,'Bremen; Frankfurt am Main','Bremen',now(),now()),(322,'Bremen; Frankfurt am Main; Leipzig','Bremen',now(),now()),(323,'Bremen; Wien','Bremen',now(),now()),(324,'Bremerhaven','Bremerhaven',now(),now()),(325,'Bremershaven','Bremerhaven',now(),now()),(326,'Wesermünde - Lehe','Bremerhaven',now(),now()),(327,'Brentwood','Brentwood',now(),now()),(328,'Brescia','Brescia',now(),now()),(329,'Brest','Brest',now(),now()),(330,'Bridgend','Bridgend',now(),now()),(331,'Brighton','Brighton',now(),now()),(332,'Brinon sur Sauldre','Brinon - sur - Sauldre',now(),now()),(333,'[Bristol]','Bristol',now(),now()),(334,'Bristol','Bristol',now(),now()),(335,'Brixen','Brixen',now(),now()),(336,'Brno','Brno',now(),now()),(337,'Brno, Olomouc','Brno',now(),now()),(338,'Brünn','Brno',now(),now()),(339,'Bromma','Bromma',now(),now()),(340,'Brookfield, Conn . ','Brookfield',now(),now()),(341,'Brooklyn','Brooklyn',now(),now()),(342,'Brooklyn N . Y . ','Brooklyn',now(),now()),(343,'Brtnice','Brtnice',now(),now()),(344,'Brtnice na Moravě','Brtnice',now(),now()),(345,'Bruges','Bruges',now(),now()),(346,'Bruges; Paris','Bruges',now(),now()),(347,'Brugge','Bruges',now(),now()),(348,'Brugo','Bruges',now(),now()),(349,'Brunssum','Brunssum',now(),now()),(350,'Bruessel','Brussels',now(),now()),(351,'Brussel','Brussels',now(),now()),(352,'Brussel / Amsterdam','Brussels',now(),now()),(353,'Brussel - Maastricht','Brussels',now(),now()),(354,'Brussel - Noord','Brussels',now(),now()),(355,'Brussel - Schaerbeek','Brussels',now(),now()),(356,'Brussel, Amsterdam','Brussels',now(),now()),(357,'Brussel, Berlin','Brussels',now(),now()),(358,'Brüssel, Leipzig','Brussels',now(),now()),(359,'Brüssel, Leipzig; Basel','Brussels',now(),now()),(360,'Brussel, Zaltbommel','Brussels',now(),now()),(361,'Brüssel; Leipzig','Brussels',now(),now()),(362,'Brussel / Amsterdam','Brussels',now(),now()),(363,'Brussels','Brussels',now(),now()),(364,'Bruxelles','Brussels',now(),now()),(365,'Bruxelles & Paris','Brussels',now(),now()),(366,'Bruxelles, Bruges','Brussels',now(),now()),(367,'Bruxelles; Arles','Brussels',now(),now()),(368,'Bruxelles; Paris','Brussels',now(),now()),(369,'Bruxelles; Paris; Leipzig; Livourne','Brussels',now(),now()),(370,'Bruxelles; Paris; Lille','Brussels',now(),now()),(371,'Bruxelles; Roulers','Brussels',now(),now()),(372,'Brieg','Brzeg',now(),now()),(373,'Bucha','Bucha',now(),now()),(374,'Bucha(Kviv)','Bucha',now(),now()),(375,'Bucarest','Bucharest',now(),now()),(376,'Bucureşti','Bucharest',now(),now()),(377,'Bukarest','Bucharest',now(),now()),(378,'Budapest','Budapest',now(),now()),(379,'Budapest / Bratislava','Budapest',now(),now()),(380,'Budapest / Bukarest','Budapest',now(),now()),(381,'Budapest; Bratislava','Budapest',now(),now()),(382,'Budapest; Pécs','Budapest',now(),now()),(383,'Eötvös Loránd Universiteit(ELTE) Boedapest','Budapest',now(),now()),(384,'Budyšín','Bautzen',now(),now()),(385,'Buenos Aires','Buenos Aires',now(),now()),(386,'Costermansville','Bukavu',now(),now()),(387,'Bünde i . W . ','Bünde',now(),now()),(388,'Burg bei Magdeburg','Burg bei Magdeburg',now(),now()),(389,'burgas','Burgas',now(),now()),(390,'Bussum','Bussum',now(),now()),(391,'Bussum, Deurne','Bussum',now(),now()),(392,'Bussum; Leipzig','Bussum',now(),now()),(393,'Bydgoszcz','Bydgoszcz',now(),now()),(394,'Beuthen','Bytom',now(),now()),(395,'Cadolzburg','Cadolzburg',now(),now()),(396,'Cairo','Cairo',now(),now()),(397,'Cambridge','Cambridge',now(),now()),(398,'Cambridge, Mass . ','Cambridge',now(),now()),(399,'Cambridge, Mass .; London','Cambridge',now(),now()),(400,'Cambridge, new York','Cambridge',now(),now()),(401,'Canandaigua; new York','Canandaigua',now(),now()),(402,'Canberra','Canberra',now(),now()),(403,'Caracas','Caracas',now(),now()),(404,'Caerdydd(Cardiff)','Cardiff',now(),now()),(405,'Carlton','Carlton',now(),now()),(406,'Carlton North(Melbourne)','Carlton North',now(),now()),(407,'Casale Monferato','Casale Monferrato',now(),now()),(408,'Casale Monferrato','Casale Monferrato',now(),now()),(409,'Čáslav','Čáslav',now(),now()),(410,'Cassel','Cassel',now(),now()),(411,'Červený Kostelec','Červený Kostelec',now(),now()),(412,'České Budějovice','Ceske Budejovice',now(),now()),(413,'Česlice','Čestlice',now(),now()),(414,'Champaign, London, Dublin','Champaign',now(),now()),(415,'Cham','Chams',now(),now()),(416,'Changsha','Changsha',now(),now()),(417,'Chapeco','Chapecó',now(),now()),(418,'Chapel Hill','Chapel Hill',now(),now()),(419,'Charleroi','Charleroi',now(),now()),(420,'Charleston','Charleston',now(),now()),(421,'Charlieu','Charlieu',now(),now()),(422,'Charlottenburg','Charlottenburg',now(),now()),(423,'Charlottenlund','Charlottenlund',now(),now()),(424,'Charlottetown','Charlottetown',now(),now()),(425,'Chattanooga','Chattanooga',now(),now()),(426,'Cheektowaga','Cheektowaga ',now(),now()),(427,'Chemnitz','Chemnitz',now(),now()),(428,'Czernowitz','Chernivtsi',now(),now()),(429,'Chicago','Chicago',now(),now()),(430,'Chicago; new York','Chicago',now(),now()),(431,'Kishenev','Chisinau',now(),now()),(432,'Chorzów','Chorzów',now(),now()),(433,'Christansstad','Christiansted',now(),now()),(434,'Chur','Chur',now(),now()),(435,'Teschen','Cieszyn',now(),now()),(436,'Cleveland','Cleveland',now(),now()),(437,'Cleveland, new York','Cleveland',now(),now()),(438,'Cluj','Cluj - Napoca',now(),now()),(439,'Cluj / Kolozsvár','Cluj - Napoca',now(),now()),(440,'Cluj - Napoca','Cluj - Napoca',now(),now()),(441,'Coburg','Coburg',now(),now()),(442,'Colmar','Colmar',now(),now()),(443,'Cologne','Cologne',now(),now()),(444,'Köln','Cologne',now(),now()),(445,'Köln, Berlin','Cologne',now(),now()),(446,'Köln; Berlin','Cologne',now(),now()),(447,'Köln; Einsiedeln; Zürich','Cologne',now(),now()),(448,'Köln; Krefeld','Cologne',now(),now()),(449,'Köln; Olten','Cologne',now(),now()),(450,'Köln; Saignelégier','Cologne',now(),now()),(451,'Köln; Zürich','Cologne',now(),now()),(452,'Köln / Stuttgart / Erlangen','Cologne',now(),now()),(453,'Colombia','Colombia',now(),now()),(454,'Conception','Concepción',now(),now()),(455,'Connecticut','Connecticut',now(),now()),(456,'Constantinopel [fingiert]','Constantinople',now(),now()),(457,'Kopenhagen','Copenhagen',now(),now()),(458,'København','Copenhagen',now(),now()),(459,'Corminboeuf','Corminboeuf',now(),now()),(460,'Cormons(Krmin)','Cormons',now(),now()),(461,'Cothen','Cothen',now(),now()),(462,'Cottbus','Cottbus',now(),now()),(463,'Culemborg','Culemborg',now(),now()),(464,'Cuneo','Cuneo',now(),now()),(465,'Curaçao','Curaçao',now(),now()),(466,'Československo','Czechoslovakia',now(),now()),(467,'Dainfern','Dainfern',now(),now()),(468,'Dakovo','Đakovo',now(),now()),(469,'Darmstadt','Darmstadt',now(),now()),(470,'Darmstadt; Düsseldorf','Darmstadt',now(),now()),(471,'Darmstadt; Genf','Darmstadt',now(),now()),(472,'Darmstadt: Primus','Darmstadt',now(),now()),(473,'Dassel','Dassel',now(),now()),(474,'Herder','De Herder',now(),now()),(475,'Deal','Deal',now(),now()),(476,'Debrecen','Debrecen',now(),now()),(477,'Delft','Delft',now(),now()),(478,'Delft [?]','Delft',now(),now()),(479,'Dendermonde','Dendermonde',now(),now()),(480,'Denver','Denver',now(),now()),(481,'Depok','Depok',now(),now()),(482,'Detmold','Detmold',now(),now()),(483,'Detroit','Detroit',now(),now()),(484,'Deurle','Deurle',now(),now()),(485,'Deurne','Deurne',now(),now()),(486,'Deventer','Deventer',now(),now()),(487,'Diepenbeek','Diepenbeek',now(),now()),(488,'Diest','Diest',now(),now()),(489,'Dietikon; Zürich','Dietikon',now(),now()),(490,'Deelbeek - Bruxelles','Dilbeek',now(),now()),(491,'Dilbeek','Dilbeek',now(),now()),(492,'Dillenburg','Dillenburg',now(),now()),(493,'Dinslaken','Dinslaken',now(),now()),(494,'Diss . Amsterdam','Diss',now(),now()),(495,'Ditzingen','Ditzingen',now(),now()),(496,'Dob','Dob',now(),now()),(497,'Dob pri Domalah','Dob',now(),now()),(498,'Dokkum','Dokkum',now(),now()),(499,'Don Mills, Ontario','Don Mills',now(),now()),(500,'Donauwörth','Donauwörth',now(),now()),(501,'Doorn','Doorn',now(),now()),(502,'Dopiewo','Dopiewo',now(),now()),(503,'Dordrecht','Dordrecht',now(),now()),(504,'Dornbirn','Dornbirn',now(),now()),(505,'Dortmund','Dortmund',now(),now()),(506,'Drachten','Drachten',now(),now()),(507,'Dreieich','Dreieich',now(),now()),(508,'Dresden','Dresden',now(),now()),(509,'Dresden - Radebeul','Dresden',now(),now()),(510,'Dresden - Weinböhla','Dresden',now(),now()),(511,'Dresden, Neustadt','Dresden',now(),now()),(512,'Dresden; Leipzig','Dresden',now(),now()),(513,'Dresden; Leipzig; Berlin','Dresden',now(),now()),(514,'Griebeuren','Driebergen',now(),now()),(515,'Driebergen - Rijsenburg','Driebergen - Rijsenburg',now(),now()),(516,'Dronten','Dronten',now(),now()),(517,'Dublin','Dublin',now(),now()),(518,'Duisburg','Duisburg',now(),now()),(519,'Duisburg; Cleve','Duisburg',now(),now()),(520,'Dülmen','Dülmen',now(),now()),(521,'Dülmen i . Westf . ','Dülmen',now(),now()),(522,'Dülmen - Hiddingsel','Dülmen',now(),now()),(523,'Dunedin','Dunedin',now(),now()),(524,'Düsseldorf','Düsseldorf',now(),now()),(525,'Düsseldorf, Frankfurt a . M . ','Düsseldorf',now(),now()),(526,'Düsseldorf, Köln','Düsseldorf',now(),now()),(527,'Düsseldorf; Köln','Düsseldorf',now(),now()),(528,'Düsseldorf / Frankfurt am Main','Düsseldorf',now(),now()),(529,'Düsseldorf / Köln','Düsseldorf',now(),now()),(530,'Düsselthal','Düsseldorf',now(),now()),(531,'East Lansing, MI','East Lansing',now(),now()),(532,'Oosterlittens / Amsterdam','Easterlittens',now(),now()),(533,'Ebenhausen bei München','Ebenhausen',now(),now()),(534,'Edinburgh','Edinburgh',now(),now()),(535,'Edinburgh; London','Edinburgh',now(),now()),(536,'Edmonton','Edmonton',now(),now()),(537,'Eger','Eger',now(),now()),(538,'Egnach','Egnach',now(),now()),(539,'Eindhoven','Eindhoven',now(),now()),(540,'Einsiedeln','Einsiedeln',now(),now()),(541,'Einsiedeln; Köln','Einsiedeln',now(),now()),(542,'Einsiedeln; Zürich; Köln','Einsiedeln',now(),now()),(543,'Eisenstadt','Eisenstadt',now(),now()),(544,'Eisingen','Eisingen',now(),now()),(545,'Eisleben','Eisleben',now(),now()),(546,'Eîlingen','Eislingen',now(),now()),(547,'Elberfeld','Elberfeld',now(),now()),(548,'Elmshorn','Elmshorn',now(),now()),(549,'ELTE, Budapest','Elte',now(),now()),(550,'Emden','Emden',now(),now()),(551,'Emmerich','Emmerich',now(),now()),(552,'Emsdetten','Emsdetten',now(),now()),(553,'Ende','Ende',now(),now()),(554,'Enkhuizen, Haarlem','Enkhuizen',now(),now()),(555,'Enschede','Enschede',now(),now()),(556,'Ephrata','Ephrata',now(),now()),(557,'Eppegem','Eppegem',now(),now()),(558,'Erbil','Erbil',now(),now()),(559,'Erftstadt','Erftstadt',now(),now()),(560,'Erfurt','Erfurt',now(),now()),(561,'Erlangen','Erlangen',now(),now()),(562,'Erlangen; Nürnberg','Erlangen',now(),now()),(563,'Ertvelde','Ertvelde',now(),now()),(564,'Eschwege','Eschwege',now(),now()),(565,'Espergaerde','Espergærde',now(),now()),(566,'Esplugues de Llobregat','Esplugues de Llobregat',now(),now()),(567,'Essen','Essen',now(),now()),(568,'Essen; Rotterdam','Essen',now(),now()),(569,'Esslingen','Esslingen am Neckar',now(),now()),(570,'Esztergom','Esztergom',now(),now()),(571,'Ettlingen','Ettlingen',now(),now()),(572,'Eupen','Eupen',now(),now()),(573,'Evanston','Evanston',now(),now()),(574,'Evanston; Chicago','Evanston',now(),now()),(575,'Faenza','Faenza',now(),now()),(576,'Fairfax','Fairfax',now(),now()),(577,'Fairfax Station, Va . ','Fairfax',now(),now()),(578,'Fairford','Fairford',now(),now()),(579,'Faridabad','Faridabad',now(),now()),(580,'Farnham','Farnham',now(),now()),(581,'Farnham; Burlington','Farnham',now(),now()),(582,'Fécamp','Fécamp',now(),now()),(583,'Feldafing','Feldafing',now(),now()),(584,'Feldkirch','Feldkirch',now(),now()),(585,'Fellbach','Fellbach',now(),now()),(586,'Fernwald','Fernwald',now(),now()),(587,'Flensburg','Flensburg',now(),now()),(588,'Firenze','Florence',now(),now()),(589,'Firenze, Milano','Florence',now(),now()),(590,'Florence','Florence',now(),now()),(591,'Florential','Florential',now(),now()),(592,'Forest Grove','Forest Grove',now(),now()),(593,'Forest Hills N . Y . ','Forest Hills',now(),now()),(594,'Formello','Formello',now(),now()),(595,'Francestown, NH','Francestown',now(),now()),(596,'Franchesse','Franchesse',now(),now()),(597,'Franckfurt','Frankfurt',now(),now()),(598,'Franckfurt; Leipzig; Hannover','Frankfurt',now(),now()),(599,'Franckfurt; Leipzig; Hannover; Wolfenbüttel','Frankfurt',now(),now()),(600,'Franckfurt am Mäyn','Frankfurt',now(),now()),(601,'Franckfurt, Leipzig, Breslau','Frankfurt',now(),now()),(602,'Franckfurt; Leipzig; Hannover','Frankfurt',now(),now()),(603,'Franckfut am Mayn','Frankfurt',now(),now()),(604,'Franeker','Frankfurt',now(),now()),(605,'Frankfurt','Frankfurt',now(),now()),(606,'Frankfurt; Leipzig; Hannover; Wolffenbüttel','Frankfurt',now(),now()),(607,'Frankfurt a . M . ','Frankfurt',now(),now()),(608,'Frankfurt a . M .; Berlin','Frankfurt',now(),now()),(609,'Frankfurt a . M .; Berlin; Wien','Frankfurt',now(),now()),(610,'Frankfurt a . M .; Hamburg','Frankfurt',now(),now()),(611,'Frankfurt a . M .; Leipzig','Frankfurt',now(),now()),(612,'Frankfurt a . M .; Olten; Wien','Frankfurt',now(),now()),(613,'Frankfurt a . M .; Wien','Frankfurt',now(),now()),(614,'Frankfurt a . M .; Wien; Zürich','Frankfurt',now(),now()),(615,'Frankfurt a . M .; Zürich','Frankfurt',now(),now()),(616,'Frankfurt a . M . ','Frankfurt',now(),now()),(617,'Frankfurt A . M . u . a . ','Frankfurt',now(),now()),(618,'Frankfurt a . M ., Berlin','Frankfurt',now(),now()),(619,'Frankfurt a . M ., Köln','Frankfurt',now(),now()),(620,'Frankfurt a . M ., new York','Frankfurt',now(),now()),(621,'Frankfurt am Main','Frankfurt',now(),now()),(622,'Frankfurt am Main, Leipzig','Frankfurt',now(),now()),(623,'Frankfurt am Main, Leipzig, Breslau','Frankfurt',now(),now()),(624,'Frankfurt am Main, Wien','Frankfurt',now(),now()),(625,'Frankfurt am Main; Herborn','Frankfurt',now(),now()),(626,'Frankfurt am Main; Kassel','Frankfurt',now(),now()),(627,'Frankfurt am Main; Leipzig','Frankfurt',now(),now()),(628,'Frankfurt am Main; Leipzig; Jena','Frankfurt',now(),now()),(629,'Frankfurt am Main; Leipzig; Liegnitz','Frankfurt',now(),now()),(630,'Frankfurt am Main; Leipzig; Liegnitz; Jena','Frankfurt',now(),now()),(631,'Frankfurt am Main; Leipzih','Frankfurt',now(),now()),(632,'Frankfurt am Main; Rotterdam','Frankfurt',now(),now()),(633,'Frankfurt am Main / Berlin / Leipzig','Frankfurt',now(),now()),(634,'Frankfurt am Main / Bern / new York / Paris','Frankfurt',now(),now()),(635,'Frankfurt am Main / Leipzig','Frankfurt',now(),now()),(636,'Frankfurt an der Oder','Frankfurt',now(),now()),(637,'Frankfurt etc','Frankfurt',now(),now()),(638,'Frankfurt etc . ','Frankfurt',now(),now()),(639,'Frankfurt, Berlin, Bern, new York, Paris, Wien','Frankfurt',now(),now()),(640,'Frankfurt, Leipzig','Frankfurt',now(),now()),(641,'Frankfurt, Main','Frankfurt',now(),now()),(642,'Frankfurt; Herborn','Frankfurt',now(),now()),(643,'Frankfurt; Leipzig','Frankfurt',now(),now()),(644,'Frankfurt; Leipzig [fingiert]','Frankfurt',now(),now()),(645,'Frankfurt / Main','Frankfurt',now(),now()),(646,'Frankfurt / Main; Berlin','Frankfurt',now(),now()),(647,'Frankfurt / Main; Leipzig','Frankfurt',now(),now()),(648,'Frankfurter Buchmesse 1989','Frankfurt',now(),now()),(649,'Frauenfeld','Frauenfeld',now(),now()),(650,'Frechen','Frechen',now(),now()),(651,'Fredensborg','Fredensborg',now(),now()),(652,'Fredericia','Fredericia',now(),now()),(653,'Fredonia','Fredonia',now(),now()),(654,'Freedom, California','Freedom',now(),now()),(655,'Christiania','Freetown Christiania',now(),now()),(656,'Freiburg','Freiburg im Breisgau',now(),now()),(657,'Freiburg i . Br . ','Freiburg im Breisgau',now(),now()),(658,'Freiburg i . Br .; Basel; Wien','Freiburg im Breisgau',now(),now()),(659,'Freiburg i . Br .; Heidelberg','Freiburg im Breisgau',now(),now()),(660,'Freiburg i . Br .; Olten','Freiburg im Breisgau',now(),now()),(661,'Freiburg im Breisgau','Freiburg im Breisgau',now(),now()),(662,'Freiburg im Breisgau; Basel','Freiburg im Breisgau',now(),now()),(663,'Freiburg; Basel; Wien','Freiburg im Breisgau',now(),now()),(664,'Freiburg; Wien; Basel','Freiburg im Breisgau',now(),now()),(665,'Freiburg / Basel / Wien','Freiburg im Breisgau',now(),now()),(666,'Freiburg / Breisgau, Wien, Basel','Freiburg im Breisgau',now(),now()),(667,'Fribourg','Fribourg',now(),now()),(668,'Friedberg','Friedberg',now(),now()),(669,'Friedrich','Friedrich',now(),now()),(670,'Friedrichshafen','Friedrichshafen',now(),now()),(671,'Fulda','Fulda',now(),now()),(672,'[Fürth]','Fürth',now(),now()),(673,'Galten','Galten',now(),now()),(674,'Garden City','Garden City',now(),now()),(675,'Danzig','Gdańsk',now(),now()),(676,'Danzig - Langfuhr','Gdańsk',now(),now()),(677,'Danzig; Leipzig','Gdańsk',now(),now()),(678,'Gdańsk','Gdańsk',now(),now()),(679,'Gdynia','Gdynia',now(),now()),(680,'Geel','Geel',now(),now()),(681,'Gelnhausen; Berlin - Dahlem','Gelnhausen',now(),now()),(682,'Gembloux','Gembloux',now(),now()),(683,'Genève','Geneva',now(),now()),(684,'Genève, Paris','Geneva',now(),now()),(685,'Ženeva','Geneva',now(),now()),(686,'Genova','Genoa',now(),now()),(687,'Gentbrugge','Gentbrugge',now(),now()),(688,'Gera','Gera',now(),now()),(689,'Gerlingen','Gerlingen',now(),now()),(690,'Germantown','Germantown',now(),now()),(691,'Gernsbach','Gernsbach',now(),now()),(692,'Gand','Ghent',now(),now()),(693,'Gent','Ghent',now(),now()),(694,'Gent, Antwerpen, Brussel, Amersfoort','Ghent',now(),now()),(695,'Gent, Utrecht','Ghent',now(),now()),(696,'Giessen','Giessen',now(),now()),(697,'Giessen; Basel','Giessen',now(),now()),(698,'Gifkendorf','Gifkendorf',now(),now()),(699,'Gladbeck','Gladbeck',now(),now()),(700,'Glasgow','Glasgow',now(),now()),(701,'Glostrup','Glostrup',now(),now()),(702,'Gloucester','Gloucester',now(),now()),(703,'Glückstadt','Glückstadt',now(),now()),(704,'Gorinchem','Gorinchem',now(),now()),(705,'Gorica','Gorizia',now(),now()),(706,'Gorizia','Gorizia',now(),now()),(707,'Görlitz','Görlitz',now(),now()),(708,'Görlitz; Leipzig','Görlitz',now(),now()),(709,'Gornji Milanovac','Gornji Milanovac',now(),now()),(710,'Goslar','Goslar',now(),now()),(711,'Gossau Zürich; Hamburg','Gossau',now(),now()),(712,'Gossau, Zürich; Hamburg; Salzburg','Gossau',now(),now()),(713,'Gotha','Gotha',now(),now()),(714,'Göteborg','Gothenburg',now(),now()),(715,'Göttingen','Göttingen',now(),now()),(716,'Grand Rapids','Grand Rapids',now(),now()),(717,'Grand Rapids, Michigan','Grand Rapids',now(),now()),(718,'Graubünden','Graubünden',now(),now()),(719,'Graz','Graz',now(),now()),(720,'Graz; Wien','Graz',now(),now()),(721,'Graz; Wien; Köln','Graz',now(),now()),(722,'Graz / Wien','Graz',now(),now()),(723,'Great Barrington, Mass . ','Great Barrington',now(),now()),(724,'Greensboro','Greensboro',now(),now()),(725,'Greifswald','Greifswald',now(),now()),(726,'Greiz','Greiz',now(),now()),(727,'Grenoble','Grenoble',now(),now()),(728,'Grimma','Grimma',now(),now()),(729,'Gronau','Gronau ',now(),now()),(730,'Broningen','Groningen',now(),now()),(731,'Groningen','Groningen',now(),now()),(732,'Groningen u . a . ','Groningen',now(),now()),(733,'Groningen / Batavia','Groningen',now(),now()),(734,'Groot - Bijgaarden','Groot - Bijgaarden',now(),now()),(735,'Grossenhain','Großenhain',now(),now()),(736,'Guangxi','Guangxi',now(),now()),(737,'Guangzhou','Guangzhou',now(),now()),(738,'Guiyang','Guiyang',now(),now()),(739,'Gurgaon','Gurgaon',now(),now()),(740,'Gütersloh','Gütersloh',now(),now()),(741,'Gyeonggi -do','Gyeonggi',now(),now()),(742,'Győr','Győr',now(),now()),(743,'Gyula','Gyula',now(),now()),(744,'Haan','Haan',now(),now()),(745,'Neustadt a . H . ','Haardt',now(),now()),(746,'Newstadt an der Hardt','Haardt',now(),now()),(747,'Haarlem','Haarlem',now(),now()),(748,'Haderslev','Haderslev',now(),now()),(749,'Hagen','Hagen',now(),now()),(750,'Hagen i . W .; Darmstadt','Hagen',now(),now()),(751,'Hago','Hago',now(),now()),(752,'Halberstadt','Halberstadt',now(),now()),(753,'Hallbergmoos','Hallbergmoos',now(),now()),(754,'Halle','Halle',now(),now()),(755,'Halle(Saale)','Halle',now(),now()),(756,'Halle a . d . S . ','Halle',now(),now()),(757,'Halle a . d . S .; Berlin','Halle',now(),now()),(758,'Halle, Berlin','Halle',now(),now()),(759,'Halle, Saale','Halle',now(),now()),(760,'Halle, Saale; Leipzig','Halle',now(),now()),(761,'Hamborn','Hamborn',now(),now()),(762,'[Hamburg ?]','Hamburg',now(),now()),(763,'Altona','Hamburg',now(),now()),(764,'Hamburg','Hamburg',now(),now()),(765,'Hamburg [?]','Hamburg',now(),now()),(766,'Hamburg - Grossborstel','Hamburg',now(),now()),(767,'Hamburg, Jena','Hamburg',now(),now()),(768,'Hamburg; Berlin','Hamburg',now(),now()),(769,'Hamburg; Frankfurt am Main; Leipzig','Hamburg',now(),now()),(770,'Hamburg; Leipzig','Hamburg',now(),now()),(771,'Hamburg; Leipzih','Hamburg',now(),now()),(772,'Hamburg; Luzern','Hamburg',now(),now()),(773,'Hamburg; Ratzeburg','Hamburg',now(),now()),(774,'Hamburg; Stuttgart','Hamburg',now(),now()),(775,'Hamburg; Wien','Hamburg',now(),now()),(776,'Hamburg / Zürich','Hamburg',now(),now()),(777,'Hämeenlinna','Hämeenlinna',now(),now()),(778,'Hameln','Hamelin',now(),now()),(779,'Hamm','Hamm',now(),now()),(780,'Hamme','Hamme',now(),now()),(781,'Achel','Hamont - Achel',now(),now()),(782,'Hanau','Hanau',now(),now()),(783,'Hanau; Frankfurt am Main','Hanau',now(),now()),(784,'Hanau / Main','Hanau',now(),now()),(785,'Handforth','Handforth',now(),now()),(786,'Handzame','Handzame',now(),now()),(787,'Hannoversch - Münden','Hann . Münden',now(),now()),(788,'Hanoi','Hanoi',now(),now()),(789,'Hannover','Hanover',now(),now()),(790,'Hannover; Hildesheim / Hannover; Wolffenbüttel','Hanover',now(),now()),(791,'Hannover; Wolfenbüttel','Hanover',now(),now()),(792,'Hannover - Kirchrode','Hanover',now(),now()),(793,'Hannover, Hildesheim','Hanover',now(),now()),(794,'Hannover, Wolfenbüttel','Hanover',now(),now()),(795,'Hannover, Wolfenbüttel, Frankfurt am Main, Leipzig','Hanover',now(),now()),(796,'Hannover; Berlin','Hanover',now(),now()),(797,'Hannover; Hildesheim','Hanover',now(),now()),(798,'Hannover; Woffenbüttel','Hanover',now(),now()),(799,'Hannover; Wolfenbüttel','Hanover',now(),now()),(800,'Harmondsworth','Harmondsworth',now(),now()),(801,'Härnösand','Härnösand',now(),now()),(802,'Harrow','Harrow',now(),now()),(803,'Harsewinkel','Harsewinkel',now(),now()),(804,'Hasselt','Hasselt',now(),now()),(805,'Hasselt / Haarlem','Hasselt',now(),now()),(806,'Hauberg','Hauberg',now(),now()),(807,'Habana','Havana',now(),now()),(808,'Havlíčkův Brod','Havlíčkův Brod',now(),now()),(809,'Hawthorn, Vic .; London','Hawthorn',now(),now()),(810,'Heemstede','Heemstede',now(),now()),(811,'Heerlen','Heerlen',now(),now()),(812,'Heide in Holstein','Heide',now(),now()),(813,'Heide / Holstein','Heide',now(),now()),(814,'Heidelberg','Heidelberg',now(),now()),(815,'Heidelberg; Luzern','Heidelberg',now(),now()),(816,'Heidelberg; Marburg a . N . ','Heidelberg',now(),now()),(817,'Heidelberg; Nürnberg','Heidelberg',now(),now()),(818,'Heidelberg / Darmstadt','Heidelberg',now(),now()),(819,'Heidenheim','Heidenheim',now(),now()),(820,'Heilbronn','Heilbronn',now(),now()),(821,'Heiligenstadt','Heiligenstadt ',now(),now()),(822,'Helmond','Helmond',now(),now()),(823,'Helmstädt','Helmstedt',now(),now()),(824,'Helsingborg','Helsingborg',now(),now()),(825,'Helsingör','Helsingør',now(),now()),(826,'Helsingfors','Helsinki',now(),now()),(827,'Helsingissä','Helsinki',now(),now()),(828,'Helsinki','Helsinki',now(),now()),(829,'Helsinki / Porvoo','Helsinki',now(),now()),(830,'Herborn','Herborn',now(),now()),(831,'Herbstein','Herbstein',now(),now()),(832,'Herford','Herford',now(),now()),(833,'Herrsching','Herrsching',now(),now()),(834,'Herzelia','Herzliya',now(),now()),(835,'Herzogenrath','Herzogenrath',now(),now()),(836,'Heusden; Oosterbaers','Heusden',now(),now()),(837,'Hilden','Hilden',now(),now()),(838,'Hildesheim','Hildesheim',now(),now()),(839,'Hildesheim; Amsterdam','Hildesheim',now(),now()),(840,'Hiltrup','Hiltrup',now(),now()),(841,'Hilversum','Hilversum',now(),now()),(842,'Hodonín','Hodonín',now(),now()),(843,'Hof','Hof',now(),now()),(844,'Höganäs','Höganäs',now(),now()),(845,'Holešova na Morave','Holeov',now(),now()),(846,'Holte','Holte',now(),now()),(847,'Honesdale, Pennsylvania','Honesdale',now(),now()),(848,'Honseldale, Pennsylvania','Honesdale',now(),now()),(849,'Honolulu','Honolulu',now(),now()),(850,'Hoogstraaten','Hoogstraten',now(),now()),(851,'Hoogstraten','Hoogstraten',now(),now()),(852,'Hoogstraten, Tilburg','Hoogstraten',now(),now()),(853,'Hoorn','Hoorn',now(),now()),(854,'Hopewell, N . Y . ','Hopewell',now(),now()),(855,'Hottingen','Hottingen',now(),now()),(856,'Hottingen, Zürich','Hottingen',now(),now()),(857,'Houten','Houten',now(),now()),(858,'Hradec Králové','Hradec Králové',now(),now()),(859,'Huis ter Heide','Huis ter Heide',now(),now()),(860,'Hull','Hull',now(),now()),(861,'Huy','Huy',now(),now()),(862,'Hyogo','Hyogo',now(),now()),(863,'Iasi','Iași',now(),now()),(864,'Ibbenbüren','Ibbenbüren',now(),now()),(865,'IJmuiden','IJmuiden',now(),now()),(866,'Ilirska Bistrica','Ilirska Bistrica',now(),now()),(867,'Illinois','Illinois',now(),now()),(868,'Immensee','Immensee',now(),now()),(869,'Indianapolis','Indianapolis',now(),now()),(870,'Ingelheim am Rhein','Ingelheim am Rhein',now(),now()),(871,'Ingolstadt','Ingolstadt',now(),now()),(872,'Innsbruck','Innsbruck',now(),now()),(873,'Innsbruck; München; Freiburg','Innsbruck',now(),now()),(874,'Innsbruck; Wien; Bozen','Innsbruck',now(),now()),(875,'Inowrocław','Inowroclaw',now(),now()),(876,'Iowa City, University of Iowa','Iowa',now(),now()),(877,'Iowa City, West Branch','Iowa',now(),now()),(878,'Isle of Skye','Isle of Skye',now(),now()),(879,'Istanbul','Istanbul',now(),now()),(880,'Itzehoe','Itzehoe',now(),now()),(881,'Ivry - sur - Seine','Ivry - sur - Seine',now(),now()),(882,'Ixelles - Bruxelles','Ixelles',now(),now()),(883,'Izabelin','Izabelin',now(),now()),(884,'Jabbeke','Jabbeke',now(),now()),(885,'Jablonec nad Nisou','Jablonec nad Nisou',now(),now()),(886,'Djakarta','Jakarta',now(),now()),(887,'Jakarta','Jakarta',now(),now()),(888,'Järna','Järna',now(),now()),(889,'Jena','Jena',now(),now()),(890,'Jena, Frankfurt a . M . ','Jena',now(),now()),(891,'Jena, Hamburg','Jena',now(),now()),(892,'Jena; Hamburg','Jena',now(),now()),(893,'Jena; HamburgHamburg','Jena',now(),now()),(894,'Jerusalem','Jerusalem',now(),now()),(895,'Jestetten','Jestetten',now(),now()),(896,'Iglau','Jihlava',now(),now()),(897,'Jinan','Jinan',now(),now()),(898,'Jinočany','Jinočany ',now(),now()),(899,'Johannesburg','Johannesburg',now(),now()),(900,'Jyväskylä','Jyväskylä',now(),now()),(901,'Kaapstad','Kaapstad',now(),now()),(902,'Kaapstad; Pretoria','Kaapstad',now(),now()),(903,'Kaldenkirchen','Kaldenkirchen',now(),now()),(904,'Kalisz','Kalisz',now(),now()),(905,'Kalmthout - Anvers','Kalmthout',now(),now()),(906,'Kaltenkirchen','Kaltenkirchen',now(),now()),(907,'Kampen','Kampen',now(),now()),(908,'Kaohsiung','Kaohsiung',now(),now()),(909,'Kapellen','Kapellen',now(),now()),(910,'Kaposvár','Kaposvár',now(),now()),(911,'Karachi','Karachi',now(),now()),(912,'Karlovy Vary ','Karlovy Vary',now(),now()),(913,'Karlshamn','Karlshamn',now(),now()),(914,'Karlsruhe','Karlsruhe',now(),now()),(915,'Kasel, Frankfurt am Main','Kasel',now(),now()),(916,'Kassel','Kassel',now(),now()),(917,'Kassel - Wilhelmshöhe','Kassel',now(),now()),(918,'Kassel; Basel','Kassel',now(),now()),(919,'Kassel; Frankfurt am Main; Leipzih','Kassel',now(),now()),(920,'Kassel; Stuttgart','Kassel',now(),now()),(921,'Kasterlee','Kasterlee',now(),now()),(922,'Katlijk','Katlijk',now(),now()),(923,'Katowice','Katowice',now(),now()),(924,'Kattowitz','Katowice',now(),now()),(925,'Kovno','Kaunas',now(),now()),(926,'Kaevlinge','Kävlinge',now(),now()),(927,'Kävlinge','Kävlinge',now(),now()),(928,'Kazaň','Kazan',now(),now()),(929,'Kecskemét','Kecskemét',now(),now()),(930,'Kefar Sava','Kefar Sava',now(),now()),(931,'Kempen','Kempen',now(),now()),(932,'Kempen - Niederrh . ','Kempen',now(),now()),(933,'Kempten','Kempten',now(),now()),(934,'Kent','Kent',now(),now()),(935,'Kevelaer','Kevelaer',now(),now()),(936,'Harkiv','Kharkiv',now(),now()),(937,'Kiel','Kiel',now(),now()),(938,'Kiev','Kiev',now(),now()),(939,'Kijev','Kiev',now(),now()),(940,'Kyiv','Kiev',now(),now()),(941,'Kyjiv','Kiev',now(),now()),(942,'Kila','Kíla',now(),now()),(943,'Celovec','Klagenfurt',now(),now()),(944,'Klagenfurt','Klagenfurt',now(),now()),(945,'Klagenfurt; Rosenheim','Klagenfurt',now(),now()),(946,'Kleve','Kleve',now(),now()),(947,'Klodzko','Klodzko',now(),now()),(948,'Kłodzko','Klodzko',now(),now()),(949,'Klosterneuburg','Klosterneuburg',now(),now()),(950,'Knokke - Heist','Knokke - Heist',now(),now()),(951,'Koblenz','Koblenz',now(),now()),(952,'Kochel','Kochel',now(),now()),(953,'Königsberg','Königsberg',now(),now()),(954,'Königsstein','Königstein im Taunus',now(),now()),(955,'Konjic','Konjic',now(),now()),(956,'Konstanz','Konstanz',now(),now()),(957,'Kontich / Anvers','Kontich',now(),now()),(958,'Koog aan de Zaan','Koog aan de Zaan',now(),now()),(959,'Koper','Koper',now(),now()),(960,'Korntal','Korntal - Münchingen',now(),now()),(961,'Kornwestheim','Kornwestheim',now(),now()),(962,'Kortenhoef . Stichting Collage','Kortenhoef',now(),now()),(963,'Courtrai','Kortrijk',now(),now()),(964,'Courtrai, Paris [&] Bruxelles','Kortrijk',now(),now()),(965,'Courtrai; Bruxelles; Paris','Kortrijk',now(),now()),(966,'Kortrijk','Kortrijk',now(),now()),(967,'Kassa(Kosice)','Košice',now(),now()),(968,'Košice','Košice',now(),now()),(969,'Košice, Žilina','Košice',now(),now()),(970,'Kostelec nad Cernými lesy','Kostelci nad Černými lesy',now(),now()),(971,'Köslin','Koszalin',now(),now()),(972,'Köthen','Köthen',now(),now()),(973,'Krakau','Krakau',now(),now()),(974,'Kraków','Krakau',now(),now()),(975,'Kralupy nad Vltavou','Kralupy nad Vltavou',now(),now()),(976,'Krefeld','Krefeld',now(),now()),(977,'Kroměříž','Kroměříž',now(),now()),(978,'Krommenie','Krommenie',now(),now()),(979,'Kronberg / Ts . ','Kronberg im Taunus',now(),now()),(980,'Kruševac','Kruševac',now(),now()),(981,'Kuala Lumpur; Oxford','Kuala Lumpur',now(),now()),(982,'Kückhoven - Erkelenz','Kückhoven',now(),now()),(983,'Kufstein','Kufstein',now(),now()),(984,'Kufstein; Wien; Rosenheim','Kufstein',now(),now()),(985,'Küsnacht','Küsnacht',now(),now()),(986,'Kutná Hora','Kutná Hora',now(),now()),(987,'La Grande Motte','La Grande - Motte',now(),now()),(988,'La Jolla, California','La Jolla',now(),now()),(989,'Lagos','Lagos',now(),now()),(990,'Lahnstein','Lahnstein',now(),now()),(991,'Lāhōr; Rāwalpindī; Karāčī','Lahore',now(),now()),(992,'Lahr','Lahr',now(),now()),(993,'Lanciano','Lanciano',now(),now()),(994,'Landeck','Landeck',now(),now()),(995,'Langemark','Langemark',now(),now()),(996,'Lanham u . a . ','Lanham',now(),now()),(997,'Lanham, MD','Lanham',now(),now()),(998,'Lantzville, B . C . ','Lantzville',now(),now()),(999,'Laren','Laren',now(),now()),(1000,'Łask','Łask',now(),now()),(1001,'Łask / Szczecin / Toruń','Łask',now(),now()),(1002,'Łask - Szczecin - Toruń','Łask',now(),now()),(1003,'Lauf bei Nürnberg','Lauf an der Pegnitz',now(),now()),(1004,'Lauf; Bern; Leipzig','Lauf an der Pegnitz',now(),now()),(1005,'Lausanne','Lausanne',now(),now()),(1006,'Laverstock','Laverstock and Ford',now(),now()),(1007,'Chambon - sur - Lignon','Le Chambon - sur - Lignon',now(),now()),(1008,'Leeuwarden','Leeuwarden',now(),now()),(1009,'Ljouwert','Leeuwarden',now(),now()),(1010,'Liegnitz','Legnica',now(),now()),(1011,'Leicester','Leicester',now(),now()),(1012,'Leiden','Leiden',now(),now()),(1013,'Leiden; Amsterdam','Leiden',now(),now()),(1014,'Leyde','Leiden',now(),now()),(1015,'[Leipzig]','Leipzig',now(),now()),(1016,'Leipzig','Leipzig',now(),now()),(1017,'Leipzig, Berlin','Leipzig',now(),now()),(1018,'Leipzig, Breslau','Leipzig',now(),now()),(1019,'Leipzig, Halle, Saale','Leipzig',now(),now()),(1020,'Leipzig, München','Leipzig',now(),now()),(1021,'Leipzig; Allendorf, Werra','Leipzig',now(),now()),(1022,'Leipzig; Amsterdam','Leipzig',now(),now()),(1023,'Leipzig; Berlin','Leipzig',now(),now()),(1024,'Leipzig; Dresden','Leipzig',now(),now()),(1025,'Leipzig; Frankfurt am Main','Leipzig',now(),now()),(1026,'Leipzig; Königsberg','Leipzig',now(),now()),(1027,'Leipzig; Meissen','Leipzig',now(),now()),(1028,'Leipzig; Pest','Leipzig',now(),now()),(1029,'Leipzig; Strassburg; Zürich','Leipzig',now(),now()),(1030,'Leipzig; Weimar','Leipzig',now(),now()),(1031,'Leipzig; Wien','Leipzig',now(),now()),(1032,'Leipzig; Wolfenbüttel','Leipzig',now(),now()),(1033,'Leipzig; Zürich','Leipzig',now(),now()),(1034,'Leizpig','Leipzig',now(),now()),(1035,'Lelystad','Lelystad',now(),now()),(1036,'Lemgo','Lemgo',now(),now()),(1037,'Lendava','Lendava',now(),now()),(1038,'Lengerich','Lengerich',now(),now()),(1039,'Leningrad','Leningrad',now(),now()),(1040,'Leningrad - Moskva','Leningrad',now(),now()),(1041,'Léon','León',now(),now()),(1042,'Leopoldstad / Léopoldville','Leopoldstadt',now(),now()),(1043,'Les Ulis; Paris','Les Ulis',now(),now()),(1044,'Leuven','Leuven',now(),now()),(1045,'Leuven, Gent','Leuven',now(),now()),(1046,'Louvain','Leuven',now(),now()),(1047,'Louvain - Paris - Rome - Zug','Leuven',now(),now()),(1048,'Louvain, Bruxelles','Leuven',now(),now()),(1049,'Louvain; Leipzig; London','Leuven',now(),now()),(1050,'Louvain; Tours','Leuven',now(),now()),(1051,'Löwen','Leuven',now(),now()),(1052,'Levallois - Perret','Levallois - Perret',now(),now()),(1053,'Leverkusen','Leverkusen',now(),now()),(1054,'Levice','Levice',now(),now()),(1055,'Levoca','Levoča',now(),now()),(1056,'Lexington','Lexington',now(),now()),(1057,'Libourne','Libourne',now(),now()),(1058,'Liège','Liège',now(),now()),(1059,'Liege(Unveroeffentlichte Diplomarbeit)','Liège',now(),now()),(1060,'Liége, Bruxelles','Liège',now(),now()),(1061,'Liège; Grivegnée','Liège',now(),now()),(1062,'Liège; Paris','Liège',now(),now()),(1063,'Luik','Liège',now(),now()),(1064,'Lier','Lier',now(),now()),(1065,'Lierskogen','Lierskogen',now(),now()),(1066,'Lille','Lille',now(),now()),(1067,'Miraflores','Lima',now(),now()),(1068,'Limburg','Limburg',now(),now()),(1069,'Linz','Linz',now(),now()),(1070,'Linz a . D . ','Linz',now(),now()),(1071,'Lisboa','Lisbon',now(),now()),(1072,'Lisbon','Lisbon',now(),now()),(1073,'Lissabon','Lisbon',now(),now()),(1074,'Litomyšl','Litomyšl',now(),now()),(1075,'Lerpwl','Liverpool',now(),now()),(1076,'Liverpool','Liverpool',now(),now()),(1077,'Ljbljana','Ljubljana',now(),now()),(1078,'Ljubljana','Ljubljana',now(),now()),(1079,'Ljubljana / Budapest','Ljubljana',now(),now()),(1080,'Llandybie','Llandybie',now(),now()),(1081,'Llandysul','Llandysul',now(),now()),(1082,'Lleida','Lleida',now(),now()),(1083,'Locarno','Locarno',now(),now()),(1084,'Lochem','Lochem',now(),now()),(1085,'Łódź','Łódź',now(),now()),(1086,'Łomża','Łomża',now(),now()),(1087,'London','London',now(),now()),(1088,'London / new York','London',now(),now()),(1089,'London and Felling - on - Tyne; new-York and Melbourne','London',now(),now()),(1090,'London, Melbourne','London',now(),now()),(1091,'London, new York','London',now(),now()),(1092,'London; Antwerp; Brussels; Paris','London',now(),now()),(1093,'London; Baltimore','London',now(),now()),(1094,'London; Boston','London',now(),now()),(1095,'London; Boston; Sydney; etc . ','London',now(),now()),(1096,'London; Chicago','London',now(),now()),(1097,'London; Connecticut','London',now(),now()),(1098,'London; Dublin','London',now(),now()),(1099,'London; Edinburgh; Antwerp; Brussels; Paris','London',now(),now()),(1100,'London; Evanston','London',now(),now()),(1101,'London; Glasgow','London',now(),now()),(1102,'London; Leiden','London',now(),now()),(1103,'London; new York','London',now(),now()),(1104,'London; new York; Toronto','London',now(),now()),(1105,'London; new York; Victoria','London',now(),now()),(1106,'London; Paris','London',now(),now()),(1107,'London; Sydney; Toronto','London',now(),now()),(1108,'London; Toronto; Melbourne','London',now(),now()),(1109,'Londyn','London',now(),now()),(1110,'Los Angeles','Los Angeles',now(),now()),(1111,'Los Angeles, USA','Los Angeles',now(),now()),(1112,'Lübeck','Lübeck',now(),now()),(1113,'Lübeck; Berlin; Leipzig','Lübeck',now(),now()),(1114,'Lübeck; Frankfurt am Main','Lübeck',now(),now()),(1115,'Lübeck; Grünau','Lübeck',now(),now()),(1116,'Lübeck; Grünau; Nürmberg','Lübeck',now(),now()),(1117,'Lübeck; Hamburg','Lübeck',now(),now()),(1118,'Lübeck; Jena','Lübeck',now(),now()),(1119,'Lublin','Lublin',now(),now()),(1120,'Luzern','Lucerne',now(),now()),(1121,'Luzern - Frankfurt / Main','Lucerne',now(),now()),(1122,'Luzern; München','Lucerne',now(),now()),(1123,'Luzern; Stuttgart','Lucerne',now(),now()),(1124,'Lüdenscheid','Lüdenscheid',now(),now()),(1125,'Lund','Lund',now(),now()),(1126,'Lüneburg','Lüneburg',now(),now()),(1127,'Lütgendortmund','Lütgendortmund',now(),now()),(1128,'Luxemburg','Luxembourg',now(),now()),(1129,'Luzarches','Luzarches',now(),now()),(1130,'L\'viv','Lviv',now(),now()),(1131,'Lwów','Lviv',now(),now()),(1132,'Lwów; Złoczów','Lviv',now(),now()),(1133,'Lwów; Zloczów','Lviv',now(),now()),(1134,'Lyon','Lyon',now(),now()),(1135,'Lysaker','Lysaker',now(),now()),(1136,'Maarssen','Maarssen',now(),now()),(1137,'Maasbree','Maasbree',now(),now()),(1138,'Maastr.','Maastricht',now(),now()),(1139,'Maastricht','Maastricht',now(),now()),(1140,'Maastricht/Brussel','Maastricht',now(),now()),(1141,'Maestricht-Paris-Bruxelles','Maastricht',now(),now()),(1142,'Madison','Madison',now(),now()),(1143,'Madras','Madras',now(),now()),(1144,'Madrid','Madrid',now(),now()),(1145,'Madrid, Barcelona','Madrid',now(),now()),(1146,'Madrid, Zaragoza','Madrid',now(),now()),(1147,'Madrid; Barcelona','Madrid',now(),now()),(1148,'Madrid; Buenos Aires; Ciudad de México','Madrid',now(),now()),(1149,'Magdeburg','Magdeburg',now(),now()),(1150,'Magdeburgk','Magdeburg',now(),now()),(1151,'Magyarország','Magyarország',now(),now()),(1152,'Maine','Maine',now(),now()),(1153,'Mainz','Mainz',now(),now()),(1154,'Palma de Mallorca','Majorca',now(),now()),(1155,'Málaga','Málaga',now(),now()),(1156,'Maldeghem','Maldegem',now(),now()),(1157,'Malmö','Malmö',now(),now()),(1158,'Manchester','Manchester',now(),now()),(1159,'Mannheim','Mannheim',now(),now()),(1160,'Mantova','Mantua',now(),now()),(1161,'Marbach','Marbach',now(),now()),(1162,'Marburg','Marburg',now(),now()),(1163,'Marburg a. d. L.','Marburg',now(),now()),(1164,'Marcinelle','Marcinelle',now(),now()),(1165,'Marcinelle-Charleroi','Marcinelle',now(),now()),(1166,'Marcinelle; Charleroi; Paris','Marcinelle',now(),now()),(1167,'Maribor','Maribor',now(),now()),(1168,'Marke','Marke',now(),now()),(1169,'Marl','Marl',now(),now()),(1170,'Marseille','Marseille',now(),now()),(1171,'Marseille; Paris','Marseille',now(),now()),(1172,'Martin','Martin',now(),now()),(1173,'Turčiansky S. Martin','Martin',now(),now()),(1174,'Matosinhos','Matosinhos',now(),now()),(1175,'Mechelen','Mechelen',now(),now()),(1176,'Medan','Medan',now(),now()),(1177,'Medellín','Medellín',now(),now()),(1178,'Meerbeke-Ninove','Meerbeke',now(),now()),(1179,'Meiringen','Meiringen',now(),now()),(1180,'Meisenheim','Meisenheim',now(),now()),(1181,'Meissen','Meissen',now(),now()),(1182,'Melbourne','Melbourne',now(),now()),(1183,'Melbourne; London','Melbourne',now(),now()),(1184,'Melsungen','Melsungen',now(),now()),(1185,'Memmingen','Memmingen',now(),now()),(1186,'Memmingen; Augsburg','Memmingen',now(),now()),(1187,'Menden','Menden',now(),now()),(1188,'Meppel','Meppel',now(),now()),(1189,'Merksem (Anvers)','Merksem',now(),now()),(1190,'Merrick, N.Y.','Merrick',now(),now()),(1191,'Merseburg','Merseburg',now(),now()),(1192,'Mexico','Mexico City',now(),now()),(1193,'México City','Mexico City',now(),now()),(1194,'Méxiko City','Mexico City',now(),now()),(1195,'Michigan','Michigan',now(),now()),(1196,'Middelburg','Middelburg',now(),now()),(1197,'Milan','Milan',now(),now()),(1198,'Milano','Milan',now(),now()),(1199,'Milano, Vicenza','Milan',now(),now()),(1200,'Milano; Cremona','Milan',now(),now()),(1201,'Milwaukee','Milwaukee',now(),now()),(1202,'Minden','Minden',now(),now()),(1203,'Minden, Dresden','Minden',now(),now()),(1204,'Minneapolis','Minneapolis',now(),now()),(1205,'Minsk','Minsk',now(),now()),(1206,'Miskolc','Miskolc',now(),now()),(1207,'Modautal-Neunkirchen','Modautal',now(),now()),(1208,'Modena','Modena',now(),now()),(1209,'Modena; Roma','Modena',now(),now()),(1210,'Mödling, Wien','Mödling',now(),now()),(1211,'Molenhoek','Molenhoek',now(),now()),(1212,'Monaco','Monaco',now(),now()),(1213,'Monchaltorf','Mönchaltorf',now(),now()),(1214,'Mönchaltorf; Hamburg','Mönchaltorf',now(),now()),(1215,'Gladbach','Mönchengladbach',now(),now()),(1216,'M. Gladbach','Mönchengladbach',now(),now()),(1217,'Mönchen Gladbach','Mönchengladbach',now(),now()),(1218,'Mönchengladbach','Mönchengladbach',now(),now()),(1219,'Mons','Mons',now(),now()),(1220,'Montpellier','Montpellier',now(),now()),(1221,'Montreal','Montreal',now(),now()),(1222,'Montreuil-sous-Bois','Montreuil',now(),now()),(1223,'Montricher','Montricher',now(),now()),(1224,'Bühl-Moos','Moos',now(),now()),(1225,'Moos','Moos in Passeier',now(),now()),(1226,'Moravská Ostrava','Moravská Ostrava',now(),now()),(1227,'Carnières-Morlanwelz','Morlanwelz',now(),now()),(1228,'Moscow','Moscow',now(),now()),(1229,'Moskou','Moscow',now(),now()),(1230,'Moskow','Moscow',now(),now()),(1231,'Moskva','Moscow',now(),now()),(1232,'Moskwa','Moscow',now(),now()),(1233,'Most','Most',now(),now()),(1234,'Mostar','Mostar',now(),now()),(1235,'Mühlhausen','Mühlhausen',now(),now()),(1236,'Muiderberg','Muiderberg',now(),now()),(1237,'Mülheim a. d. R.','Mülheim',now(),now()),(1238,'Mulhouse','Mulhouse',now(),now()),(1239,'Mulhouse, Tournai','Mulhouse',now(),now()),(1240,'Muenchen','Munich',now(),now()),(1241,'München','Munich',now(),now()),(1242,'München-Feldafing','Munich',now(),now()),(1243,'München, Berlin','Munich',now(),now()),(1244,'München, Gütersloh','Munich',now(),now()),(1245,'München, Wien','Munich',now(),now()),(1246,'München, Wien, Basel; Klagenfurt','Munich',now(),now()),(1247,'München, Zürich','Munich',now(),now()),(1248,'München; Berlin','Munich',now(),now()),(1249,'München; Berlin; Zürich','Munich',now(),now()),(1250,'München; Düsseldorf','Munich',now(),now()),(1251,'München; Gütersloh; Wien','Munich',now(),now()),(1252,'München; Leipzig','Munich',now(),now()),(1253,'München; Luzern','Munich',now(),now()),(1254,'München; München/Zürich','Munich',now(),now()),(1255,'München; Stuttgart','Munich',now(),now()),(1256,'München; Wien','Munich',now(),now()),(1257,'München; Wien; Basel','Munich',now(),now()),(1258,'München; Wien; Zürich','Munich',now(),now()),(1259,'München; Zürich','Munich',now(),now()),(1260,'München/Paderborn','Munich',now(),now()),(1261,'München/Zürich','Munich',now(),now()),(1262,'Munich/Vienna','Munich',now(),now()),(1263,'Münsingen','Münsingen',now(),now()),(1264,'Münster','Münster',now(),now()),(1265,'Murcia','Murcia',now(),now()),(1266,'Murnau','Murnau',now(),now()),(1267,'Murnau; München; Innsbruck; Basel','Murnau',now(),now()),(1268,'Murska Sobota','Murska Sobota',now(),now()),(1269,'Murska Sobota, Novi Sad / Budapest','Murska Sobota',now(),now()),(1270,'Naarden','Naarden',now(),now()),(1271,'Náchod','Nachod',now(),now()),(1272,'Næstved','Næstved',now(),now()),(1273,'Nagasaki','Nagasaki',now(),now()),(1274,'Namur','Namur',now(),now()),(1275,'Namur-(Paris-Berne)','Namur',now(),now()),(1276,'Nanjing','Nanjing',now(),now()),(1277,'Nantes','Nantes',now(),now()),(1278,'Napoli','Napoli',now(),now()),(1279,'Napoli, Ljubljana','Napoli',now(),now()),(1280,'Naumburg','Naumburg',now(),now()),(1281,'Naumburg; Jena','Naumburg',now(),now()),(1282,'Neerlandia','Neerlandia',now(),now()),(1283,'Neheim-Hüsten','Neheim',now(),now()),(1284,'Nesøya','Nesøya',now(),now()),(1285,'Nettetal','Nettetal',now(),now()),(1286,'Neuchâtel-Paris','Neuchâtel',now(),now()),(1287,'Neudamm','Neudamm',now(),now()),(1288,'Neuenstadt','Neuenstadt',now(),now()),(1289,'Neuhausen-Stuttgart','Neuhausen',now(),now()),(1290,'Neu-Isenburg','Neu-Isenburg',now(),now()),(1291,'Neukirchen','Neukirchen',now(),now()),(1292,'Neukirchen-Vluyn','Neukirchen',now(),now()),(1293,'Neumünster','Neumünster',now(),now()),(1294,'Neundorf','Neundorf',now(),now()),(1295,'Neunkirchen','Neunkirchen',now(),now()),(1296,'Neuried','Neuried',now(),now()),(1297,'Neuss','Neuss',now(),now()),(1298,'Neuss; Köln','Neuss',now(),now()),(1299,'Neustadt','Neustadt',now(),now()),(1300,'Neustadt an der Weinstraße','Neustadt',now(),now()),(1301,'Neuwied','Neuwied',now(),now()),(1302,'Neuwied & Leipzig','Neuwied',now(),now()),(1303,'Neuwied; Leipzig','Neuwied',now(),now()),(1304,'New Brunswick','New Brunswick',now(),now()),(1305,'New Brunswick, N. J.','New Brunswick',now(),now()),(1306,'New Delhi','New Delhi',now(),now()),(1307,'New Hampshire','New Hampshire',now(),now()),(1308,'New Haven','New Haven',now(),now()),(1309,'New Haven; London','New Haven',now(),now()),(1310,'New Milford; London','New Milford',now(),now()),(1311,'New York','New York',now(),now()),(1312,'New York, Toronto, London','New York',now(),now()),(1313,'New York, Toronto, London, etc.','New York',now(),now()),(1314,'New York; Boston','New York',now(),now()),(1315,'New York; Granville Mansions','New York',now(),now()),(1316,'New York; Great Britain','New York',now(),now()),(1317,'New York; London','New York',now(),now()),(1318,'New York; Oxford','New York',now(),now()),(1319,'New York; San Diego','New York',now(),now()),(1320,'New York; San Diego; London','New York',now(),now()),(1321,'New York; Warde','New York',now(),now()),(1322,'New York; Woodstock','New York',now(),now()),(1323,'Newcastle-upon Tyne','Newcastle-upon Tyne',now(),now()),(1324,'Nieuwkoop','Nieuwkoop',now(),now()),(1325,'Nijkerk','Nijkerk',now(),now()),(1326,'Nijmegen','Nijmwegen',now(),now()),(1327,'Nijmegen, B. Gottmer/Brugge, Orion','Nijmwegen',now(),now()),(1328,'Nijmegen/Amsterdam','Nijmwegen',now(),now()),(1329,'Nimwegen','Nijmwegen',now(),now()),(1330,'Nijverdal','Nijverdal',now(),now()),(1331,'Nordhastedt','Nordhastedt',now(),now()),(1332,'Nordhorn','Nordhorn',now(),now()),(1333,'Nördlingen','Nördlingen',now(),now()),(1334,'Normal, IL','Normal',now(),now()),(1335,'Norman','Norman',now(),now()),(1336,'Norrköping','Norrköping',now(),now()),(1337,'Northampton','Northampton',now(),now()),(1338,'Nottingham','Nottingham',now(),now()),(1339,'Nova Gorica','Nova Gorica',now(),now()),(1340,'Novara','Novara',now(),now()),(1341,'Novi Ligure','Novi Ligure',now(),now()),(1342,'Novi Sad','Novi Sad',now(),now()),(1343,'Noviszád','Novi Sad',now(),now()),(1344,'Ujvidék','Novi Sad',now(),now()),(1345,'Novo mesto','Novo mesto',now(),now()),(1346,'Nový Bydžov','Nový Bydžov',now(),now()),(1347,'Nový Jičín','Novy Jicin',now(),now()),(1348,'[Nürnberg]','Nuremberg',now(),now()),(1349,'Nürnberg','Nuremberg',now(),now()),(1350,'Nürnberg; Frankfurt am Main','Nuremberg',now(),now()),(1351,'Nürnberg; Neustadt an der Aysch','Nuremberg',now(),now()),(1352,'Nürnberg; Sulzbach','Nuremberg',now(),now()),(1353,'Nyíregyháza','Nyíregyháza',now(),now()),(1354,'Nyköbing Själland','Nykøbing Sjælland',now(),now()),(1355,'Oakland','Oakland',now(),now()),(1356,'Oberhausen','Oberhausen',now(),now()),(1357,'Oberlin','Oberlin',now(),now()),(1358,'Oberursel (Taunus)','Oberursel',now(),now()),(1359,'Odense','Odense',now(),now()),(1360,'Offenbach','Offenbach',now(),now()),(1361,'Ohio','Ohio',now(),now()),(1362,'Ohrid','Ohrid',now(),now()),(1363,'Oława','Olawa',now(),now()),(1364,'Old Chatham, NY','Old Chatham',now(),now()),(1365,'Oldenburg','Oldenburg',now(),now()),(1366,'Oldenburg; Groningen ','Oldenburg',now(),now()),(1367,'Oldenburg; Hamburg','Oldenburg',now(),now()),(1368,'Oldenburg; Hamburg; München','Oldenburg',now(),now()),(1369,'Olmütz','Olomouc',now(),now()),(1370,'Olomouc','Olomouc',now(),now()),(1371,'Olsztyn; Elbląg','Olsztyn',now(),now()),(1372,'Olten','Olten',now(),now()),(1373,'Olten; Freiburg i. Br.','Olten',now(),now()),(1374,'Oostburg','Oostburg',now(),now()),(1375,'Oosterhesselen','Oosterhesselen',now(),now()),(1376,'Opava','Opava',now(),now()),(1377,'Opladen','Opladen',now(),now()),(1378,'Opole','Opole',now(),now()),(1379,'Oppenheim','Oppenheim',now(),now()),(1380,'Or Yehuda','Or Yehuda',now(),now()),(1381,'Oranienburg-Berlin','Oranienburg',now(),now()),(1382,'Örebro','Örebro',now(),now()),(1383,'Örnsköldsvik','Örnsköldsvik',now(),now()),(1384,'Ort','Ort',now(),now()),(1385,'Oschatz','Oschatz',now(),now()),(1386,'Osijek','Osijek',now(),now()),(1387,'Kristiania','Oslo',now(),now()),(1388,'Kristiania; Kjøbenhavn','Oslo',now(),now()),(1389,'Oslo','Oslo',now(),now()),(1390,'Osnabrück','Osnabrück',now(),now()),(1391,'Oostende','Ostende',now(),now()),(1392,'Ostende','Ostende',now(),now()),(1393,'Osterhever','Osterhever',now(),now()),(1394,'Ostfildern','Ostfildern',now(),now()),(1395,'Ostrava','Ostrava',now(),now()),(1396,'Oświęcim','Oswiecim',now(),now()),(1397,'Ottensheim an der Donau','Ottensheim',now(),now()),(1398,'Oude Wetering','Oude Wetering',now(),now()),(1399,'Oude-God','Oude-God',now(),now()),(1400,'Aldehaske','Oudehaske',now(),now()),(1401,'Oudenaarde','Oudenaarde',now(),now()),(1402,'Overijse','Overijse',now(),now()),(1403,'Owing Mills','Owing Mills',now(),now()),(1404,'Oxford','Oxford',now(),now()),(1405,'Oxford, London','Oxford',now(),now()),(1406,'Oxford, N.Y.','Oxford',now(),now()),(1407,'Paderborn','Paderborn',now(),now()),(1408,'Paderborn, Würzburg','Paderborn',now(),now()),(1409,'Paderborn; Würzburg','Paderborn',now(),now()),(1410,'Padova','Padua',now(),now()),(1411,'Paisley','Paisley',now(),now()),(1412,'Paju','Paju',now(),now()),(1413,'Palermo','Palermo',now(),now()),(1414,'Palermo; Roma','Palermo',now(),now()),(1415,'Pápa','Pápa',now(),now()),(1416,'Paramaribo','Paramaribo',now(),now()),(1417,'Parijs','Paris',now(),now()),(1418,'Paris','Paris',now(),now()),(1419,'Paris-Bruxelles; Bruxelles','Paris',now(),now()),(1420,'Paris-Louvain','Paris',now(),now()),(1421,'Paris-Marcinelle','Paris',now(),now()),(1422,'Paris-Tournai','Paris',now(),now()),(1423,'Paris, Bruxelles','Paris',now(),now()),(1424,'Paris, Bruxelles, Genève','Paris',now(),now()),(1425,'Paris, Gand','Paris',now(),now()),(1426,'Paris; Bruges','Paris',now(),now()),(1427,'Paris; Bruxelles','Paris',now(),now()),(1428,'Paris; Courtrai; Bruxelles','Paris',now(),now()),(1429,'Paris; Crés','Paris',now(),now()),(1430,'Paris; Leipzig','Paris',now(),now()),(1431,'Paris; Leipzig; Tournai','Paris',now(),now()),(1432,'Paris; Liège','Paris',now(),now()),(1433,'Paris/Bruges','Paris',now(),now()),(1434,'Paris/Bruxelles','Paris',now(),now()),(1435,'Paris/Gembloux','Paris',now(),now()),(1436,'Parma','Parma',now(),now()),(1437,'Parma, Milano','Parma',now(),now()),(1438,'Pas-de-Calais','Pas-de-Calais',now(),now()),(1439,'Pasian di Prato','Pasian di Prato',now(),now()),(1440,'Passau','Passau',now(),now()),(1441,'Marina di Patti','Patti',now(),now()),(1442,'Pavona','Pavona',now(),now()),(1443,'Pazin','Pazin',now(),now()),(1444,'Pécs','Pécs',now(),now()),(1445,'Pelpin','Pelpin',now(),now()),(1446,'Perugia','Perugia',now(),now()),(1447,'Petrovec','Petrovec',now(),now()),(1448,'Pfaffenhoven','Pfaffenhoven',now(),now()),(1449,'Pforzheim','Pforzheim',now(),now()),(1450,'Philadelphia','Philadelphia',now(),now()),(1451,'Philadelphia; New York','Philadelphia',now(),now()),(1452,'Plzen','Pilsen',now(),now()),(1453,'Pinneberg','Pinneberg',now(),now()),(1454,'Pirmasens','Pirmasens',now(),now()),(1455,'Pistoia','Pistoia',now(),now()),(1456,'Plauen i.V.','Plauen',now(),now()),(1457,'Pleven','Pleven',now(),now()),(1458,'Plovdiv','Plovdiv',now(),now()),(1459,'Titograd','Podgorica',now(),now()),(1460,'Pointe-à-Pitre','Pointe-a-Pitre',now(),now()),(1461,'Pomona, Calif.','Pomona',now(),now()),(1462,'Poperinghe','Poperinge',now(),now()),(1463,'Port Elizabeth','Port Elizabeth',now(),now()),(1464,'Port Townsend, W.A.','Port Townsend',now(),now()),(1465,'Portland','Portland',now(),now()),(1466,'Porto','Porto',now(),now()),(1467,'Portree','Portree',now(),now()),(1468,'Pörtschach','Pörtschach',now(),now()),(1469,'Porvoo','Porvoo',now(),now()),(1470,'Porvoo, Helsinki, Juva','Porvoo',now(),now()),(1471,'Porvoo; Helsinki','Porvoo',now(),now()),(1472,'Poeszneck','Pößneck',now(),now()),(1473,'Pößneck','Pößneck',now(),now()),(1474,'Pößneck i. Thür.','Pößneck',now(),now()),(1475,'Potsdam','Potsdam',now(),now()),(1476,'Potsdam jetzt: Bad Godesberg','Potsdam',now(),now()),(1477,'Posen','Poznań',now(),now()),(1478,'Poznan','Poznań',now(),now()),(1479,'Poznań; Warszawa','Poznań',now(),now()),(1480,'Poznań; Warszawa; Wilno; Lublin','Poznań',now(),now()),(1481,'Městské divadlo Vinohrady, Praha','Prague',now(),now()),(1482,'Praag','Prague',now(),now()),(1483,'Prag','Prague',now(),now()),(1484,'Prag; Wien; Zürich','Prague',now(),now()),(1485,'Prága','Prague',now(),now()),(1486,'Prague','Prague',now(),now()),(1487,'Praha','Prague',now(),now()),(1488,'Praha / Bánska Bystrica','Prague',now(),now()),(1489,'Praha 4','Prague',now(),now()),(1490,'Praha VII','Prague',now(),now()),(1491,'Praha-Karlín','Prague',now(),now()),(1492,'Praha-Olšany','Prague',now(),now()),(1493,'Praha; Brno','Prague',now(),now()),(1494,'Praha: Melantrich ','Prague',now(),now()),(1495,'Preddvor','Preddvor',now(),now()),(1496,'Přerov','Prerov',now(),now()),(1497,'Přerov/Praha','Prerov',now(),now()),(1498,'Prešov','Prešov',now(),now()),(1499,'Preston','Preston',now(),now()),(1500,'Pretoria','Pretoria',now(),now()),(1501,'Príbram','Príbram',now(),now()),(1502,'Prien','Prien',now(),now()),(1503,'Princeton','Princeton',now(),now()),(1504,'Princeton; Oxford','Princeton',now(),now()),(1505,'Pristhinë','Prishtina',now(),now()),(1506,'Priština','Prishtina',now(),now()),(1507,'Prostějov','Prostejov',now(),now()),(1508,'Puna','Puna',now(),now()),(1509,'Purmerend','Purmerend',now(),now()),(1510,'Québec','Quebec',now(),now()),(1511,'Quedlinburg; Halberstadt','Quedlinburg',now(),now()),(1512,'Rabat','Rabat',now(),now()),(1513,'Racibórz','Raciborz',now(),now()),(1514,'Radovljica','Radovljica',now(),now()),(1515,'Rákosszentmihály','Rákosszentmihály',now(),now()),(1516,'Ramallah','Ramallah',now(),now()),(1517,'Ranchi','Ranchi',now(),now()),(1518,'Randers','Randers',now(),now()),(1519,'Rasquera','Rasquera',now(),now()),(1520,'Rastatt','Rastatt',now(),now()),(1521,'Rastatt; Baden','Rastatt',now(),now()),(1522,'Ratingen','Ratingen',now(),now()),(1523,'Ravensburg','Ravensburg',now(),now()),(1524,'Reading, Mass.','Reading',now(),now()),(1525,'Recklinghausen','Recklinghausen',now(),now()),(1526,'Regensburg','Regensburg',now(),now()),(1527,'Regensburg; Rom','Regensburg',now(),now()),(1528,'Reggio Emilia','Reggio Emilia',now(),now()),(1529,'Reicheneck','Reicheneck',now(),now()),(1530,'Reinbek','Reinbek',now(),now()),(1531,'Reinbek bei Hamburg','Reinbek',now(),now()),(1532,'Rekkem','Rekkem',now(),now()),(1533,'Rennes','Rennes',now(),now()),(1534,'Reutlingen','Reutlingen',now(),now()),(1535,'Reval','Reval',now(),now()),(1536,'Reykjavik','Reykjavik',now(),now()),(1537,'Rheda-Wiedenbrück','Rheda-Wiedenbrück',now(),now()),(1538,'Rheda-Wiedenbrück [u.a.]; Wien','Rheda-Wiedenbrück',now(),now()),(1539,'Rheda-Wiedenbrück u.a.; Wien','Rheda-Wiedenbrück',now(),now()),(1540,'Rheda-Wiedenbrück, Gütersloh','Rheda-Wiedenbrück',now(),now()),(1541,'Rheda-Wiedenbrück; Gütersloh; Wien','Rheda-Wiedenbrück',now(),now()),(1542,'Rheda-Wiedenbrück; Wien','Rheda-Wiedenbrück',now(),now()),(1543,'Rheda-Wiedenbrück; Wien, u.a.','Rheda-Wiedenbrück',now(),now()),(1544,'Rheinbreitbach','Rheinbreitbach',now(),now()),(1545,'Rheinhausen','Rheinhausen',now(),now()),(1546,'Rieden','Rieden',now(),now()),(1547,'Rīgā','Riga',now(),now()),(1548,'Rijeka','Rijeka',now(),now()),(1549,'Rijswijk','Rijswijk',now(),now()),(1550,'Rijswijk, Antwerpen','Rijswijk',now(),now()),(1551,'Rimbach','Rimbach',now(),now()),(1552,'Rinteln','Rinteln',now(),now()),(1553,'Riocuarto','Rio cuarto',now(),now()),(1554,'Rio de Janeiro','Rio de Janeiro',now(),now()),(1555,'Rio de Janeiro; São Paulo','Rio de Janeiro',now(),now()),(1556,'Risskov','Risskov',now(),now()),(1557,'Riverside','Riverside',now(),now()),(1558,'Rochester','Rochester',now(),now()),(1559,'Rochester, Mich.','Rochester',now(),now()),(1560,'Rodez','Rodez',now(),now()),(1561,'Roelofavrendsveen','Roelofavrendsveen',now(),now()),(1562,'Roeselare','Roeselare',now(),now()),(1563,'Roulers','Roeselare',now(),now()),(1564,'Remagen-Rolandseck','Rolandseck',now(),now()),(1565,'Rom','Rome',now(),now()),(1566,'Roma','Rome',now(),now()),(1567,'Rome','Rome',now(),now()),(1568,'Renaix','Ronse',now(),now()),(1569,'Roosendaal','Roosendaal',now(),now()),(1570,'Rorschach','Rorschach',now(),now()),(1571,'Rosemont','Rosemont',now(),now()),(1572,'Rosenheim','Rosenheim',now(),now()),(1573,'Rostock','Rostock',now(),now()),(1574,'Rothenburg','Rothenburg',now(),now()),(1575,'Rothenburg o. d. Tauber','Rothenburg',now(),now()),(1576,'Rothenfels','Rothenfels',now(),now()),(1577,'Rotterdam','Rotterdam',now(),now()),(1578,'Rotterdam, Amsterdam','Rotterdam',now(),now()),(1579,'Rotterdam; Amsterdam','Rotterdam',now(),now()),(1580,'Rotterdam; Paris','Rotterdam',now(),now()),(1581,'Rubí (Barcelona)','Rubí ',now(),now()),(1582,'Rudolstadt','Rudolstadt',now(),now()),(1583,'Ruhrgebiet und Frankfurt','Ruhrgebiet',now(),now()),(1584,'Rüschlikon; Zürich; Stuttgart; Wien','Rüschlikon',now(),now()),(1585,'Ruse','Ruse',now(),now()),(1586,'Rüsselsheim','Rüsselsheim',now(),now()),(1587,'Rzeszów','Rzeszow',now(),now()),(1588,'Saarbrücken','Saarbrücken',now(),now()),(1589,'Saarlautern','Saarlautern',now(),now()),(1590,'Saarlouis','Saarlouis',now(),now()),(1591,'Sag Harbor','Sag Harbor',now(),now()),(1592,'Saint Paul; Minnesota','Saint Paul',now(),now()),(1593,'St.Pölten, Wien, Linz','Saint Pölten',now(),now()),(1594,'Saint-Lambert','Saint-Lambert',now(),now()),(1595,'Saint-Nazaire','Saint-Nazaire',now(),now()),(1596,'Salamanca','Salamanca',now(),now()),(1597,'Salermo, Roma','Salerno',now(),now()),(1598,'Salgótarján','Salgótarján',now(),now()),(1599,'Salzburg','Salzburg',now(),now()),(1600,'Salzburg; Leipzig','Salzburg',now(),now()),(1601,'Salzburg; Wien','Salzburg',now(),now()),(1602,'Sambor','Sambir',now(),now()),(1603,'San Bernardino, CA','San Bernardino',now(),now()),(1604,'Canzian d\'Isonzo','San Canzian d\'Isonzo',now(),now()),(1605,'San Diego','San Diego',now(),now()),(1606,'San Diego; New York, London','San Diego',now(),now()),(1607,'San Francisco','San Francisco',now(),now()),(1608,'San José','San Jose',now(),now()),(1609,'San Sebastian','San Sebastian',now(),now()),(1610,'Donostia','San Sebastián',now(),now()),(1611,'Sangerhausen, Wolfenbüttel','Sangerhausen',now(),now()),(1612,'Sant Cugat del Vallès','Sant Cugat del Vallès',now(),now()),(1613,'Santa Fe','Santa Fe',now(),now()),(1614,'Santafé de Bogotá','Santa Fe',now(),now()),(1615,'Santiago de Chile','Santiago',now(),now()),(1616,'Sant\'Oreste','Sant\'Oreste',now(),now()),(1617,'Santpoort','Santpoort',now(),now()),(1618,'Santpoort; Amsterdam','Santpoort',now(),now()),(1619,'Sao Leopoldo','São Leopoldo',now(),now()),(1620,'São Paolo','São Paolo',now(),now()),(1621,'Sao Paulo','São Paolo',now(),now()),(1622,'Sarajevo','Sarajevo',now(),now()),(1623,'Szatmár','Satu Mare',now(),now()),(1624,'Szatmár-Németi','Satu Mare',now(),now()),(1625,'Szatmárnémeti','Satu Mare',now(),now()),(1626,'Sawtry, Cambridgeshire','Sawtry',now(),now()),(1627,'Schaffhausen','Schaffhausen',now(),now()),(1628,'Scharnstein; Simbach','Scharnstein',now(),now()),(1629,'Scheveningen','Scheveningen',now(),now()),(1630,'Scheyern','Scheyern',now(),now()),(1631,'Schleswig','Schleswig',now(),now()),(1632,'Schönberg/Mecklenburg','Schönberg',now(),now()),(1633,'Schoorl','Schoorl',now(),now()),(1634,'Schwäbisch Hall','Schwäbisch Hall',now(),now()),(1635,'Schwerin','Schwerin',now(),now()),(1636,'Schwerin, Rostock, Hinstorff','Schwerin',now(),now()),(1637,'Seattle','Seattle',now(),now()),(1638,'Szinérváralja','Seini',now(),now()),(1639,'Sekowa','Sekowa',now(),now()),(1640,'Senica','Senica',now(),now()),(1641,'Seoul','Seoul',now(),now()),(1642,'Sewanee','Sewanee',now(),now()),(1643,'\'s Graveland','\'s-Graveland',now(),now()),(1644,'\'s-Graveland','\'s-Graveland',now(),now()),(1645,'\'s Gravendeel','\'s-Gravendeel',now(),now()),(1646,'Gravenzande','\'s-Gravenzande',now(),now()),(1647,'Shanghai','Shanghai',now(),now()),(1648,'\'s-Hertogenbosch','\'s-Hertogenbosch',now(),now()),(1649,'Den Bosch','\'s-Hertogenbosch',now(),now()),(1650,'Sibiu','Sibiu',now(),now()),(1651,'Siegburg','Siegburg',now(),now()),(1652,'Siegen','Siegen',now(),now()),(1653,'Siegen; Wiesbaden','Siegen',now(),now()),(1654,'Siena','Siena',now(),now()),(1655,'Sigtuna','Sigtuna',now(),now()),(1656,'Singapore','Singapore',now(),now()),(1657,'Singapore; Oxford','Singapore',now(),now()),(1658,'Singen, Wiesbaden','Singen',now(),now()),(1659,'Singen; Wiesbaden','Singen',now(),now()),(1660,'Sint- Truiden/ Maastricht','Sint- Truiden',now(),now()),(1661,'Sint-Amands aan de Schelde','Sint-Amands',now(),now()),(1662,'Sint-Martens-Latem','Sint-Martens-Latem',now(),now()),(1663,'Sint Niklaas','Sint-Niklaas',now(),now()),(1664,'Sint- Niklaas','Sint-Niklaas',now(),now()),(1665,'Sint-Niklaas','Sint-Niklaas',now(),now()),(1666,'Sint-Stevens-Woluwe','Sint-Stevens-Woluwe',now(),now()),(1667,'Sint-Stevens-Woluwe/Amsterdam','Sint-Stevens-Woluwe',now(),now()),(1668,'Skopje','Skopje',now(),now()),(1669,'Slaný','Slaný',now(),now()),(1670,'Slavonski Brod','Slavonski Brod',now(),now()),(1671,'Sliedrecht','Sliedrecht',now(),now()),(1672,'Smedervo','Smedervo',now(),now()),(1673,'Sneek','Sneek',now(),now()),(1674,'Soesterberg','Soesterberg',now(),now()),(1675,'Sofia','Sofia',now(),now()),(1676,'Sofija','Sofia',now(),now()),(1677,'Solingen','Solingen',now(),now()),(1678,'Solna','Solna',now(),now()),(1679,'Solothurn','Solothurn',now(),now()),(1680,'Sorø','Sorø',now(),now()),(1681,'Sosnowiec','Sosnowiec',now(),now()),(1682,'South Hampton','South Hampton',now(),now()),(1683,'Split','Split',now(),now()),(1684,'Spouwen','Spouwen',now(),now()),(1685,'Sremski Karlovci','Sremski Karlovci',now(),now()),(1686,'St. Albans','St. Albans',now(),now()),(1687,'St. Augustin','St. Augustin',now(),now()),(1688,'St. Catherines, Ont.','St. Catherines',now(),now()),(1689,'St. Catherines, Ont.; Edinburgh','St. Catherines',now(),now()),(1690,'St. Gallen','St. Gallen',now(),now()),(1691,'St. Gallen; Stuttgart','St. Gallen',now(),now()),(1692,'St. Leonards, N.S.W.','St. Leonards',now(),now()),(1693,'St. Paul, Minnesota','St. Paul',now(),now()),(1694,'St. Winoxbergen','St. Winoxbergen',now(),now()),(1695,'St.-Jans-Molenbeek','St.-Jans-Molenbeek',now(),now()),(1696,'St.-Kwintense-Lennik','St.-Kwintense-Lennik',now(),now()),(1697,'Petersburg','St.Petersburg',now(),now()),(1698,'Sankt-Petersburg','St.Petersburg',now(),now()),(1699,'Sint Petersburg','St.Petersburg',now(),now()),(1700,'St. Peter','St.Petersburg',now(),now()),(1701,'St. Petersburg','St.Petersburg',now(),now()),(1702,'St. Petersburg; Nijmegen','St.Petersburg',now(),now()),(1703,'St.Petersburg','St.Petersburg',now(),now()),(1704,'Stade','Stade',now(),now()),(1705,'Stäfa','Stäfa',now(),now()),(1706,'Stará Říše na Moravě','Stará Říše',now(),now()),(1707,'Stari Banovci','Stari Banovci',now(),now()),(1708,'Starnberg','Starnberg',now(),now()),(1709,'Starnberg; München','Starnberg',now(),now()),(1710,'Stavanger','Stavanger',now(),now()),(1711,'Steenvoorde','Steenvoorde',now(),now()),(1712,'Steglitz-Berlin','Steglitz',now(),now()),(1713,'Stellenbosch','Stellenbosch',now(),now()),(1714,'Steyl; Kaldenkirchen','Steyl',now(),now()),(1715,'Stierstadt im Taunus','Stierstadt',now(),now()),(1716,'Stockholm','Stockholm',now(),now()),(1717,'Stockhom','Stockholm',now(),now()),(1718,'Straelen','Straelen',now(),now()),(1719,'Strasbourg','Strasbourg',now(),now()),(1720,'Straßburg','Strasbourg',now(),now()),(1721,'Struga','Struga',now(),now()),(1722,'Stuttgart','Stuttgart',now(),now()),(1723,'Stuttgart i.e. Fellbach','Stuttgart',now(),now()),(1724,'Stuttgart u.a.','Stuttgart',now(),now()),(1725,'Stuttgart, Bad Cannstatt','Stuttgart',now(),now()),(1726,'Stuttgart; Berlin','Stuttgart',now(),now()),(1727,'Stuttgart; Berlin; Leipzig','Stuttgart',now(),now()),(1728,'Stuttgart; Bern','Stuttgart',now(),now()),(1729,'Stuttgart; Dresden','Stuttgart',now(),now()),(1730,'Stuttgart; Hamburg; München','Stuttgart',now(),now()),(1731,'Stuttgart; Leipzig','Stuttgart',now(),now()),(1732,'Stuttgart; Leipzig; Berlin','Stuttgart',now(),now()),(1733,'Stuttgart; Leipzig; Berlin; Wien','Stuttgart',now(),now()),(1734,'Stuttgart; München','Stuttgart',now(),now()),(1735,'Stuttgart; Olten; Salzburg','Stuttgart',now(),now()),(1736,'Stuttgart; Wien','Stuttgart',now(),now()),(1737,'Stuttgart; Zürich','Stuttgart',now(),now()),(1738,'Stuttgart; Zürich; Salzburg','Stuttgart',now(),now()),(1739,'Stuttgart/Wien','Stuttgart',now(),now()),(1740,'Subotica','Subotica',now(),now()),(1741,'Suceava','Suceava',now(),now()),(1742,'Sulzbach','Sulzbach',now(),now()),(1743,'Šumperk','Šumperk',now(),now()),(1744,'Sundbyberg','Sundbyberg',now(),now()),(1745,'Sundbyberg; Stockholm','Sundbyberg',now(),now()),(1746,'Surrey','Surrey',now(),now()),(1747,'Sv. Martin','Sveti Martin',now(),now()),(1748,'Swaftham','Swaffham',now(),now()),(1749,'Swindon','Swindon',now(),now()),(1750,'Schweiz','Switzerland',now(),now()),(1751,'Switzerland','Switzerland',now(),now()),(1752,'Sydney','Sydney',now(),now()),(1753,'Sydney / Melbourne','Sydney',now(),now()),(1754,'Sydney, Washington, Oxford','Sydney',now(),now()),(1755,'Stettin','Szczecin',now(),now()),(1756,'Szeged','Szeged',now(),now()),(1757,'Szekszárd','Szekszárd',now(),now()),(1758,'Szolnok','Szolnok',now(),now()),(1759,'\'t Harde','\'t Harde',now(),now()),(1760,'Tábor','Tabor',now(),now()),(1761,'Taichung (Taiwan)','Taichung',now(),now()),(1762,'Taipei','Taipei',now(),now()),(1763,'Tallinn','Tallinn',now(),now()),(1764,'Tampere','Tampere',now(),now()),(1765,'Tarazona','Tarazona',now(),now()),(1766,'Tarset','Tarset',now(),now()),(1767,'Tartu','Tartu',now(),now()),(1768,'Taschenbuch','Taschenbuch',now(),now()),(1769,'Tasov na Moravě','Tasov',now(),now()),(1770,'Tasov na Moravě / Praha','Tasov',now(),now()),(1771,'Tatabánya','Tatabánya',now(),now()),(1772,'Taunusstein','Taunusstein',now(),now()),(1773,'Tbilisi','Tbilisi',now(),now()),(1774,'Teheran','Tehran',now(),now()),(1775,'Tel Aviv','Tel Aviv',now(),now()),(1776,'Teplitz-Schönau','Teplice',now(),now()),(1777,'Teralfene','Teralfene',now(),now()),(1778,'Teufen','Teufen',now(),now()),(1779,'Texing','Texingtal',now(),now()),(1780,'\'s Gravenhage','The Hague',now(),now()),(1781,'\'s Gravenhage, London','The Hague',now(),now()),(1782,'\'s-Gravenhage','The Hague',now(),now()),(1783,'\'s-Gravenhage, Brussel','The Hague',now(),now()),(1784,'BZZTOH, \'s Gravenhage','The Hague',now(),now()),(1785,'Den Haag','The Hague',now(),now()),(1786,'Den Haag, Amsterdam','The Hague',now(),now()),(1787,'Den Haag, Rotterdam','The Hague',now(),now()),(1788,'Den Haag; Bandung','The Hague',now(),now()),(1789,'Den Haag; Brussel; Amsterdam','The Hague',now(),now()),(1790,'Den Haag; Liège','The Hague',now(),now()),(1791,'Den Haag; Londen/Copenh.','The Hague',now(),now()),(1792,'Den Haag/Rotterdam','The Hague',now(),now()),(1793,'Hague','The Hague',now(),now()),(1794,'La Haye','The Hague',now(),now()),(1795,'The Hague','The Hague',now(),now()),(1796,'Thorn','Thorn',now(),now()),(1797,'Tiegem','Tiegem',now(),now()),(1798,'Tiel','Tiel',now(),now()),(1799,'Thielt','Tielt',now(),now()),(1800,'Tielt','Tielt',now(),now()),(1801,'Tielt, Amsterdam','Tielt',now(),now()),(1802,'Tielt; Amsterdam','Tielt',now(),now()),(1803,'Tielt/Den Haag','Tielt',now(),now()),(1804,'Tilburg','Tilburg',now(),now()),(1805,'Tirana','Tirana',now(),now()),(1806,'Tirana, Utrecht','Tirana',now(),now()),(1807,'Todmorden','Todmorden',now(),now()),(1808,'Tokyo','Tokyo',now(),now()),(1809,'Tolmin','Tolmin',now(),now()),(1810,'Tongeren','Tongeren',now(),now()),(1811,'Tongres','Tongeren',now(),now()),(1812,'Tongres; Paris','Tongeren',now(),now()),(1813,'Torhout','Torhout',now(),now()),(1814,'Toronto','Toronto',now(),now()),(1815,'Torre Pellice','Torre Pellice',now(),now()),(1816,'Toulouse','Toulouse',now(),now()),(1817,'Doornik','Tournai',now(),now()),(1818,'Tournai','Tournay',now(),now()),(1819,'Tournai/Paris','Tournay',now(),now()),(1820,'tournay','Tournay',now(),now()),(1821,'Třebíč','Trebíc',now(),now()),(1822,'Třeboň','Trebon',now(),now()),(1823,'Tricht','Tricht',now(),now()),(1824,'Trier','Trier',now(),now()),(1825,'Triëst','Trieste',now(),now()),(1826,'Trieste','Trieste',now(),now()),(1827,'Trst','Trieste',now(),now()),(1828,'Trivandrum','Trivandrum',now(),now()),(1829,'Trnava','Trnava',now(),now()),(1830,'Trouville-sur-mer','Trouville',now(),now()),(1831,'Tržič','Tric',now(),now()),(1832,'Tübingen','Tübingen',now(),now()),(1833,'Tübingen, Basel','Tübingen',now(),now()),(1834,'Tübingen; Basel','Tübingen',now(),now()),(1835,'Tuggen','Tuggen',now(),now()),(1836,'Torino','Turin',now(),now()),(1837,'Turku','Turku',now(),now()),(1838,'Turnhout','Turnhout',now(),now()),(1839,'Turnov','Turnov',now(),now()),(1840,'Udine','Udine',now(),now()),(1841,'Uherské Hradiště','Uherské Hradiště',now(),now()),(1842,'Uherské Hradište','Uherské Hradiště',now(),now()),(1843,'Uhldingen; Seewis','Uhldingen',now(),now()),(1844,'Uhldingen/Bodensee','Uhldingen',now(),now()),(1845,'Ulm','Ulm',now(),now()),(1846,'Ulm/Neu-Ulm','Ulm',now(),now()),(1847,'Unhošť u Prahy','Unhošť',now(),now()),(1848,'Uppsala','Uppsala',now(),now()),(1849,'Upsala','Uppsala',now(),now()),(1850,'Urtenen (Schweiz)','Urtenen',now(),now()),(1851,'Uruguay','Uruguay',now(),now()),(1852,'USA','USA',now(),now()),(1853,'Utrecht','Utrecht',now(),now()),(1854,'Utrecht (unveroeffentlichte Diplomarbeit)','Utrecht',now(),now()),(1855,'Utrecht / Antwerpen / Wijnegen','Utrecht',now(),now()),(1856,'Utrecht / Olomouc','Utrecht',now(),now()),(1857,'Utrecht / Wrocław / Warszawa','Utrecht',now(),now()),(1858,'Utrecht, Amsterdam','Utrecht',now(),now()),(1859,'Utrecht, Antwerpen','Utrecht',now(),now()),(1860,'Utrecht, Haarlem','Utrecht',now(),now()),(1861,'Utrecht, Wijnegen','Utrecht',now(),now()),(1862,'Utrecht/Aartselaar','Utrecht',now(),now()),(1863,'Utrecht/Antwerpen','Utrecht',now(),now()),(1864,'Uzes','Uzès',now(),now()),(1865,'Valby','Valby',now(),now()),(1866,'Valencia','Valencia',now(),now()),(1867,'Varna','Varna',now(),now()),(1868,'Warnsdorf','Varnsdorf',now(),now()),(1869,'Västerås','Västerås',now(),now()),(1870,'Veendam','Veendam',now(),now()),(1871,'Veenendaal','Veenendaal',now(),now()),(1872,'Vejle','Vejle',now(),now()),(1873,'Veldwezelt','Veldwezelt',now(),now()),(1874,'Veliko Tarnovo','Veliko Tarnovo',now(),now()),(1875,'Velké Meziříčí','Velké Mezirící',now(),now()),(1876,'Velp','Velp',now(),now()),(1877,'Venezia','Venice',now(),now()),(1878,'Venlo','Venlo',now(),now()),(1879,'Verden (Aller)','Verden',now(),now()),(1880,'Verviers','Verviers',now(),now()),(1881,'Verviers; Paris','Verviers',now(),now()),(1882,'Veurne','Veurne',now(),now()),(1883,'Vevey; Paris','Vevey',now(),now()),(1884,'Vianen','Vianen',now(),now()),(1885,'Vicenza','Vicenza',now(),now()),(1886,'Victoria','Victoria',now(),now()),(1887,'(Wien)','Vienna',now(),now()),(1888,'Diplomarbeit Wien','Vienna',now(),now()),(1889,'Wien','Vienna',now(),now()),(1890,'Wien (Unveroeffentlichte Diplomararbeit)','Vienna',now(),now()),(1891,'Wien / Veszprém','Vienna',now(),now()),(1892,'Wien u.a.','Vienna',now(),now()),(1893,'Wien, München','Vienna',now(),now()),(1894,'Wien; Amsterdam; Leipzig','Vienna',now(),now()),(1895,'Wien; Berlin; Stuttgart','Vienna',now(),now()),(1896,'Wien; Gütersloh','Vienna',now(),now()),(1897,'Wien; Hamburg','Vienna',now(),now()),(1898,'Wien; Heidelberg','Vienna',now(),now()),(1899,'Wien; Innsbruck; Wiesbaden','Vienna',now(),now()),(1900,'Wien; Leipzig','Vienna',now(),now()),(1901,'Wien; München','Vienna',now(),now()),(1902,'Wien; München; Zürich','Vienna',now(),now()),(1903,'Wien; St. Pölten','Vienna',now(),now()),(1904,'Wien; Stuttgart','Vienna',now(),now()),(1905,'Wien/Köln/Weimar','Vienna',now(),now()),(1906,'Wien/Muenchen','Vienna',now(),now()),(1907,'Viersen','Viersen',now(),now()),(1908,'Vigo','Vigo',now(),now()),(1909,'Villa San Secondo','Villa San Secondo',now(),now()),(1910,'Villneuve d’Ascq','Villneuve d’Ascq',now(),now()),(1911,'Vilnius','Vilnius',now(),now()),(1912,'Vilvoorde','Vilvoorde',now(),now()),(1913,'Vinton, Iowa','Vinton',now(),now()),(1914,'Virum','Virum',now(),now()),(1915,'Viterbo','Viterbo',now(),now()),(1916,'Vitoria','Vitoria',now(),now()),(1917,'Vlissingen','Vlissingen',now(),now()),(1918,'Wolowiec','Volovec',now(),now()),(1919,'Wolowiec','Volovec',now(),now()),(1920,'Voorschoten','Voorschoten',now(),now()),(1921,'Vorarlberg','Vorarlberg',now(),now()),(1922,'Vorselaar','Vorselaar',now(),now()),(1923,'Vršac','Vršac',now(),now()),(1924,'Wachtebeke','Wachtebeke',now(),now()),(1925,'Wädenswil','Wädenswil',now(),now()),(1926,'Wageningen','Wageningen',now(),now()),(1927,'Waibstedt','Waibstedt',now(),now()),(1928,'Wald','Wald',now(),now()),(1929,'Walmeera','Walmeera',now(),now()),(1930,'Wanswerd','Wanswert',now(),now()),(1931,'Warendorf','Warendorf',now(),now()),(1932,'Warlingham','Warlingham',now(),now()),(1933,'Warmbronn (Leonberg)','Warmbronn (Leonberg)',now(),now()),(1934,'Warsaw','Warsaw',now(),now()),(1935,'Warschau','Warsaw',now(),now()),(1936,'Warszawa','Warsaw',now(),now()),(1937,'Warszawa (Kraków)','Warsaw',now(),now()),(1938,'Warszawa; Kraków','Warsaw',now(),now()),(1939,'Warsze','Warsaw',now(),now()),(1940,'Washington','Washington',now(),now()),(1941,'Wassenaar','Wassenaar',now(),now()),(1942,'Watford','Watford',now(),now()),(1943,'Wěclaw-Budyšín','Bautzen',now(),now()),(1944,'Wedel','Wedel',now(),now()),(1945,'Weesp','Weesp',now(),now()),(1946,'Weesp, Amsterdam','Weesp',now(),now()),(1947,'Weiler im Allgäu','Weiler-Simmerberg',now(),now()),(1948,'Weilerswist','Weilerswist',now(),now()),(1949,'Weimar','Weimar',now(),now()),(1950,'Weinheim','Weinheim',now(),now()),(1951,'Weinheim, Basel','Weinheim',now(),now()),(1952,'Weinheim; Basel','Weinheim',now(),now()),(1953,'Wellington','Wellington',now(),now()),(1954,'Weltevreden','Weltevreden',now(),now()),(1955,'Wernigerode','Wernigerode',now(),now()),(1956,'Wesel','Wesel',now(),now()),(1957,'Westervoort','Westervoort',now(),now()),(1958,'Westminster','Westminster',now(),now()),(1959,'Westport','Westport',now(),now()),(1960,'Westport, Conn.','Westport',now(),now()),(1961,'Wetzikon','Wetzikon',now(),now()),(1962,'Wetzlar','Wetzlar',now(),now()),(1963,'Wevelgem','Wevelgem',now(),now()),(1964,'Wiesbaden','Wiesbaden',now(),now()),(1965,'Wiesbaden; München','Wiesbaden',now(),now()),(1966,'Wiesbaden; Zürich','Wiesbaden',now(),now()),(1967,'Wijnegem','Wijnegem',now(),now()),(1968,'Wilhelmshaven','Wilhelmshaven',now(),now()),(1969,'Wilmete, Ill.','Wilmete',now(),now()),(1970,'Wilrijk','Wilrijk',now(),now()),(1971,'Wilrijk-Antwerpen','Wilrijk',now(),now()),(1972,'Windsor, Ont.','Windsor',now(),now()),(1973,'Main St. Winnipeg','Winnipeg',now(),now()),(1974,'Winschoten','Winschoten',now(),now()),(1975,'Winterthur','Winterthur',now(),now()),(1976,'Witten','Witten',now(),now()),(1977,'Wittenberg','Wittenberg',now(),now()),(1978,'Wittingen','Wittingen',now(),now()),(1979,'Witzenhausen','Witzenhausen',now(),now()),(1980,'Wolfenbüttel','Wolfenbüttel',now(),now()),(1981,'Wolfenbüttel; Basel','Wolfenbüttel',now(),now()),(1982,'Wolfenbüttel; Berlin','Wolfenbüttel',now(),now()),(1983,'Wolfshagen','Wolfshagen',now(),now()),(1984,'Wolfshagen-Scharbeutz','Wolfshagen',now(),now()),(1985,'Wollerau/Schweiz','Wollerau',now(),now()),(1986,'Woodchester','Woodchester',now(),now()),(1987,'Woubrugge','Woubrugge',now(),now()),(1988,'Wrocław','Wroclaw',now(),now()),(1989,'Wroclaw/Dresden','Wroclaw',now(),now()),(1990,'Wroclaw','Wroclaw',now(),now()),(1991,'Wrocław / Warszawa / Kraków','Wroclaw',now(),now()),(1992,'Wrocław-Warszawa','Wroclaw',now(),now()),(1993,'Breslau','Wroclaw',now(),now()),(1994,'Breslau; Brieg','Wroclaw',now(),now()),(1995,'Breslau; Leipzig','Wroclaw',now(),now()),(1996,'Wuppertal','Wuppertal',now(),now()),(1997,'Wuppertal- Barmen','Wuppertal',now(),now()),(1998,'Wuppertal-Elberfeld','Wuppertal',now(),now()),(1999,'Wuppertal; Kassel','Wuppertal',now(),now()),(2000,'Wuppertal; Zürich','Wuppertal',now(),now()),(2001,'Würzburg','Würzburg',now(),now()),(2002,'Würzburg/Den Haag','Würzburg',now(),now()),(2005,'Jossa','Jossa',now(),now()),(2006,'Kaliski','Kalisz',now(),now()),(2007,'Kleinschönebeck','Schöneiche',now(),now()),(2008,'Pest','Budapest',now(),now()),(2009,'Pöîneck i. Thür.','Pößneck',now(),now()),(2010,'Straîburg','Strasbourg',now(),now()),(2011,'Worms','Worms',now(),now()),(2012,'Yellow Springs','Yellow Springs',now(),now()),(2013,'Erevan','Yerevan',now(),now()),(2014,'Jerevan','Yerevan',now(),now()),(2015,'Yerevan','Yerevan',now(),now()),(2016,'Yogjakarta','Yogyakarta',now(),now()),(2017,'Yogyakarta','Yogyakarta',now(),now()),(2018,'Ypsilanti, Mich.','Ypsilanti',now(),now()),(2019,'Zaandam','Zaandam',now(),now()),(2020,'Zaandam; Alkmaar','Zaandam',now(),now()),(2021,'Ząbki','Zabki',now(),now()),(2022,'Zadar','Zadar',now(),now()),(2023,'Agram (Zagreb)','Zagreb',now(),now()),(2024,'Zagreb','Zagreb',now(),now()),(2025,'Zagreb (Agram)','Zagreb',now(),now()),(2026,'Zakrzewo','Zakrzewo',now(),now()),(2027,'Zalaegerszeg','Zalaegerszeg',now(),now()),(2028,'Zaltbommel','Zaltbommel',now(),now()),(2029,'Zapresic','Zaprešić',now(),now()),(2030,'Zaragoza','Zaragoza',now(),now()),(2031,'Zeist','Zeist',now(),now()),(2032,'Zenica','Zenica',now(),now()),(2033,'Zeulenroda','Zeulenroda',now(),now()),(2034,'Zhejiang','Zhejiang',now(),now()),(2035,'Žilina','Žilina',now(),now()),(2036,'Zlín','Zlín',now(),now()),(2037,'Zolder','Zolder',now(),now()),(2038,'Zondervan','Zondervan',now(),now()),(2039,'Zug','Zug',now(),now()),(2040,'Zürich','Zurich',now(),now()),(2041,'Zürich-Leipzig-Wien','Zurich',now(),now()),(2042,'Zürich, München','Zurich',now(),now()),(2043,'Zürich; Basel','Zurich',now(),now()),(2044,'Zürich; Berlin; Wien; Leipzig','Zurich',now(),now()),(2045,'Zürich; Bruxelles','Zurich',now(),now()),(2046,'Zürich; Frauenfeld','Zurich',now(),now()),(2047,'Zürich; Hamburg','Zurich',now(),now()),(2048,'Zürich; Innsbruck','Zurich',now(),now()),(2049,'Zürich; Köln','Zurich',now(),now()),(2050,'Zürich; Köln; Lahr','Zurich',now(),now()),(2051,'Zürich; München','Zurich',now(),now()),(2052,'Zürich; New York','Zurich',now(),now()),(2053,'Zürich; Prag','Zurich',now(),now()),(2054,'Zürich; Stuttgart','Zurich',now(),now()),(2055,'Zutphen','Zutphen',now(),now()),(2056,'Zweibrücken','Zweibrücken',now(),now()),(2057,'Zwickau','Zwickau',now(),now()),(2058,'Zwolle','Zwolle',now(),now()),(2060,'Wrocław','Wrocław',now(),now()),(2061,'Wrocław','Wrocław',now(),now()),(2062,'Kleinschönebeck','Kleinschönebeck',now(),now()),(2063,'Pöîneck i. Thür.','Pößneck',now(),now())";
                $result = mysqli_query($connYarm, $sql);
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
            exit;
        }
    }

    /*private function fillTables($connDlbt,$connYarm){

        $roles=['actor','author','choreographer','composer','corporate_author','director','editor','illustrator','preface_postface','producer','translator','txtwriter'];
        foreach ($roles as $role) {
            $sql = "INSERT INTO roles(name) VALUES('" . $role . "')";
            if (mysqli_query($connYarm, $sql)) {
                //echo('Role ' . $role . 'inserted successfully. <br>');
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                exit;
            }
        }

        $genres=['Prose','Poetry','---','Humanities','Nature','Praktical life','Science'];
        foreach ($genres as $genre) {
            $sql = "INSERT INTO genres(name) VALUES('" . $genre . "')";
            if (mysqli_query($connYarm, $sql)) {
                //echo('Genre ' . $genre . 'inserted successfully. <br>');
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                exit;
            }
        }

        $identifier_types=['PID','PID-TEI XML','DOI','PI','AC','urn','Handle','nbn','ISBN','ISSN','url'];
        foreach ($identifier_types as $idtype) {
            $sql = "INSERT INTO identifier_types(name) VALUES('" . $idtype . "')";
            if (mysqli_query($connYarm, $sql)) {
                //echo('Identifier type ' . $idtype . 'inserted successfully. <br>');
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                exit;
            }
        }

        $statuses=['Checked','Imported','Denied'];
        foreach ($statuses as $status) {
            $sql = "INSERT INTO statuses(name) VALUES('" . $status . "')";
            if (mysqli_query($connYarm, $sql)) {
                //echo('Role ' . $role . ' inserted successfully. <br>');
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                exit;
            }
        }

        //Load languages
        $sql="select * from ref_languages order by language_id ";
        $result=mysqli_query($connDlbt, $sql);
        if ($result){
            if ($result->num_rows > 0) {
                foreach ($result as $language){
                    $sql = "INSERT INTO languages(name, code, enabled) VALUES('" . $language['language_name'] . "', '" . $language['lang_code']  . "', '" . $language['language_enabled']."')";
                    if (mysqli_query($connYarm, $sql)) {
                        //echo('Language ' . $language['language_name'] . ' inserted successfully. <br>');
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                    exit;
                    }
               }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
            exit;
        }

        $sql="select * from types order by type_name ";
        $result=mysqli_query($connDlbt, $sql);
        if ($result){
            if ($result->num_rows > 0) {
                foreach ($result as $type){
                    $sql = "INSERT INTO types(name, enabled) VALUES('" . $type['type_name'] . "', '" . $type['type_enabled']."')";
                    if (mysqli_query($connYarm, $sql)) {
                        //echo('Type ' . $type['type_name'] . ' inserted successfully. <br>');
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
                        exit;
                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . " < br>";
            exit;
        }

        $arrayLicenses=json_decode('[
    {
        "license_link" : "All rights reserved",
        "license_entry" : "All rights reserved"
    },
    {
        "license_link" : "http://creativecommons.org/licenses/by/2.0/at/",
        "license_entry" : "CC BY 2.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc/2.0/at/",
        "license_entry" : "CC BY-NC 2.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-nd/2.0/at/",
        "license_entry" : "CC BY-NC-ND 2.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-sa/2.0/at/",
        "license_entry" : "CC BY-NC-SA 2.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nd/2.0/at/",
        "license_entry" : "CC BY-ND 2.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-sa/2.0/at/",
        "license_entry" : "CC BY-SA 2.0 AT"
    },
            {
                "license_link" : "",
        "license_entry" : "GNU-License"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by/3.0/at/",
        "license_entry" : "CC BY 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc/3.0/at/",
        "license_entry" : "CC BY-NC 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-nd/3.0/at/",
        "license_entry" : "CC BY-NC-ND 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-sa/3.0/at/",
        "license_entry" : "CC BY-NC-SA 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nd/3.0/at/",
        "license_entry" : "CC BY-ND 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-sa/3.0/at/",
        "license_entry" : "CC BY-SA 3.0 AT"
    },
            {
                "license_link" : "http://creativecommons.org/publicdomain/mark/1.0/",
        "license_entry" : "Public Domain Mark 1.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by/4.0/",
        "license_entry" : "CC BY 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc/4.0/",
        "license_entry" : "CC BY-NC 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-nd/4.0/",
        "license_entry" : "CC BY-NC-ND 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-sa/4.0/",
        "license_entry" : "CC BY-NC-SA 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nd/4.0/",
        "license_entry" : "CC BY-ND 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-sa/4.0/",
        "license_entry" : "CC BY-SA 4.0"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by/2.0/",
        "license_entry" : "CC BY 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-sa/2.0/",
        "license_entry" : "CC BY-SA 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc/2.0/",
        "license_entry" : "CC BY-NC 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nd/2.0/",
        "license_entry" : "CC BY-ND 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-sa/2.0/",
        "license_entry" : "CC BY-NC-SA 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-nd/2.0/",
        "license_entry" : "CC BY-NC-ND 2.0 Generic"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by/3.0/",
        "license_entry" : "CC BY 3.0 Unported"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-sa/3.0/",
        "license_entry" : "CC BY-SA 3.0 Unported"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc/3.0/",
        "license_entry" : "CC BY-NC 3.0 Unported"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nd/3.0/",
        "license_entry" : "CC BY-ND 3.0 Unported"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-sa/3.0/",
        "license_entry" : "CC BY-NC-SA 3.0 Unported"
    },
            {
                "license_link" : "http://creativecommons.org/licenses/by-nc-nd/3.0/",
        "license_entry" : "CC BY-NC-ND 3.0 Unported"
    }]
            ',true);
        foreach ($arrayLicenses as $license) {
            $sql = "INSERT INTO licenses (name,link) VALUES ('" . $license['license_entry'] . "', '" . $license['license_link'] ."')";
            if (mysqli_query($connYarm, $sql)) {
                //echo('License ' . $license['license_entry'] . ' inserted successfully . <br > ');
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connYarm) . "<br>";
                exit;
            }
        }

    }*/
}
