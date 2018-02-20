<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Object\Service as ObjectService;
use Cradle\Module\System\Object\Validator as ObjectValidator;
use Cradle\Module\System\Object\Schema as ObjectSchema;

use Cradle\Module\System\Schema as SystemSchema;
use Cradle\Module\System\Exception as SystemException;

/**
 * System Object Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    $errors = $schema
        ->model()
        ->validator()
        ->getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $schema
        ->model()
        ->formatter()
        ->formatData(
            $data,
            $this->package('global')->service('s3-main'),
            $this->package('global')->path('upload')
        );

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');
    //save object to database
    $results = $objectSql->create($data);

    //get the primary value
    $primary = $results[$schema->getPrimary()];

    $table = $schema->getTableName();
    $relations = $schema->getRelations();

    //loop through relations
    foreach ($relations as $name => $relation) {
        //link relations
        if(isset($data[$relation['primary2']])) {
            $objectSql->link(
                $table,
                $primary,
                $data[$relation['primary2']]
            );
        }
    }

    //index object
    $objectElastic->create($primary);

    //invalidate cache
    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * System Object Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    $id = $key = null;
    $slugs = $schema->getSlugs($schema->getPrimary());

    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $id = $data[$slug];
            $key = $slug;
            break;
        }
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
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $objectRedis->getDetail($key . '-' . $id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $objectElastic->get($key, $id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $objectSql->get($key, $id);
        }

        if ($results) {
            //cache it from database or index
            $objectRedis->createDetail($key . '-' . $id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Object Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    if(!$request->hasStage('schema')) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($request->getStage('schema'));

    $primary = $schema->getPrimary();
    $active = $schema->getActive();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save to database
    if($active) {
        $payload = [];
        $payload[$primary] = $data[$primary];
        $payload[$active] = 0;

        $results = $objectSql->update($payload);
    } else {
        $results = $objectSql->remove($data[$primary]);
    }

    //remove from index
    $objectElastic->remove($data[$primary]);

    //invalidate cache
    $slugs = $schema->getSlugs($primary);
    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $objectRedis->removeDetail($data[$slug]);
        }
    }

    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * System Object Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    if(!$request->hasStage('schema')) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($request->getStage('schema'));

    $primary = $schema->getPrimary();
    $active = $schema->getActive();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save to database
    $payload = [];
    $payload[$primary] = $data[$primary];
    $payload[$active] = 1;

    $results = $objectSql->update($payload);

    //create index
    $objectElastic->create($data[$primary]);

    //invalidate cache
    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * System Object Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $objectRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $objectElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $objectSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $objectRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * System Object Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    $errors = $schema
        ->model()
        ->validator()
        ->getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $schema
        ->model()
        ->formatter()
        ->formatData(
            $data,
            $this->package('global')->service('s3-main'),
            $this->package('global')->path('upload')
        );

    //get the primary value
    $primary = $schema->getPrimary();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save object to database
    $results = $objectSql->update($data);

    //index object
    $objectElastic->update($results[$primary]);

    //invalidate cache
    $slugs = $schema->getSlugs($primary);
    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $objectRedis->removeDetail($data[$slug]);
        }
    }

    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * System Object Csv Export Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-csv-export', function ($request, $response) {
    $data['csv'] = $request->getStage('csv');
    $data['header'] = $request->getStage('header');
    $data['filename'] = $request->getStage('filename');

    //Set CSV header
    foreach (array_keys($data['csv'][0]) as $key => $value) {
            cradle()->inspect($value);
            cradle()->inspect($data['header']);
        if (array_key_exists($value, $data['header'])) {
            $header[$value] = $data['header'][$value];
            $fields[] = $value;
        }
    }

    $fields = array_intersect(array_keys($data['header']), $fields);
    $head = array_intersect(array_keys($data['header']), array_keys($header));

    foreach ($head as $key => $value) {
        $head[$key] =  $header[$value];
    }

    //Set new rows by required field
    foreach ($data['csv'] as $row) {
        $newRow = [];
        $arranged = [];

        foreach ($row as $key => $value) {
            if (in_array($key, $fields)) {
                $newRow[array_search($key, $fields)] = $row[$key];
            }
        }

        ksort($newRow);
        $newData[] = array_combine($fields, $newRow);
    }

    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=' . $data['filename']);

    ob_clean();
    $f = fopen('php://output', 'w');

    fputcsv($f, $head);

    foreach ($newData as $row) {
        fputcsv($f, $row);
    }

    fclose($f);

    return ' ';
});

/**
 * System Object Csv Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-csv-import', function ($request, $response) {
    //get columns
    $columns = $request->getStage('keys');

    if (!$columns) {
        return $response->setError(true, 'Column is not set');
    }

    $data  = array();
    $mimeTypes = array(
        'text/comma-separated-values',
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel'
    );

    //validate file
    if (empty($_FILES['csv']['tmp_name'])) {
        return $response->setError(true, 'No CSV');
    }

    $extension = substr(strrchr($_FILES['csv']['tmp_name'], "."), 1);
    if (!in_array($_FILES['csv']['type'], $mimeTypes) || $extension == 'csv') {
        return $response->setError(true, 'Invalid CSV');
    }

    $handle = fopen($_FILES['csv']['tmp_name'], 'r');

    $csv = array();
    if ($handle !== false) {
        $ctr = 0;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            //if columns not match
            if (count($columns) != count($row)) {
                return $response->setError(true, 'Columns not Match');
            }

            //set header
            if ($ctr == 0) {
                $data['header'] = $row;
            } else {
                $csv[] = $row;
            }

            $ctr++;
        }
    }

    //set columns to key
    foreach ($csv as $item) {
        foreach ($item as $key => $value) {
            $tmp[$columns[$key]] = $value;
        }
        $data['csv'][] = $tmp;
    }

    $response->setError(false)->setResults($data);
});

/**
 * System Object Item Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-import', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //set counter
    $ctr = ['new' => 0, 'old' => 0];

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);
    //----------------------------//
    // 2. Validate Data
    //validate data
    foreach ($data['csv'] as $item) {
        $errors = $schema
            ->model()
            ->validator()
            ->getCreateErrors($item);

        //if there are errors
        if (!empty($errors)) {
            return $response
                ->setError(true, 'Invalid Parameters')
                ->set('json', 'validation', $errors);
        }
    }

    // remove products on stage first
    $request->removeStage('csv');

    // There is no error,
    // So proceed on adding/updating the items one by one
    foreach ($data['csv'] as $key => $item) {
        //get primary
        $primary = $item[$schema->getPrimary()];
        $request->setStage('filter', $schema->getPrimary(), $primary);

        cradle()->trigger('system-object-detail', $request, $response);
        $existingItem = $response->getResults();

        // be sure to clear product stage
        $request->removeStage();

        //set schema
        $request->setStage('schema', $data['schema']);

        if ($existingItem && !empty($item[$schema->getPrimary()])) {
            //if date created is empty
            if (isset($item[$data['schema'] . '_created']) && empty($item[$data['schema'] . '_created'])) {
                unset($item[$data['schema'] . '_created']);
            }

            // set item data to stage
            $request->setStage($item);

            //set primary
            $request->setStage($schema->getPrimary(), $primary);

            // trigger single object update event
            cradle()->trigger('system-object-update', $request, $response);
            // check response if there is an error
            if ($response->isError()) {
                // return error
                return;
            }

            //increment old counter
            $ctr['old']++;
            continue;
        }

        // unset primary and set object data to stage
        unset($item[$schema->getPrimary()]);
        $request->setStage($item);

        // trigger single product create event
        cradle()->trigger('system-object-create', $request, $response);
        // check response if there is an error
        if ($response->isError()) {
            // return error
            return;
        }

        //increment new counter
        $ctr['new']++;
    }

    $response->setError(false)->setResults($ctr);
});
