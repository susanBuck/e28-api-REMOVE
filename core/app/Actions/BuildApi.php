<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Str;
use File;
use Artisan;
use stdClass;
use Hash;

class BuildApi
{
    public $results;
    private $routes;
    private $resourceName;
    private $resourceNameLower;
    private $resourceNameStudly;

    /**
     *
     */
    public function __construct($resources)
    {
        $resources->user =  new stdClass();

        $this->clearMigrationFiles();
        
        foreach ($resources as $resourceName => $fields) {
            $this->resourceName = $resourceName;
            $this->resourceNameStudly = Str::studly($resourceName);
            $this->resourceNameLower = Str::lower($resourceName);
            $this->resourceNameLowerPlural = Str::plural(Str::lower($resourceName));

            if ($this->resourceName != 'user') {
                $this->createMigration($fields);
                $this->createModel();
                $this->createController($fields);
                $this->addRoutes();
                $this->runMigration('9999_99_99_999999_create_'.$this->resourceNameLower.'_table.php');
            } else {
                $this->runMigration('2014_10_12_000000_create_users_table.php');
            }

            $this->runSeeds();

            $this->results['resources'][] = $this->resourceName;
        }

        $this->writeRoutes();
    }

    /**
     *
     */
    private function runSeeds()
    {
        $fileName = $this->resourceNameLower.'-seeds.json';
        $filePath = base_path('../seeds/' . $fileName);

        $results = [
            'error' => [],
            'success' => []
        ];

        $exists = File::exists($filePath);
        
        if (!$exists) {
            return;
        } else {
            $seeds = File::get($filePath);
            $seeds = json_decode($seeds);

            if (is_null($seeds)) {
                $this->results['errors'][] = 'Invalid seed file: ' . $filePath;
                return;
            }
        }

        if ($this->resourceName != "user") {
            $class = "App\Models\GeneratedModels\\" . $this->resourceNameStudly;
        } else {
            $class = "App\Models\\" . $this->resourceNameStudly;
        }

        foreach ($seeds->seeds as $data) {
            $resource = new $class;

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                } else {
                    $resource->{$key} = ($key == 'password') ? Hash::make($value) : $value;
                }
            }

            $error = null;

            try {
                $resource->save();
            } catch (\Illuminate\Database\QueryException $e) {
                $error = 'Caught exception: '.  $e->getMessage(). "\n";
                $results['failed'][] = $error;
            }

            if (!$error) {
                $results['added'][] = $resource->toArray();
            }
        }
        
        $this->results['seeds'][$this->resourceName] = $results;
    }

    /**
     *
     */
    private function createModel()
    {
        $template = File::get(base_path('templates/Resource.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('resource', $this->resourceNameLower, $template);
        File::put(app_path('Models/GeneratedModels/' . $this->resourceNameStudly . '.php'), $template);
    }
    
    /**
     *
     */
    private function addRoutes()
    {
        $routes = File::get(base_path('templates/routes.php'));
        $routes = str_replace('Resource', $this->resourceNameStudly, $routes);
        $routes = str_replace('resource', $this->resourceNameLower, $routes);

        $this->routes .= $routes;
    }

    /**
     *
     */
    private function writeRoutes()
    {
        // Update routes after iterating through all the resources
        File::put(base_path('routes/generated-routes.php'), "<?php \n" . $this->routes);
    }

    /**
     *
     */
    private function createController($fields)
    {
        // Controller
        $template = File::get(base_path('templates/ResourceController.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('resource', $this->resourceNameLower, $template);

        $fieldsDeclaration = 'private $fields = [';
        foreach ($fields as $field => $details) {
            $fieldsDeclaration .= '"'.$field.'" => [';
            foreach ($details->validators as $validator) {
                $fieldsDeclaration .= '"'.$validator.'",';
            }

            $fieldsDeclaration .= '], ';
        }
        $fieldsDeclaration .= '];';

        $template = str_replace('private $fields = [];', $fieldsDeclaration, $template);

        File::put(app_path('Http/Controllers/GeneratedControllers/'.$this->resourceNameStudly.'Controller.php'), $template);
    }

    /**
     *
     */
    private function clearMigrationFiles()
    {
        # Remove any existing migrations we have created
        $existingFiles = File::files(base_path('database/migrations'));
        foreach ($existingFiles as $file) {
            if (Str::contains($file->getFilename(), '9999_99_99_999999_')) {
                File::delete($file->getPathname());
            }
        }
    }

    /**
     *
     */
    private function createMigration($fields)
    {
        # Create migration file for this resource
        $template = File::get(base_path('templates/migration.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('resource', $this->resourceNameLowerPlural, $template);

        $schema = '';

        foreach ($fields as $field => $fieldDetails) {
            $schema .= "\$table->" . $fieldDetails->type . "('" . $field ."'); \n";
        }

        $template = str_replace('# [schema]', $schema, $template);
        File::put(base_path('database/migrations/9999_99_99_999999_create_'.$this->resourceNameLower.'_table.php'), $template);
    }

    /**
     *
     */
    private function runMigration($migrationFile)
    {
        Artisan::call('migrate:refresh --force --path=database/migrations/'.$migrationFile);
    }
}