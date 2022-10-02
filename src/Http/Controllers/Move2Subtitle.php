<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Models\Elasticsearch;
use App\Models\Ref;

class Move2Subtitle extends Controller

{

    public function move2subtitle()
    {


        $pars = array(' - ', ' : ');

        foreach ($pars as $par) {
            $query = Ref::select('id as ref_id', 'title', 'subtitle');
            $query->where(function ($query) use ($par) {
                $query->whereNotNull('title');
                $query->where('title', 'like', "%" . $par . "%");
            });

            $z = 0;
            $results = $query->get();
            foreach ($results as $qRef) {
                $ref = Ref::find($qRef->ref_id);
                $title_subtitle = explode($par, $qRef->title, 2);
                $ref->title = $title_subtitle[0];
                if ($ref->subtitle == '')
                    $ref->subtitle = $title_subtitle[1];
                else
                    $ref->subtitle = $title_subtitle[1] . '; ' . str_replace(' - ', ' ; ', $ref->subtitle);
                echo($z . '= ' . $ref->title . ' - ' . $ref->subtitle . '<br>');
                $ref->update();
                $z++;
            }
        }
    }


}
