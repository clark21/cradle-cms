<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Role Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/search', function($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage('filter')) {
        $request->setStage('filter', 'role_active', 1);
    }

    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
            'role_active'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('role-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-search page-admin';
    $data['title'] = cradle('global')->translate('Roles');
    $body = cradle('/app/admin')->template('role/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Role Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/role/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if(!cradle('global')->role('role:create', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'error');

        // set content
        return cradle('global')->redirect('/admin/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // get post stored as item
    $data['item'] = $request->getPost();

    // if not empty item
    if (isset($data['item']) && !empty($data['item'])) {
        // get permissions key
        if (isset($data['item']['role_permissions'])) {
            $data['item']['role_permissions'] = array_keys($data['item']['role_permissions']);
        }
    }

    // get roles settings
    $roles = cradle()->package('global')->config('roles');

    // if not set role permissions
    if (!isset($data['item']['role_permissions'])) {
        $data['item']['role_permissions'] = [];
    }

    // define group role variable
    $groups = [];

    // loop roles
    foreach($roles as $key => $role) {
        // get by part
        $parts = explode(':', $role);
        // set action
        $action = $role;
        // collect group roles
        $groups[$parts[0]]['actions'][] = [
            'action' => $parts[1],
            'role' => $action,
            'checked' => in_array($action, $data['item']['role_permissions']) ? 1 : 0
        ];
    }

    // set grouped roles
    $data['roles'] = $groups;

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-create page-admin';
    $data['title'] = cradle('global')->translate('Create Role');
    $body = cradle('/app/admin')->template('role/form', $data);

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
$cradle->get('/admin/role/update/:role_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if(!cradle('global')->role('role:update', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'error');

        // set content
        return cradle('global')->redirect('/admin/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    $roles = cradle('global')->config('roles');

    // trigger role detail
    cradle()->trigger('role-detail', $request, $response);

    // get role details
    $data['item'] = $response->getResults();

    if (!empty($request->getPost())) {
        // get post stored as item
        $data['item'] = $request->getPost();

        // get any errors
        $data['errors'] = $response->getValidation();

        // if not empty item
        if (isset($data['item']) && !empty($data['item'])) {
            // get permissions key
            if (isset($data['item']['role_permissions'])) {
                $data['item']['role_permissions'] = array_keys($data['item']['role_permissions']);
            }
        }
    }


    // if not set
    if (!isset($data['item']['role_permissions'])) {
        $data['item']['role_permissions'] = [];
    }

    // define group role variable
    $groups = [];

    // loop roles
    foreach($roles as $key => $role) {
        // get by part
        $parts = explode(':', $role);
        // set action
        $action = $role;
        // collect group roles
        $groups[$parts[0]]['actions'][] = [
            'action' => $parts[1],
            'role' => $action,
            'checked' => in_array($action, $data['item']['role_permissions']) ? 1 : 0
        ];
    }

    // set grouped roles
    $data['roles'] = $groups;

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-role-update page-admin';
    $data['title'] = 'Update Roles';
    $body = cradle('/app/admin')->template('role/form', $data);

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
$cradle->post('/admin/role/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    // get roles
    if (isset($data['role_permissions'])) {
        // return all keys of role permissions
        $data['role_permissions'] = array_keys($data['role_permissions']);
        // set to request role permissions
        $request->setStage('role_permissions', $data['role_permissions']);
    }

    // set role type admin
    $request->setStage('role_type', 'admin');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash('Invalid Data', 'success');
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
$cradle->post('/admin/role/update/:role_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

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
    if($response->isError()) {
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
$cradle->get('/admin/role/remove/:role_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if(!cradle('global')->role('role:remove', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'error');

        // set content
        return cradle('global')->redirect('/admin/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
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
$cradle->get('/admin/role/restore/:role_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if(!cradle('global')->role('role:restore', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'error');

        // set content
        return cradle('global')->redirect('/admin/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Role was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/role/search');
});
