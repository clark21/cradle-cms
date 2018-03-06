<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Role\Validator as RoleValidator;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Permission Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('permission-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = RoleValidator::getPermissionCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $permission = [
        'label' => $data['permission_label'],
        'method' => $data['permission_method'],
        'path' => $data['permission_path'],
    ];

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot

    // get permissions
    $results['permissions'] = $this->package('global')->config('admin/permissions');
    $results['permissions'][] = $permission;

    $path = $this->package('global')->path('config') . '/admin/permissions.php';

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
 * Permission Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('permission-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['permission_key'])) {
        $id = $data['permission_key'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (is_null($id)) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $permission = $this->package('global')->config('admin/permissions')[$id];

    $results['row'] = [
        'permission_label'  => $permission['label'],
        'permission_method' => $permission['method'],
        'permission_path'   => $permission['path']
    ];


    $response->setError(false)->setResults($results);
});

/**
 * Permission Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('permission-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['permission_key'])) {
        $id = $data['permission_key'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (is_null($id)) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    $permission = [
        'label' => $data['permission_label'],
        'method' => $data['permission_method'],
        'path' => $data['permission_path'],
    ];
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    // get permissions
    $results['permissions'] = $this->package('global')->config('permissions');
    $results['permissions'][$id] = $permission;

    $path = $this->package('global')->path('config') . '/admin/permissions.php';

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
 * Permission Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('permission-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the permission data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['permission_key'])) {
        $id = $data['permission_key'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (is_null($id)) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Process Data
    $labels = [];
    $results['permissions'] = $this->package('global')->config('admin/permissions');
    if (isset($results['permissions'][$id])) {
        // get labels
        $labels[] = $results['permissions'][$id]['label'];
        // unset permission
        unset($results['permissions'][$id]);
        // return all values
        $results['permissions'] = array_values($results['permissions']);
    }

    // role request
    $roleRequest = Request::i()
        // set stage label
        ->setStage('labels', $labels);

    // role response
    $roleResponse = Response::i()->load();

    // trigger job
    cradle()->trigger('role-permissions-update', $roleRequest, $roleResponse);

    $path = $this->package('global')->path('config') . '/admin/permissions.php';

    file_put_contents(
        $path,
        '<?php //-->' . "\n return " .
        var_export($results['permissions'], true) . ';'
    );

    $response->setError(false)->setResults($results);
});
