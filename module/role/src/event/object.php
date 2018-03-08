<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

 use Cradle\Http\Request;
 use Cradle\Http\Response;

/**
 * System Object Permission Make Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-permission-make', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Prepare Data
    // default permissions
    $permissions = include(dirname(__DIR__) . '/permissions.php');

    //----------------------------//
    // 3. Process Data
    // get path file
    $path = $this->package('global')->path('config') . '/admin/permissions.php';

    // check if file
    if (!is_file($path)) {
        file_put_contents(
            $path,
            '<?php //-->' . "\n return [];"
        );
    }

    // get permissions from config
    $results['permissions'] = $this->package('global')->config('admin/permissions');

    $lists = [];
    // loop all permissions
    foreach ($permissions as $permission) {
        // format label
        $label = sprintf($permission['label'], ucwords($data['name']));

        // get key
        $key = array_search($label, array_column($results['permissions'], 'label'));

        // continue if has key
        if (is_int($key)) {
            continue;
        }

        // collect permissions list
        $lists[] = [
            'label' => $label,
            'method' => sprintf($permission['method'], strtolower($data['name'])),
            'path' => sprintf($permission['path'], strtolower($data['name']))
        ];
    }

    // merge permissions
    $results['permissions'] = array_merge($results['permissions'], $lists);

    // update file permissions
    file_put_contents(
        $path,
        '<?php //-->' . "\n return " .
        var_export($results['permissions'], true) . ';'
    );

    //return response format
    $response->setError(false)->setResults([
        'rows' => $results['permissions'],
        'total' => count($results['permissions'])
    ]);
});

/**
 * System Object Permission Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-permission-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Prepare Data
    // default permissions
    $permissions = include(dirname(__DIR__) . '/permissions.php');

    //----------------------------//
    // 3. Process Data
    // get path file
    $path = $this->package('global')->path('config') . '/admin/permissions.php';

    // check if file
    if (!is_file($path)) {
        file_put_contents(
            $path,
            '<?php //-->' . "\n return [];"
        );
    }

    // get permissions from config
    $results['permissions'] = $this->package('global')->config('admin/permissions');

    $permissionLabels = [];

    // loop all permissions
    foreach ($permissions as $permission) {
        // format label
        $label = sprintf($permission['label'], ucwords($data['name']));

        $permissionLabels[] = $label;

        // get key
        $key = array_search($label, array_column($results['permissions'], 'label'));

        // unset permissions if has key
        if (is_int($key)) {
            unset($results['permissions'][$key]);
            $results['permissions'] = array_values($results['permissions']);
        };
    }

    // role request
    $roleRequest = Request::i()
        // set stage labels
        ->setStage('labels', $permissionLabels);

    // role response
    $roleResponse = Response::i()->load();

    // trigger job
    cradle()->trigger('role-permissions-update', $roleRequest, $roleResponse);

    // update file permissions
    file_put_contents(
        $path,
        '<?php //-->' . "\n return " .
        var_export($results['permissions'], true) . ';'
    );

    //return response format
    $response->setError(false)->setResults([
        'rows' => $results['permissions'],
        'total' => count($results['permissions'])
    ]);
});
