<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;


class uploadRefbase2YarmCoreController extends Controller
{

    public function upload2yarmcore()
    {

        list($connDBExport, $result1) = self::connect2Db('litwebdb');
        list($connDBImport, $result2) = self::connect2Db('yarmcore');

        //load users if not already in table
        $this->loadUsers($connDBExport, $connDBImport);

        $counter = 0;

        //run import
        while ($row = mysqli_fetch_assoc($result1)) {

            $row = array_map(function ($e) use ($connDBImport) {
                return mysqli_escape_string($connDBImport, $e);
            }, $row);

            $userMail = substr($row['created_by'], strpos($row['created_by'], '(') + 1, strlen($row['created_by']) - strpos($row['created_by'], '(') - 2);
            $modifierMail = substr($row['modified_by'], strpos($row['modified_by'], '(') + 1, strlen($row['modified_by']) - strpos($row['modified_by'], '(') - 2);

            $user_id = $this->selectID('users', $userMail, $connDBImport);
            $modifier_id = $this->selectID('users', $modifierMail, $connDBImport);
            $statusID = 1;
            $languageIDTarget = $this->selectID('languages', $row['language'], $connDBImport);
            $typeID = $this->selectID('types', $row['TYPE'], $connDBImport);

            //set missing DLBT_Vars;
            $primary = '';
            $literature = '';
            $parentRef = '';
            $genreID = '';
            $languageIDSource = '';
            $comments_on_illustrations = '';
            $comments_on_translation = '';
            $comments_on_publication = '';
            $comments_on_preface_postface = '';
            $orig_title = '';
            $physical_description = '';
            $year_original_title = '';
            $issue_title = '';
            $signature = '';
            $source_library = '';


            $insertFieldsDLBT = "(literature,primarytxt,user_id,modifier_id,language_target_id,language_source_id, genre_id,type_id, status_id,old_serial_id,old_parent_id,abstract,comments_on_illustrations,comments_on_publication,comments_on_preface_postface,comments_on_translation,container,edition,issue,issue_title,keywords,orig_title,pages,parent_id,physical_description,place,publisher,notes,title,year,year_original_title,volume,volume_numeric,series_editor,series_issue,series_title,series_volume,signature,series_volume_numeric,source_library,location, created_at, updated_at)";
            $insertValuesDLBT = "('" . $literature . "' , '" . $primary . "' , " . $user_id . ' , ' . $modifier_id . ' , ' . $languageIDTarget . ' , "' . $languageIDSource . '", "' . $genreID . '", ' . $typeID . ' , ' . $statusID . ' , ' . $row['serial'] . ' , "' . $parentRef . '", "' . $row['abstract'] . '", "' . $comments_on_illustrations . '", "' . $comments_on_publication . '", "' . $comments_on_preface_postface . '", "' . $comments_on_translation . '", "' . $row['publication'] . '", "' . $row['edition'] . '", "' . $row['issue'] . '", "' . $issue_title . '", "' . $row['keywords'] . '", "' . $orig_title . '", "' . $row['pages'] . '", "' . $parentRef . '", "' . $physical_description . '", "' . $row['place'] . '", "' . $row['publisher'] . '", "' . $row['notes'] . '", "' . $row['title'] . '", "' . $row['year'] . '", "' . $year_original_title . '", "' . $row['volume'] . '", "' . $row['volume_numeric'] . '", "' . $row['series_editor'] . '", "' . $row['series_issue'] . '", "' . $row['series_title'] . '", "' . $row['series_volume'] . '", "' . $signature . '", "' . $row['series_volume_numeric'] . '", "' . $source_library . '", "' . $row['location'] . '", "' . now() . '", "' . now() . '")';
            $sqlUpdateYarmRefs = "INSERT INTO refs " . $insertFieldsDLBT . " VALUES " . $insertValuesDLBT;

            //check if Data already in DB
            $hash = $this->hashRefs($row);
            $hashFound = false;
            $last_id = Null;

            $sqlCheckHash = "select hash from refs where hash = '" . $hash . "'";
            $resultHash = mysqli_query($connDBImport, $sqlCheckHash);
            if (mysqli_num_rows($resultHash)!==0){
                $hashFound = true;
                continue;
            }

            if (mysqli_query($connDBImport, $sqlUpdateYarmRefs) && $hashFound == false) {
                $last_id = $connDBImport->insert_id;
                $counter++;

                $sqlHash = "UPDATE refs SET hash = '" . $hash . "' where id = " . $last_id;
                mysqli_query($connDBImport, $sqlHash);

                echo("(" . $counter . ") Record (ID = " . $last_id . ") inserted successfully. <br>");
                $this->look4Names($row, $last_id, $connDBImport);
                $idPlaceEnglish = $this->setIdsInplace_ref('placerefs2geoplaces', $row['place'], $last_id, $connDBImport);
            } else {
                $last_id = $connDBImport->insert_id;
                echo "ERROR: Could not execute $sqlUpdateYarmRefs . " . mysqli_error($connDBImport) . "<br>";
            };

        }
        if (is_null($last_id))
            echo("No records inserted!.<br>");
        else
            echo("Records inserted successfully.<br>");
    }

