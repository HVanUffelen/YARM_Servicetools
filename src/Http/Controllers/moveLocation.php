<?php
namespace Yarm\Adminnames\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ElasticsearchController;
use App\Models\Elasticsearch;
use App\Models\Ref;

class moveLocation extends Controller

{
    public function moveLocation()
    {
        $data = ref::where('location', 'like', '%Bundschuh%')
            ->orWhere('location','like','%Mc Martin%')->get();
        foreach ($data as $ref) {
            $location = $ref->location;
            if ($ref->source_library != '') {
                if ($ref->source_library != 'Data imported/adapted with Database Jack Mc Martin')
                $ref->source_library .= ' - ' . $location;
            }
            else
                $ref->source_library = $location;
            $ref->location = '';
            $ref->save();
        }
    }
}
