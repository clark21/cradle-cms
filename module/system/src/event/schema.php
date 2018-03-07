<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Validator as SystemValidator;
use Cradle\Module\System\Schema as SystemSchema;
use Cradle\Module\System\Service as SchemaService;

/**
 * System Schema Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = SystemValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    // filter relations
    if (isset($data['relations'])) {
        // filter out empty relations
        $data['relations'] = array_filter(
            $data['relations'],
            function ($relation) {
                // make sure we have relation name
                return $relation['name'] !== '' ? true : false;
            }
        );
    }

    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getName();

    //create table
    $schema->service('sql')->create($data);

    $path = $this->package('global')->path('config') . '/admin/schema';

    if (!is_dir($path)) {
        mkdir($path, 0777);
    }

    file_put_contents(
        $path . '/' . $table . '.php',
        '<?php //-->' . "\n return " .
        var_export($data, true) . ';'
    );

    //return response format
    $response->setError(false)->setResults($data);
});


/**
 * System Schema Create Elastic Mapping Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-create-elastic', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // check for required parameters
    if (!isset($data['name'])) {
        return $response->setError(true, 'Invalid parameters.');
    }

    // check if object exists
    $objectPath = $this->package('global')->path('config') . '/admin/schema/' . $data['name'] . '.php';
    if (!file_exists($objectPath)) {
        return $response->setError(true, 'Object doesn\'t exist.');
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // get object data
    $data = include_once ($objectPath);
    
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getName();

    //create elastic mappings
    $schema->service('elastic')->createMap();

    //return response format
    $response->setError(false)->setResults($data);
});

/**
 * System Schema Create Elastic Mapping Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-search-elastic', function ($request, $response) {
    
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $path = $this->package('global')->path('config') . '/admin/schema/elastic/';

    $files = scandir($path);

    $active = 1;
    if (isset($data['filter']['active'])) {
        $active = $data['filter']['active'];
    }

    $results = [];
    foreach ($files as $file) {
        if (//if this is not a php file
            (strpos($file, '.php') === false)
            //or active and this is not active
            || ($active && strpos($file, '_') === 0)
            //or not active and active
            || (!$active && strpos($file, '_') !== 0)
        ) {
            continue;
        }

        $object = $this->package('global')->config('admin/schema/' . substr($file, 0, -4));
        $object['name'] = $object['singular'] = preg_replace('/\.php$/', '', $file);
        $results[] = $object;
    }
    
    //set response format
    $response->setError(false)->setResults([
        'rows' => $results,
        'total' => count($results)
    ]);
});

/**
 * System Schema Map Elastic
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-map-elastic', function ($request, $response) {
    
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //check for required parameters
    if (!isset($data['name'])) {
        return $response->setError(true, 'Invalid parameters.');
    }

    // check if object exists
    $objectPath = $this->package('global')->path('config') . '/admin/schema/' . $data['name'] . '.php';
    if (!file_exists($objectPath)) {
        return $response->setError(true, 'Object doesn\'t exist.');
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // get object data
    
    // 4. Process Data
    $schema = SystemSchema::i($data['name']);
    
    //map elastic
    

    //create elastic mappings
    $schema->service('elastic')->map();
    
    //check if mapping is successfull
    if ($map === false) {
        return $response->setError(true, 'Something went wrong.');
    }
    
    $response->setError(false);
});

/**
 * System Schema populate elastic
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-populate-elastic', function($request, $response) {   
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // check for required fields
    if (!isset($data['name'])) {
        return $response->setError(true, 'Invalid parameters.');
    }

    // check if object exists
    $objectPath = $this->package('global')->path('config') . '/admin/schema/' . $data['name'] . '.php';
    if (!file_exists($objectPath)) {
        return $response->setError(true, 'Object doesn\'t exist.');
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // get object data
    
    // 4. Process Data
    $schema = SystemSchema::i($data['name']);
    $schema->service('elastic')->populate();
    $response->setError(false);
});

/**
 * System Schema Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['schema'])) {
        $id = $data['schema'];
    } else if (isset($data['name'])) {
        $id = $data['name'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $results = $this->package('global')->config('admin/schema/' . $id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');

    try {
        //remove table
        $systemSql->remove($data);
    } catch (\Exception $e) {
        return $response->setError(true, $e->getMessage());
    }

    $path = $this->package('global')->path('config')
        . '/admin/schema/'
        . $table
        . '.php';

    if (file_exists($path)) {
        $new = $this->package('global')->path('config')
            . '/admin/schema/_'
            . $table
            . '.php';

        rename($path, $new);
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $request->setStage('name', '_' . $request->getStage('name'));
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');

    try {
        //remove table
        $systemSql->restore($data);
    } catch (\Exception $e) {
        return $response->setError(true, $e->getMessage());
    }

    $path = $this->package('global')->path('config')
        . '/admin/schema/_'
        . $table
        . '.php';

    if (file_exists($path)) {
        $new = $this->package('global')->path('config')
            . '/admin/schema/'
            . $table
            . '.php';

        rename($path, $new);
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $path = $this->package('global')->path('config') . '/admin/schema/';

    $files = scandir($path);

    $active = 1;
    if (isset($data['filter']['active'])) {
        $active = $data['filter']['active'];
    }

    $results = [];
    foreach ($files as $file) {
        if (//if this is not a php file
            (strpos($file, '.php') === false)
            //or active and this is not active
            || ($active && strpos($file, '_') === 0)
            //or not active and active
            || (!$active && strpos($file, '_') !== 0)
        ) {
            continue;
        }

        $results[] = $this->package('global')->config('admin/schema/' . substr($file, 0, -4));
    }

    //set response format
    $response->setError(false)->setResults([
        'rows' => $results,
        'total' => count($results)
    ]);
});

/**
 * System Schema Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-schema-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = SystemValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    // filter relations
    if (isset($data['relations'])) {
        // filter out empty relations
        $data['relations'] = array_filter(
            $data['relations'],
            function ($relation) {
                // make sure we have relation name
                return $relation['name'] !== '' ? true : false;
            }
        );
    }

    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');
    //update table
    $systemSql->update($data);

    $path = $this->package('global')->path('config') . '/admin/schema';

    file_put_contents(
        $path . '/' . $table . '.php',
        '<?php //-->' . "\n return " .
        var_export($data, true) . ';'
    );

    //return response format
    $response->setError(false)->setResults($data);
});