    private function loadUsers($connDBExport, $connDBImport)
    {
        $sql = "select * from users order by user_id";
        $result = mysqli_query($connDBExport, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                foreach ($result as $user) {

                    //check if user = already in table!
                    $userMail = $user['email'];
                    $sql = "select * from users where email = '" . $userMail . "'";
                    if ($userData = mysqli_query($connDBImport, $sql)) {
                        $addUser = false;
                        $oldUser = $userData->fetch_assoc();
                        if ($oldUser == null)
                            $addUser = true;
                    } else {
                        echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                        exit;
                    }

                    if ($addUser == true) {
                        $userName = $user['first_name'] . " " . $user['last_name'];
                        $sql = "INSERT INTO users (name,email,password,created_at,updated_at) VALUES ('" . $userName . "', '" . $user['email'] . "', '" . "\$2y\$10\$SSj8PAGFfap7KnGk8QdZQOLLtshmc0aVEufeGyKt6aNGYKxWH2k6K" . "', '" . now() . "', '" . now() . "')";
                        if (mysqli_query($connDBImport, $sql)) {
                            $last_id = $connDBImport->insert_id;
                            if ($user['email'] == 'herbert . van - uffelen@univie . ac . at')
                                $userRoleId = 1;
                            else
                                $userRoleId = 7;
                            $sql = "INSERT INTO roles4user_user (user_id,roles4user_id,created_at,updated_at) VALUES ('" . $last_id . "', '" . $userRoleId . "', '" . now() . "', '" . now() . "')";
                            if (!mysqli_query($connDBImport, $sql)) {
                                echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                                exit;
                            }
                            $sql = "INSERT INTO options (user_id,records_per_page,show_auto_completion,language,language_target,created_at,updated_at) VALUES ('" . $last_id . "', '" . '5' . "', '" . 'true' . "' , '" . '20' . "', '" . '29' . "' , '" . now() . "', '" . now() . "')";
                            if (!mysqli_query($connDBImport, $sql)) {
                                echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                                exit;
                            }

                        } else {
                            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                            exit;
                        }

                    }
                }
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
            exit;
        }
    }

    private function look4Names($row, $last_id, $connDBImport)
    {
        if ($row['author'] | $row['corporate_author'] | $row['editor']) {

            if (isset($row['author']) and $row['author'] != '') {
                $role = $this->selectID('roles', 'author', $connDBImport);
                $this->splitAndSelectNames($row['author'], $role, $last_id, $connDBImport);
            }
            if (isset($row['corporate_author']) and $row['corporate_author'] != '') {
                $role = $this->selectID('roles', 'corporate', $connDBImport);
                $this->splitAndSelectNames($row['corporate_author'], $role, $last_id, $connDBImport);
            }
            if (isset($row['editor']) and $row['editor'] != '') {
                $role = $this->selectID('roles', 'editor', $connDBImport);
                $this->splitAndSelectNames($row['editor'], $role, $last_id, $connDBImport);
            }
        }
    }

