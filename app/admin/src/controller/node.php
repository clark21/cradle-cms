<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;
use Cradle\Module\Meta\Fields;

/**
 * Render the Node Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/search', function($request, $response) {
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
            'node_status',
            'node_published'
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
        'node_active',
            'node_status',
            'node_type'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('node-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-node-search page-admin';
    $data['title'] = cradle('global')->translate('Nodes');
    $body = cradle('/app/admin')->template('node/search', $data);

    //set content
    $response
        ->setPage('title', ucwords($data['title']))
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Node Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-node-create page-admin';
    $data['title'] = cradle('global')->translate('Create Node');
    $body = cradle('/app/admin')->template('node/form', $data);

    //set content
    $response
        ->setPage('title', ucwords($data['title']))
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Node Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/update/:node_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('node-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/node/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-node-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Node');
    $body = cradle('/app/admin')->template('node/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Node Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/node/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if node_image has no value make it null
    if ($request->hasStage('node_image') && !$request->getStage('node_image')) {
        $request->setStage('node_image', null);
    }

    //if node_detail has no value make it null
    if ($request->hasStage('node_detail') && !$request->getStage('node_detail')) {
        $request->setStage('node_detail', null);
    }

    //if node_tags has no value make it null
    if ($request->hasStage('node_tags') && !$request->getStage('node_tags')) {
        $request->setStage('node_tags', null);
    }

    //if node_meta has no value make it null
    if ($request->hasStage('node_meta') && !$request->getStage('node_meta')) {
        $request->setStage('node_meta', null);
    }

    //if node_files has no value make it null
    if ($request->hasStage('node_files') && !$request->getStage('node_files')) {
        $request->setStage('node_files', null);
    }

    //if node_status has no value use the default value
    if ($request->hasStage('node_status') && !$request->getStage('node_status')) {
        $request->setStage('node_status', 'pending');
    }

    //if node_published has no value make it null
    if ($request->hasStage('node_published') && !$request->getStage('node_published')) {
        $request->setStage('node_published', null);
    }

    //node_type is disallowed
    $request->removeStage('node_type');

    //if node_flag has no value make it null
    if ($request->hasStage('node_flag') && !$request->getStage('node_flag')) {
        $request->setStage('node_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('node-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/node/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Node was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/node/search');
});

/**
 * Process the Node Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/node/update/:node_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if node_image has no value make it null
    if ($request->hasStage('node_image') && !$request->getStage('node_image')) {
        $request->setStage('node_image', null);
    }

    //if node_detail has no value make it null
    if ($request->hasStage('node_detail') && !$request->getStage('node_detail')) {
        $request->setStage('node_detail', null);
    }

    //if node_tags has no value make it null
    if ($request->hasStage('node_tags') && !$request->getStage('node_tags')) {
        $request->setStage('node_tags', null);
    }

    //if node_meta has no value make it null
    if ($request->hasStage('node_meta') && !$request->getStage('node_meta')) {
        $request->setStage('node_meta', null);
    }

    //if node_files has no value make it null
    if ($request->hasStage('node_files') && !$request->getStage('node_files')) {
        $request->setStage('node_files', null);
    }

    //if node_status has no value use the default value
    if ($request->hasStage('node_status') && !$request->getStage('node_status')) {
        $request->setStage('node_status', 'pending');
    }

    //if node_published has no value make it null
    if ($request->hasStage('node_published') && !$request->getStage('node_published')) {
        $request->setStage('node_published', null);
    }

    //node_type is disallowed
    $request->removeStage('node_type');

    //if node_flag has no value make it null
    if ($request->hasStage('node_flag') && !$request->getStage('node_flag')) {
        $request->setStage('node_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('node-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/node/update/' . $request->getStage('node_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Node was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/node/search');
});

/**
 * Process the Node Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/remove/:node_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('node-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Node was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/node/search');
});

/**
 * Process the Node Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/restore/:node_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('node-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Node was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/node/search');
});

/**
 * Render dynamic node create
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/node/:node_type/create', function($request, $response) {    
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // validate meta
    cradle()->trigger('meta-validate', $request, $response);

    // error?
    if($response->isError() && $response->getMessage() === 'Not Found') {
        // do nothing
        return;
    }

    // get the meta
    $meta = $response->getResults();

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);
 
    // set errors
    $data['errors'] = [];

    // error?
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // set meta data
    $data['meta'] = $meta;

    // set fields template
    $data['fields'] = (new Fields($meta['meta_fields']))
        ->setData($data['item'])
        ->setError($data['errors'])
        ->compile();

    //----------------------------//
    // 3. Render Template

    $class = sprintf(
        'page-developer-node-%s-create page-admin',
        $meta['meta_key']
    );

    $data['title'] = cradle('global')->translate(
        'Create %s',
        ucwords($meta['meta_singular'])
    );

    $body = cradle('/app/admin')->template('node/type-form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-admin-page');

/**
 * Process dynamic node create
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/node/:node_type/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // validate meta
    cradle()->trigger('meta-validate', $request, $response);

    // error?
    if($response->isError()) {
        // do nothing
        return;
    }

    // get the data
    $data = $request->getStage();

    // get the meta
    $meta = $response->getResults();

    //----------------------------//
    // 2. Prepare Data
    $fields = (new Fields($meta['meta_fields']))
        ->setData($data);

    // get errors
    $errors = $fields->getValidation();

    // if we have errors
    if(!empty($errors)) {
        $response
            ->setError(true, 'Invalid Request')
            ->set('json', 'validation', $errors);

        // trigger route
        return cradle()
            ->triggerRoute(
                'get',
                sprintf('/admin/node/%s/create', $request->getStage('node_type')),
                $request,
                $response
            );
    }

    // get field values (non-default)
    $values = $fields->getValues();

    // values to stage
    $request->setStage('node_meta', $values);

    //if node_image has no value make it null
    if ($request->hasStage('node_image') && !$request->getStage('node_image')) {
        $request->setStage('node_image', null);
    }

    //if node_detail has no value make it null
    if ($request->hasStage('node_detail') && !$request->getStage('node_detail')) {
        $request->setStage('node_detail', null);
    }

    //if node_tags has no value make it null
    if ($request->hasStage('node_tags') && !$request->getStage('node_tags')) {
        $request->setStage('node_tags', null);
    }

    //if node_meta has no value make it null
    if ($request->hasStage('node_meta') && !$request->getStage('node_meta')) {
        $request->setStage('node_meta', null);
    }

    //if node_files has no value make it null
    if ($request->hasStage('node_files') && !$request->getStage('node_files')) {
        $request->setStage('node_files', null);
    }

    //if node_status has no value use the default value
    if ($request->hasStage('node_status') && !$request->getStage('node_status')) {
        $request->setStage('node_status', 'pending');
    }

    //if node_published has no value make it null
    if ($request->hasStage('node_published') && !$request->getStage('node_published')) {
        $request->setStage('node_published', null);
    }

    //node_type is disallowed
    $request->removeStage('node_type');

    //if node_flag has no value make it null
    if ($request->hasStage('node_flag') && !$request->getStage('node_flag')) {
        $request->setStage('node_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('node-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        // trigger route
        return cradle()
            ->triggerRoute(
                'get',
                sprintf('/admin/node/%s/create', $meta['meta_key']),
                $request,
                $response
            );
    }

    //it was good
    //add a flash
    cradle('global')->flash('Node was Created', 'success');

    //redirect
    return cradle('global')->redirect(
        sprintf(
            '/admin/node/%s/search',
            $meta['meta_key']
        )
    );
});