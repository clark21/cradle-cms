<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Permission Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/permission/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    if(!$request->hasStage('filter')) {
        $request->setStage('filter', 'permission_active', 1);
    }

    // get path file
    $path = $this->package('global')->path('config') . '/permissions.php';

    // check if file
    if(!is_file($path)) {
        file_put_contents(
            $path,
            '<?php //-->' . "\n return [];"
        );
    }

    // get permissions
    $data['permissions'] = $this->package('global')->config('permissions');

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-permission-search page-admin';
    $data['title'] = cradle('global')->translate('Permissions');
    $body = cradle('/app/admin')->template('permission/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Permission Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/permission/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if(!cradle('/module/role')->hasPermissions($request)) {
        cradle('global')->flash('Request not Permitted', 'error');
        return cradle('global')->redirect('/admin/permission/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-permission-create page-admin';
    $data['title'] = cradle('global')->translate('Create Permission');
    $body = cradle('/app/admin')->template('permission/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Permission Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/permission/update/:permission_key', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if(!cradle('/module/role')->hasPermissions($request)) {
        cradle('global')->flash('Request not Permitted', 'error');
        return cradle('global')->redirect('/admin/permission/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // trigger role detail
    cradle()->trigger('permission-detail', $request, $response);

    // get role details
    $data['item'] = $response->getResults('row');

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-permission-update page-admin';
    $data['title'] = 'Update Permissions';
    $data['action'] = 'Updating Permissions';
    $body = cradle('/app/admin')->template('permission/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Permission Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/permission/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if(!cradle('/module/role')->hasPermissions($request)) {
        cradle('global')->flash('Request not Permitted', 'error');
        return cradle('global')->redirect('/admin/permission/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('permission-create', $request, $response);

    // 4. Interpret Results
    if($response->isError()) {
        cradle('global')->flash('Invalid Data', 'error');
        return cradle()->triggerRoute(
            'get',
            '/admin/permission/create',
            $request,
            $response
        );
    }

    //it was good
    //add a flash
    cradle('global')->flash('Permission was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/permission/search');
});

/**
 * Process the Permission Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/permission/update/:permission_key', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('permission-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/permission/update/' . $request->getStage('permission_key');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Permission was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/permission/search');
});

/**
 * Process the Permission Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/permission/remove/:permission_key', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    //cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to prepare
    // get permissions
    $data['permissions'] = $this->package('global')->config('permissions');

    cradle()->inspect($data); exit;

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('permission-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Permission was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/permission/search');
});

/**
 * Process the Permission Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/permission/restore/:role_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('permission-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Permission was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/permission/search');
});
