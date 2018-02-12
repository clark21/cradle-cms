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
    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getTableName();

    //create table
    $schema->service('sql')->create($data);

    $path = $this->package('global')->path('config') . '/schema';

    if(!is_dir($path)) {
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
    if(isset($data['schema'])) {
        $id = $data['schema'];
    } else if(isset($data['name'])) {
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
    $results = $this->package('global')->config('schema/' . $id);

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
    $table = $schema->getTableName();
    //this/these will be used a lot
    $systemSql = $schema->get('sql');
    //remove table
    $systemSql->remove($data);

    $path = $this->package('global')->path('config')
        . '/schema/'
        . $table
        . '.php';

    if(file_exists($path)) {
        $new = $this->package('global')->path('config')
            . '/schema/_'
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
    $table = $schema->getTableName();
    //this/these will be used a lot
    $systemSql = $schema->get('sql');
    //remove table
    $systemSql->restoreTable($data);

    $path = $this->package('global')->path('config')
        . '/schema/_'
        . $table
        . '.php';

    if(file_exists($path)) {
        $new = $this->package('global')->path('config')
            . '/schema/'
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
    $path = $this->package('global')->path('config') . '/schema/';

    $files = scandir($path);

    $active = 1;
    if (isset($data['filter']['active'])) {
        $active = $data['filter']['active'];
    }

    $results = [];
    foreach($files as $file) {
        if(
            //if this is not a php file
            (strpos($file, '.php') === false)
            //or active and this is not active
            || ($active && strpos($file, '_') === 0)
            //or not active and active
            || (!$active && strpos($file, '_') !== 0)
        ) {
            continue;
        }

        $results[] = $this->package('global')->config('schema/' . substr($file, 0, -4));
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
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $schema = SystemSchema::i($data);
    $table = $schema->getTableName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');
    //update table
    $systemSql->update($data);

    $path = $this->package('global')->path('config') . '/schema';

    file_put_contents(
        $path . '/' . $table . '.php',
        '<?php //-->' . "\n return " .
        var_export($data, true) . ';'
    );

    //return response format
    $response->setError(false)->setResults($data);
});