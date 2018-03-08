<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 use Cradle\Module\Role\Validator as RoleValidator;

/**
 * Render the Role Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'role_active', 1);
    }

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'role_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('role-search', $request, $response);

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-search page-admin';
    $data['title'] = cradle('global')->translate('Roles');
    $body = cradle('/module/role')->template('role/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Render the Role Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // get path file
    $path = $this->package('global')->path('config') . '/admin/permissions.php';

    // check if file
    if (!is_file($path)) {
        $permission[] = [
            'label' => 'Front End Access',
            'method' => 'all',
            'path' => '(?!/admin)/**'
        ];

        file_put_contents(
            $path,
            '<?php //-->' . "\n return " .
            var_export($permission, true) . ';'
        );
    }

    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    $permissions = cradle('global')->config('admin/permissions');

    $data['permissions'] = $permissions;

    if (isset($data['item']['role_permissions'])) {
        $rolePermissions = array_keys($data['item']['role_permissions']);

        // loop through data
        foreach ($rolePermissions as $permission) {
            $key = array_search($permission, array_column($data['permissions'], 'label'));
            if (is_int($key)) {
                $data['permissions'][$key]['checked'] = true;
            }
        }
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-create page-admin';
    $data['title'] = 'Create Role';
    $body = cradle('/module/role')->template('role/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Role Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/update/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // trigger role detail
    cradle()->trigger('role-detail', $request, $response);

    // get role details
    $data['item'] = $response->getResults();

    // premissions
    $permissions = cradle('global')->config('admin/permissions');

    $data['permissions'] = $permissions;

    // loop through data
    foreach ($data['item']['role_permissions'] as $permission) {
        $key = array_search($permission['label'], array_column($data['permissions'], 'label'));
        if (is_int($key)) {
            $data['permissions'][$key]['checked'] = true;
        }
    }

    if (!empty($request->getPost())) {
        // get post stored as item
        $data['item'] = $request->getPost();

        // get any errors
        $data['errors'] = $response->getValidation();

        if (isset($data['item']['role_permissions'])) {
            $rolePermissions = array_keys($data['item']['role_permissions']);

            // loop through data
            foreach ($rolePermissions as $permission) {
                $key = array_search($permission, array_column($data['permissions'], 'label'));
                if (is_int($key)) {
                    $data['permissions'][$key]['checked'] = true;
                }
            }
        }
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-update page-admin';
    $data['title'] = 'Update Role';
    $body = cradle('/module/role')->template('role/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Role Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/role/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    $permissions = cradle('global')->config('admin/permissions');

    // get roles
    if (isset($data['role_permissions'])) {
        // return all keys of role permissions
        $data['role_permissions'] = array_keys($data['role_permissions']);
        // set to request role permissions
        $request->setStage('role_permissions', $data['role_permissions']);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash('Invalid Data', 'error');
        return cradle()->triggerRoute('get', '/admin/role/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Role was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/role/search');
});

/**
 * Process the Role Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/role/update/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    $permissions = cradle('global')->config('admin/permissions');

    // get roles
    if (isset($data['role_permissions'])) {
        // return all keys of role permissions
        $data['role_permissions'] = array_keys($data['role_permissions']);
        // set to request role permissions
        $request->setStage('role_permissions', $data['role_permissions']);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/admin/role/update/' . $request->getStage('role_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Role was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/role/search');
});

/**
 * Process the Role Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/remove/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // trigger role detail
    cradle()->trigger('role-detail', $request, $response);

    // get role details
    $data['item'] = $response->getResults();

    // not removable
    if($data['item']['role_flag'] == 1) {
        //add a flash
        cradle('global')->flash('Invalid Action', 'error');
        //redirect
        return cradle('global')->redirect('/admin/role/search');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate('Role was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/role/search');
});

/**
 * Process the Role Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/restore/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate('Role was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/role/search');
});

/**
 * Render the Role Auth Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/auth/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // trigger role detail
    $data = $request->getStage();

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'role_active', 1);
    }

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    $request->setStage('auth', true);

    //trigger job
    cradle()->trigger('role-search', $request, $response);

    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-auth-search page-admin';
    $data['title'] = 'Access';
    $body = cradle('/module/role')->template('auth/search', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');


/**
 * Render the Role Auth Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/auth/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // trigger role detail
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-update page-admin';
    $data['title'] = 'Access Create';
    $body = cradle('/module/role')->template('auth/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Role Auth Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/role/auth/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    cradle()->trigger('role-auth-link', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/role/auth/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Role Auth was Added', 'success');

    //redirect
    cradle('global')->redirect('/admin/role/auth/search');
});


/**
 * Process the Role Auth Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/auth/:role_id/:role_auth_id/remove', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/role/auth/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    cradle()->trigger('role-auth-unlink', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/role/auth/search', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Role Auth was Removed', 'success');

    //redirect
    cradle('global')->redirect('/admin/role/auth/search');
});
