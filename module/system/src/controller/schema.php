<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/schema/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage()) {
        $request->setStage('filter', 'active', 1);
    }

    //trigger job
    cradle()->trigger('system-schema-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-schema-search page-admin';
    $data['title'] = cradle('global')->translate('System Schemas');
    $body = cradle('/module/system')->template('search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/schema/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //for ?copy=1 functionality
    if (empty($data['item']) && $request->hasStage('copy')) {
        $request->setStage('schema', $request->getStage('copy'));
        cradle()->trigger('system-schema-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect('/admin/system/schema/search');
        }

        $data['item'] = $response->getResults();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-schema-create page-admin';
    $data['action'] = 'create';
    $data['title'] = cradle('global')->translate('Create System Schema');

    cradle('global')
        ->handlebars()
        ->registerHelper('is_array', function($value, $option) {
            if(is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    $body = cradle('/module/system')->template(
        'form',
        $data,
        [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'type-options',
            'format-options',
            'validation-options',
            'icon-options'
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
 * Render the Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/schema/update/:name', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('system-schema-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect('/admin/system/schema/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-schema-update page-admin';
    $data['action'] = 'update';
    $data['title'] = cradle('global')->translate('Updating System Schema');

    cradle('global')
        ->handlebars()
        ->registerHelper('is_array', function($value, $option) {
            if(is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    $body = cradle('/module/system')->template(
        'form',
        $data,
        [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'type-options',
            'format-options',
            'validation-options',
            'icon-options'
        ]
    );

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/schema/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if detail has no value make it null
    if ($request->hasStage('detail') && !$request->getStage('detail')) {
        $request->setStage('detail', null);
    }

    //if fields has no value make it an array
    if ($request->hasStage('fields') && !$request->getStage('fields')) {
        $request->setStage('fields', []);
    }

    //if validation has no value make it an array
    if ($request->hasStage('validation') && !$request->getStage('validation')) {
        $request->setStage('validation', []);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-schema-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/admin/system/schema/create',
            $request,
            $response
        );
    }

    //record logs
    cradle()->log(
        sprintf(
            '%s schema created',
            ucfirst($request->getStage('name'))
        ),
        $request,
        $response
    );

    //it was good
    //add a flash
    cradle('global')->flash('System Schema was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/system/schema/search');
});

/**
 * Process the Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/schema/update/:name', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if detail has no value make it null
    if ($request->hasStage('detail') && !$request->getStage('detail')) {
        $request->setStage('detail', null);
    }

    //if fields has no value make it an array
    if ($request->hasStage('fields') && !$request->getStage('fields')) {
        $request->setStage('fields', []);
    }

    //if validation has no value make it an array
    foreach($request->getStage('fields') as $i => $field) {
        if ($request->hasStage('fields', $i, 'validation')
            && !$request->getStage('fields', $i, 'validation')
        )
        {
            $request->setStage('fields', $i, 'validation', []);
        }
    }

    //if relations has no value make it an array
    if ($request->hasStage('relations') && !$request->getStage('relations')) {
        $request->setStage('relations', []);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-schema-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = sprintf(
            '/admin/system/schema/update/%s',
            $request->getStage('name')
        );
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log(
        sprintf(
            '%s schema updated',
            ucfirst($request->getStage('name'))
        ),
        $request,
        $response
    );

    //it was good
    //add a flash
    cradle('global')->flash('System Schema was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/system/schema/search');
});

/**
 * Process the Object Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/schema/remove/:name', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-schema-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate('System Schema was Removed');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s schema removed',
                ucfirst($request->getStage('name'))
            ),
            $request,
            $response
        );
    }


    cradle('global')->redirect('/admin/system/schema/search');
});

/**
 * Process the Object Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/schema/restore/:name', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-schema-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate('System Schema was Restored');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s schema restored',
                ucfirst($request->getStage('name'))
            ),
            $request,
            $response
        );
    }


    cradle('global')->redirect('/admin/system/schema/search');
});

/**
 * Render Template Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/template/:action', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    $action = $request->getStage('action');

    //----------------------------//
    // 2. Render Template
    $class = sprintf('page-admin-system-template-%s page-admin', $action);
    $data['title'] = cradle('global')->translate('System Template ' . ucfirst($action));
    $body = cradle('/module/system')->template('template/' . $action, $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render Docs Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/docs', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Render Template
    $class = 'page-admin-system-docs page-admin';
    $data['title'] = cradle('global')->translate('Documentation');
    $body = cradle('/module/system')->template('docs', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');