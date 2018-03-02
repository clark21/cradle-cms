<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Role\Permission\Validator as PermissionValidator;

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
    $errors = PermissionValidator::getCreateErrors($data);

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
    $results['permissions'] = $this->package('global')->config('permissions');
    $results['permissions'][] = $permission;

    $path = $this->package('global')->path('config') . '/permissions.php';

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
    $permission = $this->package('global')->config('permissions')[$id];

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

    $path = $this->package('global')->path('config') . '/permissions.php';

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
 * Role Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('permission-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the role detail
    $this->trigger('role-detail', $request, $response);

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
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    $roleElastic = RoleService::get('elastic');

    //save to database
    $results = $roleSql->update([
        'role_id' => $data['role_id'],
        'role_active' => 0
    ]);

    //remove from index
    $roleElastic->remove($data['role_id']);

    //invalidate cache
    $roleRedis->removeDetail($data['role_id']);
    $roleRedis->removeSearch();

    $response->setError(false)->setResults($results);
});
