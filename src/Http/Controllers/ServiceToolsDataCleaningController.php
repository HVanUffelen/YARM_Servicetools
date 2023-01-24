<?php

namespace Yarm\Servicetools\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataCleaningController;
use App\Http\Controllers\PaginationController;
use App\Models\File;
use App\Models\Group;
use App\Models\Name;
use App\Models\Ref;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Yarm\Elasticsearch\ElasticsearchController;


class ServiceToolsDataCleaningController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        //$this->middleware('can:admin');
        $this->middleware('can:edit-names');
    }

    public function fileNotFoundList()
    {
        $q = '';
        return view('servicetools::files.fileNotFoundlist')->with(self::addFileNotFoundData($q));
    }

    public function commentsOnIllustrationsList()
    {
        $q = '';
        return view('servicetools::refs.commentsOnIllustrationsList')->with(self::addCommentsOnIllustrations($q));
    }

    public function commentsOnTranslationList()
    {
        $q = '';
        return view('servicetools::refs.commentsOnTranslationList')->with(self::addCommentsOnTranslations($q));
    }

    public function commentsOnPrefacePostfaceList()
    {
        $q = '';
        return view('servicetools::refs.commentsOnPrefacePostfaceList')->with(self::addCommentsOnPrefacePostface($q));
    }

    public function commentsOnPublicationList()
    {
        $q = '';
        return view('servicetools::refs.commentsOnPublicationList')->with(self::addCommentsOnPublication($q));
    }

    public function originalTitleList()
    {
        $q = '';
        return view('servicetools::refs.originalTitleList')->with(self::addOriginalTitles($q));
    }

    public function publisherList()
    {
        $q = '';
        return view('servicetools::refs.publisherList')->with(self::addPublishers($q));
    }

    static function addPublishers($q)
    {
        $colNames = ['publisher', 'quantity'];
        $paginationValue = PaginationController::getPaginationItemCount();

        $query = Ref::select('id as ref_id', 'publisher', DB::raw('COUNT(publisher) as quantity'))
            ->where('publisher', '!=', '')
            ->groupBy('publisher')
            ->havingRaw('count(publisher) > 1');

        if ($q != '') {
            $query->where('publisher', 'like', '%' . $q . '%');
        }

        $data = [
            'publishers' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    public function confirmPublisher(Request $request)
    {
        try {
            $changed_ids = [];
            foreach ($request->select as $selected_id) {
                $ref = Ref::find($selected_id);
                if ($ref->publisher != $request->edit) {
                    foreach (Ref::where('publisher', '=', $ref->publisher)->get() as $refSamePublisher) {
                        array_push($changed_ids, $refSamePublisher->id);
                        $refSamePublisher->publisher = $request->edit;
                        $refSamePublisher->update();
                    }
                }
            }
            return redirect('/ydbviews/publishers/?page=' . $request->pageUrl)
                ->with('alert-success', 'Succesfully changed publisher for Ref(s) with id(s) ' . implode(", ", $changed_ids) . ' to "' . $request->edit . '".');
        } catch (\Throwable $th) {
            return redirect('/' . strtolower(config('yarm.sys_name')) . '/publishers/?page=' . $request->pageUrl)
                ->with('alert-danger', $th);
        }
    }

    static function addCommentsOnPublication($q)
    {
        $colNames = ['ref_id', 'title', 'subtitle', 'comments_on_publication'];
        $paginationValue = PaginationController::getPaginationItemCount();

        $query = Ref::select('id as ref_id', 'title', 'subtitle', 'comments_on_publication')
            ->whereNotNull('comments_on_publication')
            ->where('comments_on_publication', '!=', '');

        // TODO : Exceptions for comments on publication
        // if (Storage::exists('/' . 'exeptionsCOOriTitle.txt')) {
        //     $exeptions = explode(',', Storage::get('/' . 'exeptionsCOOriTitle.txt'));
        //     foreach ($exeptions as $exeption){
        //         $query->where('orig_title', 'not like', '%' . $exeption . '%');
        //     }
        // }

        if ($q != '') {
            $query->where('comments_on_publication', 'like', '%' . $q . '%');
        }

        $data = [
            'comments_on_publication' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    public function editCommentsOnPublication(Request $request)
    {
        try {
            $ref = Ref::find($request->id);
            $ref->title = $request->title;
            $ref->subtitle = $request->subtitle;
            $ref->comments_on_publication = $request->comments_on_publication;
            $ref->update();
            return Ref::select('id as ref_id', 'title', 'subtitle', 'comments_on_publication')->find($request->id);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    static function addOriginalTitles($q)
    {
        $colNames = ['ref_id', 'original_title'];
        $paginationValue = PaginationController::getPaginationItemCount();

        $query = Ref::select('id as ref_id', 'orig_title as original_title');

        $query->where(function ($query) {
            $query->whereNotNull('orig_title');
            $query->where('orig_title', '!=', '');
        });

        $query->where(function ($query) {
            $query->orWhere('orig_title', 'regexp', '[( ][12][0-9]{3}[)]');
            $query->orWhere('orig_title', 'regexp', '[,][ ][12][0-9]{3}(?!-)(?!\/)');
            $query->orWhere('orig_title', 'regexp', '[ ][(]*[^(]+[:,].*?[)]');
        });

        if (Storage::exists('/' . 'exeptionsEditCOOriTitle.txt')) {
            $exeptions = explode(',', Storage::get('/' . 'exeptionsCOOriTitle.txt'));
            foreach ($exeptions as $exeption) {
                $query->where('orig_title', 'not like', '%' . $exeption . '%');
            }
        }

        if ($q != '') {
            $query->where('orig_title', 'like', '%' . $q . '%');
        }

        $data = [
            'original_titles' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    static function addCommentsOnTranslations($q)
    {
        $colNames = ['ref_id', 'comments_on_translation'];
        $paginationValue = PaginationController::getPaginationItemCount();

        $query = Ref::select('id as ref_id', 'comments_on_translation')
            ->whereNotNull('comments_on_translation');

        if (Storage::exists('/' . 'exeptionsCOTr.txt')) {
            $exeptions = explode('#', Storage::get('/' . 'exeptionsCOTr.txt'));

            $query->where(function ($query) use ($exeptions) {
                $query->where(function ($query) {
                    $query->where('comments_on_translation', '!=', '');
                });
            })->where(function ($query) use ($exeptions) {
                foreach ($exeptions as $exeption) {
                    $query->Where('comments_on_translation', '!=', $exeption);
                };
            });
        }

        if ($q != '') {
            $query->where('comments_on_translation', 'like', '%' . $q . '%');
        }

        $data = [
            'comments_on_translation' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    static function addCommentsOnIllustrations($q)
    {
        $colNames = ['ref_id', 'comments_on_illustrations'];
        $paginationValue = PaginationController::getPaginationItemCount();

        // $commentsOnTranslations = array_unique(Ref::where('comments_on_translations','!=', '')->pluck('commments_on_translations'),asc);

        $query = Ref::select('id as ref_id', 'comments_on_illustrations')
            ->whereNotNull('comments_on_illustrations');

        if (Storage::exists('/' . 'exeptionsCOIll.txt')) {
            $exeptions = explode(',', Storage::get('/' . 'exeptionsCOIll.txt'));

            $query->where(function ($query) use ($exeptions) {
                $query->where(function ($query) {
                    $query->whereNotNull('comments_on_illustrations');
                    $query->where('comments_on_illustrations', '!=', '');
                });
            })->Where(function ($query) use ($exeptions) {
                foreach ($exeptions as $exeption) {
                    $query->where('comments_on_illustrations', '!=', $exeption);
                }
            });
        }

        if ($q != '') {
            $query->where('comments_on_illustrations', 'like', '%' . $q . '%');
        }

        $data = [
            'comments_on_illustrations' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    static function addCommentsOnPrefacePostface($q)
    {
        $colNames = ['ref_id', 'comments_on_preface_postface'];
        $paginationValue = PaginationController::getPaginationItemCount();

        $query = Ref::select('id as ref_id', 'comments_on_preface_postface')
            ->whereNotNull('comments_on_preface_postface');

        if (Storage::exists('/' . 'exeptionsCOIll.txt')) {
            $exeptions = explode('#', Storage::get('/' . 'exeptionsCOPrPo.txt'));

            $query->where(function ($query) use ($exeptions) {
                $query->where(function ($query) {
                    $query->whereNotNull('comments_on_preface_postface');
                    $query->where('comments_on_preface_postface', '!=', '');
                });
            })->Where(function ($query) use ($exeptions) {
                foreach ($exeptions as $exeption) {
                    $query->where('comments_on_preface_postface', '!=', $exeption);
                }
            });
        }

        if ($q != '') {
            $query->where('comments_on_preface_postface', 'like', '%' . $q . '%');
        }

        $data = [
            'comments_on_preface_postface' => $query->paginate($paginationValue),
            'colNames' => $colNames
        ];

        return $data;
    }

    static function addFileNotFoundData($q)
    {
        $colNames = ['name', 'local_name'];
        $paginationValue = PaginationController::getPaginationItemCount();
        $localFiles = [];

        $files = File::select('id', 'name')
            ->where('esearch', 'like', '%not f%');

        if ($q != '') {
            $files->where('name', 'like', '%' . $q . '%');
        }

        foreach ($files->get() as $file) {
            $localFile = self::findLocalFile($file);
            $dataclean_file['id'] = $file->id;
            $dataclean_file['local_name'] = $localFile;
            array_push($localFiles, $dataclean_file);
        }

        $data = [
            'files' => $files->paginate($paginationValue),
            'localFiles' => $localFiles,
            'colNames' => $colNames
        ];

        return $data;
    }

    public static function findLocalFile($file)
    {
        $allLocalFiles = Storage::allFiles('YARMDBUploads');
        $validChars = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz123456789_,. ';

        try {
            // Get all words from the DB file
            $searchLike = preg_split('/[\s,_-]+/', pathinfo($file->name, PATHINFO_FILENAME));
            $searchLikeCount = count($searchLike);

            // Remove words with special characters
            foreach ($searchLike as $i => $term) {
                foreach (str_split($term) as $char) {
                    if (!in_array($char, str_split($validChars))) {
                        unset($searchLike[$i]);
                        break;
                    }
                }
            }
            $searchLikeCountNoSpecial = count($searchLike);

            foreach ($allLocalFiles as $localFile) {
                // Get all words in the local file
                $localFile = ltrim(substr($localFile, strpos($localFile, '/', 1)), '/');
                $localFileWords = preg_split('/[\s,_-]+/', pathinfo($localFile, PATHINFO_FILENAME));

                // Check if the localFile contains non-special-character words from our DB file
                $intersect = array_intersect($localFileWords, $searchLike);

                // If the amount of words without special characters is the same as the amount of intersected words
                // && the total amount of words in the DB file is the same as in the local file
                // THEN add the local file name to the file object
                if ($searchLikeCountNoSpecial == count($intersect)
                    && $searchLikeCount == count($localFileWords)) {
                    return $localFile;
                }
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function changeFileNames()
    {
        $files = File::where('esearch', 'like', '%not f%')->get();
        $successIds = [];
        $failedIds = [];
        foreach ($files as $file) {
            try {
                $localName = $file->name;
                $localName = str_replace('ö', 'Ф', $localName);
                $localName = str_replace('ü', 'Б', $localName);
                $localName = str_replace('ä', 'Д', $localName);
                $localName = str_replace('ß', 'c', $localName);
                Storage::move('YARMDBUploads/' . $localName, 'YARMDBUploads/' . $file->name);
                array_push($successIds, $file->id);
            } catch (\Throwable $th) {
                array_push($failedIds, $file->id);
            }
        }

        //try to store to Elasticsearch (if package = present)
        if (config('elasticsearch.elasticsearch_present') == true) {
            \Yarm\Elasticsearch\Http\Controllers\ElasticsearchController::upload2ElasticSearch(true, null);
        }


        return redirect()->back()
            ->with('alert-danger', 'Files with id(s) = ' . implode(", ", $failedIds) . ' could not be cleaned automatically.')
            ->with('alert-success', 'Files with id(s) = ' . implode(", ", $successIds) . ' are cleaned automatically.');
    }

    public function changeOneFileName(Request $request)
    {
        try {
            $file = File::find($request->id);
            $file->name = $request->name;
            $file->update();
            if ($request->local_name != $request->new_local_name) {
                Storage::move('YARMDBUploads/' . $request->local_name, 'YARMDBUploads/' . $request->new_local_name);
            }

            //upload to Elasticsearch (if package is present)
            if (config('elasticsearch.elasticsearch_present') == true) {
                \Yarm\Elasticsearch\Http\Controllers\ElasticsearchController::upload2ElasticSearch(false, $request->id);
            }

            return [$file, DataCleaningController::findLocalFile($file), Storage::has('YARMDBUploads/' . $request->new_local_name)];
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function updateViaf()
    {
        // Get all names with a VIAF_id that have not already been updated this month
        $viafNames = Name::where('VIAF_id', '!=', '')
            ->whereNotNull('VIAF_id')
            ->where('updated_at', '<', date('Y-m-1 00:00:00'))
            ->get();

        $count = 0;
        $total = count($viafNames);

        if ($total <= 0) {
            return redirect()->back()->with('alert-warning', __('All VIAF data is up to date'));
        }

        foreach ($viafNames as $name) {
            $response = Http::get('https://www.viaf.org/viaf/' . trim($name->VIAF_id) . '/viaf.json')->json();

            // Get all duplicate names

            // Get all subfields
            $subfields = array();
            if (isset($response['x400s'])) {
                if (!isset($response['x400s']['x400']['datafield']['subfield'])) {
                    foreach ($response['x400s']['x400'] as $x400) {
                        array_push($subfields, $x400['datafield']['subfield']);
                    }
                } else {
                    array_push($subfields, $response['x400s']['x400']['datafield']['subfield']);
                }
            }

            // Get names from subfields
            $names = array();
            if (count($subfields) > 0) {
                foreach ($subfields as $subfield) {
                    if (!isset($subfield['#text'])) {
                        $viafName = '';
                        foreach ($subfield as $subItem) {
                            $viafName .= (' ' . $subItem['#text']);
                        }
                        $viafName = trim($viafName);
                        array_push($names, $viafName);
                    } else {
                        array_push($names, $subfield['#text']);
                    }
                }
            }

            // Remove duplicate names
            $uniqueNames = array();
            if (count($names) > 0) {
                foreach ($names as $unfilteredName) {
                    $nameNoSpecialChars = preg_replace('/[^\x00-\x7F]/', '', $unfilteredName);
                    if (!in_array($nameNoSpecialChars, $uniqueNames)) array_push($uniqueNames, $unfilteredName);
                }
            }

            // Get xlinks
            $xLinks = array();
            if (isset($response['xLinks'])) {
                if (is_array($response['xLinks']['xLink'])) {
                    foreach ($response['xLinks']['xLink'] as $xLink) {
                        if (is_string($xLink)) {
                            array_push($xLinks, $xLink);
                        } elseif (isset($xLink['#text'])) {
                            array_push($xLinks, $xLink['#text']);
                        } else {
                            $response['xLinks']['xLink']['#text'];
                        }
                    }
                } else {
                    array_push($xLinks, $response['xLinks']['xLink']);
                }
            }

            // Get ISNI ID
            $ISNI_id = NULL;
            if (isset($response['sources'])) {
                if (!isset($response['sources']['source']['#text'])) {
                    foreach ($response['sources']['source'] as $source) {
                        if (str_starts_with($source['#text'], 'ISNI')) {
                            $ISNI_id = $source['@nsid'];
                        }
                    }
                } else {
                    if (str_starts_with($response['sources']['source']['#text'], 'ISNI')) {
                        $ISNI_id = $response['sources']['source']['@nsid'];
                    }
                }
            }

            // Get Nationality
            $nationality = NULL;
            if (isset($response['nationalityOfEntity'])) {
                if (!isset($response['nationalityOfEntity']['data']['text'])) {
                    $nationality = $response['nationalityOfEntity']['data'][0]['text'];
                } else {
                    $nationality = $response['nationalityOfEntity']['data']['text'];
                }
            }

            // Update name
            $name->alternative_names = (count($uniqueNames) == 1) ? $uniqueNames[0] : implode(';', $uniqueNames);
            $name->xLink = (count($xLinks) == 1) ? $xLinks[0] : implode(';', $xLinks);
            if (!isset($name->ISNI_id)) $name->ISNI_id = $ISNI_id;
            if (!isset($name->nationality)) $name->nationality = $nationality;
            if (!isset($name->gender)) $name->gender = $response['fixed']['gender'];
            if (!isset($name->birth_year)) $name->birth_year = $response['birthDate'] === '0' ? null : intval($response['birthDate']);
            if (!isset($name->death_year)) $name->death_year = $response['deathDate'] === '0' ? null : intval($response['deathDate']);

            $name->updated_at = date('Y-m-d H:m:s');
            $name->update();

            // Show progress in terminal
            $count++;
            //error_log('Completed name: ' . $count . '/' . $total . ' (ID: ' . $name->id . ')');
        }

        return redirect()->back()->with('alert-success', __('VIAF data updated') . ' (' . $count . ' names)');
    }

    public function getCommentsOnPublication(Request $request)
    {
        try {
            return Ref::select('id', 'title', 'subtitle', 'comments_on_publication')->find($request->id);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function CDZP()
    {
        $query = Ref::leftJoin('group_ref', 'group_ref.ref_id', '=', 'refs.id');
        $query->Where(function ($query) {
            $query->orWhere('pages', 'like', '%-%');
            $query->orWhere('physical_description', 'like', '%+ %');
        });
        $query->where('group_ref.group_id', '=', '3');
        $result = $query->get();

        foreach ($result as $dataSet) {
            $pages = str_replace(['-', ' - '], '–', $dataSet->pages);
            $description = ltrim($dataSet->physical_description, '+ ');
            $description = str_replace(['-', ' - '], '–', $description);
            $ref = Ref::find($dataSet->ref_id);
            if (isset($ref)) {
                $ref->pages = $pages;
                $ref->physical_description = $description;
                $ref->save();
            }

        }


    }


}
