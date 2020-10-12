<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Str;
use Artisan;
use DB;

use Illuminate\Support\Facades\Schema;

use App\Actions\LoadDatabaseTables;
use App\Actions\ConfigApi;

class HomeController extends Controller
{
    public $resources;
    
    /**
     *
     */
    public function __construct()
    {
        $resourceFiles = File::allFiles(base_path('_setup/resources/'));

        $this->resources =  new \stdClass();

        foreach ($resourceFiles as $file) {
            $file = pathinfo($file);
            
            $resourcePath = $file['dirname'] . '/' . $file['basename'];
            
            $resourceText = File::get($resourcePath);
            
            $resourceObj = json_decode($resourceText);
            
            if ($resourceObj) {
                foreach ($resourceObj as $resourceName => $values) {
                    $this->resources->$resourceName = $values;
                    break;
                }
            }
        }
    }
    
    /**
     * GET /
     */
    public function index()
    {
        $loadDatabaseTables = new LoadDatabaseTables();

        return view('index')->with([
            'database' => $loadDatabaseTables->results,
            'resources' => $this->resources
        ]);
    }
}