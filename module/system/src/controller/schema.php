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

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
    }

    //form the data
    $data = array_merge(
        //we need to case for things like
        //filter and sort on the template
        $request->getStage(),
        //this is from the search event
        $response->getResults()
    );

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

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

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

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
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

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-schema-create page-admin';

    //determine the action
    $data['action'] = 'create';

    //determine the title
    $data['title'] = cradle('global')->translate('Create System Schema');

    //add custom page helpers
    cradle('global')
        ->handlebars()
        ->registerHelper('is_array', function($value, $option) {
            if(is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    //render the body
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

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

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
    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //if no item
    if(empty($data['item'])) {
        //get the original schema row
        cradle()->trigger('system-schema-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //redirect
            $redirect = '/admin/system/schema/search';

            //this is for flexibility
            if($request->hasStage('redirect_uri')) {
                $redirect = $request->getStage('redirect_uri');
            }

            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect($redirect);
        }

        $data['item'] = $response->getResults();
    }

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-schema-update page-admin';

    //determine the action
    $data['action'] = 'update';

    //determine the title
    $data['title'] = cradle('global')->translate('Updating System Schema');

    //add custom page helpers
    cradle('global')
        ->handlebars()
        ->registerHelper('is_array', function($value, $option) {
            if(is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    //render the body
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

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

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
    //if the event returned an error
    if($response->isError()) {
        //determine route
        $route = '/admin/system/schema/create';

        //this is for flexibility
        if($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
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
    cradle('global')->redirect($redirect);
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
    //if the event returned an error
    if($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/schema/update/%s',
            $request->getStage('name')
        );

        //this is for flexibility
        if($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
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

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    //it was good
    //add a flash
    cradle('global')->flash('System Schema was Updated', 'success');

    //redirect
    cradle('global')->redirect($redirect);
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
    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

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

    cradle('global')->redirect($redirect);
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
    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

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

    cradle('global')->redirect($redirect);
});