    private function splitAndSelectNames($names, $role, $last_id, $connDBImport)
    {
        $names = str_replace(['., ', ' and ', ' und ', ' / '], '; ', $names);
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
            $result = mysqli_query($connDBImport, $sql);
            if ($result) {
                if ($result->num_rows == 0) {
                    $last_id_name = $this->addNamesToTableYarm($name, $role, $last_id, $i, $connDBImport);
                    $this->addToNameRef($last_id, $last_id_name, $role, $i, $connDBImport);
                } else {
                    $row = mysqli_fetch_assoc($result);
                    $this->addToNameRef($last_id, $row['id'], $role, $i, $connDBImport);
                }
            } else {
                echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                exit;
            }

            $i++;
        }
    }

    private function addToNameRef($lastRef_id, $last_NameId, $role, $position, $connDBImport)
    {
        $sql = 'INSERT INTO name_ref(name_id, ref_id, role_id, position, created_at, updated_at) VALUES(' . $last_NameId . ', ' . $lastRef_id . ', ' . $role . ', ' . $position . ", '" . now() . "', '" . now() . "')";
        if (mysqli_query($connDBImport, $sql)) {
            $last_id = $connDBImport->insert_id;
            //echo ($last_id . ": ID inserted successfully in name_ref. <br>");
        } else {
            $last_id = $connDBImport->insert_id;
            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
            dd($last_id);
        };
    }

    private function addNamesToTableYarm($name, $role, $last_id, $position, $connDBImport)
    {
        $arrayName = explode(', ', $name, 2);
        $familyName = $arrayName[0];
        if (isset($arrayName[1]))
            $first_name = $arrayName[1];
        else
            $first_name = '';
        //write to Table
        $sql = "INSERT INTO names (name,first_name,created_at,updated_at) VALUES ('" . $familyName . "', '" . $first_name . "', '" . now() . "' , '" . now() . "')";
        if (mysqli_query($connDBImport, $sql)) {
            $last_id = $connDBImport->insert_id;
            //echo ($last_id . ": Name inserted successfully. <br>");
        } else {
            $last_id = $connDBImport->insert_id;
            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
            dd($last_id);
        };

        return $last_id;
    }

    private function selectID($table, $name, $connDBImport)
    {
        if ($table == 'licenses')
            $sql = "select id from " . $table . " where link='" . $name . "'";
        else if ($table == 'users')
            $sql = "select id from " . $table . " where email='" . $name . "'";
        else
            $sql = "select id from " . $table . " where name='" . $name . "'";
        $result = mysqli_query($connDBImport, $sql);
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
            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
            exit;
        }
    }

    private function setIdsInplace_ref($table, $name, $refId, $connDBImport)
    {
        $sql = "select * from " . $table . " where placeOriginal = '" . $name . "'";
        $result = mysqli_query($connDBImport, $sql);
        if ($result) {
            if ($result->num_rows > 0) {
                $row = mysqli_fetch_assoc($result);
                $placeEnglish = $row['placeEnglish'];
                $sql = "select id from places where name = '" . $placeEnglish . "'";
                $result = mysqli_query($connDBImport, $sql);
                if ($result) {
                    if ($result->num_rows > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $idPlaceEnglish = $row['id'];
                        $sql = "INSERT INTO place_ref (place_id, ref_id, created_at, updated_at) VALUES ('" . $idPlaceEnglish . "', '" . $refId . "', '" . now() . "' , '" . now() . "')";
                        $result = mysqli_query($connDBImport, $sql);
                        if ($result) {
                            return $idPlaceEnglish;
                        } else {
                            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
                            exit;
                        }
                    }
                }
            } else {
                return null;
            }
        } else {
            echo "ERROR: Could not execute $sql . " . mysqli_error($connDBImport) . "<br>";
            exit;
        }
    }

    private function hashRefs($row)
    {
        $fields = ['title', 'publication', 'year', 'edition'];
        $names = str_replace(['., ', ' and '], '; ', $row['author']);
        $authors = explode("; ", $names);
        $hashData[] = $authors[0];

        foreach ($fields as $field) {
            if (isset($row[$field]) && !empty($row[$field]))
                $hashData[] = strval($row[$field]);
            else
                $hashData[] = null;
        }

        $hash = sha1(serialize($hashData));

        return $hash;
    }

    private function connect2Db($dbName)
    {

        $connDB = mysqli_connect("localhost", "root", "superpass222");

        if ($connDB) {
            //set dbName;
            if (mysqli_select_db($connDB, $dbName)) {
                $sql1 = "SELECT * from refs";
                $result1 = mysqli_query($connDB, $sql1);
                if (!$result1) {
                    echo "Could not successfully run query ($sql1) from DB: " . mysqli_error($connDB);
                    exit;
                }
                if (mysqli_num_rows($result1) == 0 | mysqli_num_rows($result1) == NULL) {
                    echo "No rows found, so am exiting";
                    exit;
                }
            } else {
                echo "Unable to select dbname: " . mysqli_error($connDB);
                exit;
            }
        } else {
            echo "Unable to connect to DB: " . mysqli_error($connDB);
            exit;
        }

        return [$connDB, $result1];

    }

}
