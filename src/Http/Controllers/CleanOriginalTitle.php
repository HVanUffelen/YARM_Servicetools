<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Http\Controllers\RefController;
use App\Models\Elasticsearch;
use App\Models\Place_ref;
use App\Models\Ref;
use Illuminate\Support\Facades\Storage;

class CleanOriginalTitle extends Controller

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

    public function moveCommentsInOriginalTitle()
    {
        $query = Ref::select('id as ref_id', 'orig_title', 'year_original_title', 'place_original_title', 'publisher_original_title');

        $query->where(function ($query) {
            $query->whereNotNull('orig_title');
            $query->where('orig_title', 'not like', '%;%');
            //$query->where('year_original_title','=',0);
            $query->whereNull('place_original_title');
            $query->whereNull('publisher_original_title');
            $query->where(function ($query) {
                $query->orwhere('year','>',1840);
                $query->orwhere('year','=',0);
            });
        });

        if (Storage::exists('/' . 'exeptionsCOOriTitle.txt')) {
            $exeptions = explode('#', Storage::get('/' . 'exeptionsCOOriTitle.txt'));
            foreach ($exeptions as $exeption) {
                //echo($exeption . '<br>');
                $query->where('orig_title', 'not like', '%' . $exeption . '%');
            }
        }

        $query->where(function ($query) {
            $query->orWhere('orig_title', 'regexp', '[( ][12][0-9]{3}[)]');
            $query->orWhere('orig_title', 'regexp', '[,][ ][12][0-9]{3}(?!-)(?!\/)');
            $query->orWhere('orig_title', 'regexp', '[ ][(]*[^(]+[:,].*?[)]');
        });

        //exit;
        $z = 0;
        $results = $query->get();

        foreach ($results as $qRef) {
            $ref = Ref::find($qRef->ref_id);
            if ($ref->orig_title == 'Boris, 1966')
                echo ($ref->orig_title);
            $yearsBetweenBrackets = array();
            preg_match('/[( ][12][0-9]{3}[)]/', $qRef->orig_title, $yearsBetweenBrackets);
            if (count($yearsBetweenBrackets)) {
                if ($qRef->year_original_title == null || $qRef->year_original_title == 0 || $qRef->year_original_title == '') {
                    $ref->year_original_title = trim(str_replace(array('(', ')'), '', $yearsBetweenBrackets[0]));
                    if (preg_match('/[(][12][0-9]{3}[)]/', $qRef->orig_title) > 0) {
                        $ref->orig_title = str_replace($yearsBetweenBrackets[0], '', $ref->orig_title);
                    } else {
                        $ref->orig_title = str_replace(str_replace(array(')'), '', $yearsBetweenBrackets[0]), '', $ref->orig_title);
                    }
                } elseif ($ref->year_original_title == trim(str_replace(array('(', ')'), '', $yearsBetweenBrackets[0]))) {
                    $ref->orig_title = str_replace($yearsBetweenBrackets[0], '', $ref->orig_title);
                }
                $qRef->year_original_title = trim(str_replace(array('(', ')'), '', $yearsBetweenBrackets[0]));
                $qRef->orig_title = str_replace($yearsBetweenBrackets[0], '', $ref->orig_title);
                $ref->update();
            }

            $yearsAfterComma = array();
            preg_match('/[,][ ][12][0-9]{3}(?!-)(?!\/)/', $qRef->orig_title, $yearsAfterComma);
            if (count($yearsAfterComma) == 1) {
                if ($qRef->year_original_title == null || $qRef->year_original_title == 0 || $qRef->year_original_title == '') {
                    $ref->year_original_title = trim(str_replace(array(','), '', $yearsAfterComma[0]));
                    $ref->orig_title = str_replace($yearsAfterComma[0], '', $ref->orig_title);
                } elseif ($ref->year_original_title == trim(str_replace(array(','), '', $yearsAfterComma[0]))) {
                    $ref->orig_title = str_replace($yearsAfterComma[0], '', $ref->orig_title);
                }
                $qRef->year_original_title = trim(str_replace(array(','), '', $yearsAfterComma[0]));
                $qRef->orig_title = str_replace($yearsAfterComma[0], '', $ref->orig_title);
                $ref->update();
            }

            if (($qRef->place_original_title == null || $qRef->place_original_title == '') ||
                ($qRef->publisher_original_title == null || $qRef->publisher_original_title == '')) {
                $publishersOrGeoplaces = array();
                preg_match('/[ ][(]*[^(]+[:,].*?[)]/', $qRef->orig_title, $publishersOrGeoplaces);
                if (count($publishersOrGeoplaces) == 1) {
                    $pubAndGeo = explode(':', $publishersOrGeoplaces[0]);
                    if (isset($pubAndGeo[0]))
                        $cleanedItemPlace = trim(str_replace(array('(', ')', ':', ','), '', $pubAndGeo[0]));
                    if (isset($pubAndGeo[1]))
                        $cleanedItemPublisher = trim(str_replace(array('(', ')', ':', ','), '', $pubAndGeo[1]));

                    if ($cleanedItemPlace) {
                        if (($qRef->place_original_title == null || $qRef->place_original_title == '')) {
                            $geoPlace = $this->getGeoplace($cleanedItemPlace, $ref);
                        }
                        $ref->place_original_title = $cleanedItemPlace;
                        $qRef->place_original_title = $cleanedItemPlace;
                        //$geoPlace = null;
                    }
                    if ($cleanedItemPublisher) {
                        if ($qRef->publisher_original_title == null || $qRef->publisher_original_title == '') {
                            $ref->publisher_original_title = $cleanedItemPublisher;
                            $qRef->publisher_original_title = $cleanedItemPublisher;
                        }
                    }
                    $ref->orig_title = str_replace($publishersOrGeoplaces[0], '', $ref->orig_title);
                    $qRef->orig_title = str_replace($publishersOrGeoplaces[0], '', $ref->orig_title);
                    $ref->update();
                }
            }
            $z++;
            $string = '';
            if (!empty($qRef->orig_title))
                $string .= '(' . $z . ') Title: ' . $qRef->orig_title;
            if (!empty($qRef->publisher_original_title))
                $string .= ' - Publisher: ' . $qRef->publisher_original_title;
            if (!empty($qRef->place_original_title))
                $string .= ' - Place: ' . $qRef->place_original_title;
            if (!empty($qRef->year_original_title))
                $string .= ' - Year: ' . $qRef->year_original_title;
            $string .= '<br>';
            echo($string);
        }
    }

}
