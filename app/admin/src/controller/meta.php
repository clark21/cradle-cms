<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Meta Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/super/meta/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        $sortable = [
            'meta_type'
        ];

        foreach($request->getStage('order') as $key => $direction) {
            if(!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
        'meta_active',
            'meta_type'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('meta-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-meta-search page-admin';
    $data['title'] = cradle('global')->translate('Meta');
    $body = cradle('/app/admin')->template('meta/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Meta Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/meta/create/:type', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-meta-create page-admin';
    $data['title'] = cradle('global')->translate('Create Meta');

    cradle('global')->handlebars()->registerHelper('is_array', function($value, $option) {
        if(is_array($value)) {
            return $option['fn']();
        }

        return $option['inverse']();
    });

    if($request->getStage('type') === 'user') {
        $data['title'] = cradle('global')->translate('Create User Type');
        $data['item']['meta_type'] = 'user';
        $data['item']['meta_fields'] = include(__DIR__ . '/../config/user.php');
    } else {
        $data['title'] = cradle('global')->translate('Create Node Type');
        $data['item']['meta_type'] = 'node';
        $data['item']['meta_fields'] = include(__DIR__ . '/../config/node.php');
    }

    $body = cradle('/app/admin')->template(
        'meta/form',
        $data,
        [
            'meta_styles',
            'meta_templates',
            'meta_scripts',
            'meta_row',
            'meta_types',
            'meta_lists',
            'meta_details',
            'meta_validation',
            'meta_update',
            'meta_type-options',
            'meta_format-options',
            'meta_validation-options'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Meta Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/meta/update/:meta_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('meta-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/meta/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-meta-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Meta');
    $body = cradle('/app/admin')->template('meta/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Meta Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/meta/create/:type', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    $request->setStage('meta_type', $request->getStage('type'));

    //if meta_detail has no value make it null
    if ($request->hasStage('meta_detail') && !$request->getStage('meta_detail')) {
        $request->setStage('meta_detail', null);
    }

    //if meta_fields has no value make it null
    if ($request->hasStage('meta_fields') && !$request->getStage('meta_fields')) {
        $request->setStage('meta_fields', null);
    }

    //meta_flag is disallowed
    $request->removeStage('meta_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('meta-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/meta/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Meta was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/meta/search');
});

/**
 * Process the Meta Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/meta/update/:meta_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if meta_type has no value use the default value
    if ($request->hasStage('meta_type') && !$request->getStage('meta_type')) {
        $request->setStage('meta_type', 'post');
    }

    //if meta_detail has no value make it null
    if ($request->hasStage('meta_detail') && !$request->getStage('meta_detail')) {
        $request->setStage('meta_detail', null);
    }

    //if meta_fields has no value make it null
    if ($request->hasStage('meta_fields') && !$request->getStage('meta_fields')) {
        $request->setStage('meta_fields', null);
    }

    //meta_flag is disallowed
    $request->removeStage('meta_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('meta-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/meta/update/' . $request->getStage('meta_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Meta was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/meta/search');
});

/**
 * Process the Meta Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/meta/remove/:meta_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('meta-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Meta was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/meta/search');
});

/**
 * Process the Meta Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/meta/restore/:meta_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('meta-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Meta was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/meta/search');
});
