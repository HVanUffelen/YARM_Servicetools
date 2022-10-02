<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Models\Elasticsearch;
use App\Models\Ref;

class HashRefsController extends Controller
{

public static function hash()
    {
        //$allIds = Ref::select('id')->get();
        //$max = count($allIds);
        $max=62820;

        for ($i = 0; $i <= $max; $i++) {
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

    }
