<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the System Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data
    if ($response->isError()) {
        return;
    }

    // disable session
    $request->setStage('session', 'false');
    // render raw data
    $request->setStage('render', 'false');
    // disable redirect
    $request->setStage('redirect', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/search',
            $request->getStage('schema')
        ),
        $request,
        $response
    );
});

/**
 * Render the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/system/object/:schema1/search/:schema2/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // render raw data
    $request->setStage('render', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/search/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('schema2'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    // rest route
    $route = sprintf(
        '/rest/system/object/%s/search',
        $request->getStage('schema')
    );

    // set route
    $request->setStage('route', $route);

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/search',
            $request->getStage('schema')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema1/search/:schema2/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    // rest route
    $route = sprintf(
        '/rest/system/object/%s/search/%s/%s',
        $request->getStage('schema1'),
        $request->getStage('schema2'),
        $request->getStage('id')
    );

    // set route
    $request->setStage('route', $route);

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/search/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('schema2'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    // rest route
    $route = sprintf(
        '/rest/system/object/%s/create',
        $request->getStage('schema')
    );

    // set route
    $request->setStage('route', $route);

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/create',
            $request->getStage('schema')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Create Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema1/create/:schema2/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    // rest route
    $route = sprintf(
        '/rest/system/object/%s/create/%s/%s',
        $request->getStage('schema1'),
        $request->getStage('schema2'),
        $request->getStage('id')
    );

    // set route
    $request->setStage('route', $route);

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/create/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('schema2'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    // rest route
    $route = sprintf(
        '/rest/system/object/%s/update/%s',
        $request->getStage('schema'),
        $request->getStage('id')
    );

    // set route
    $request->setStage('route', $route);

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/update/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/system/object/:schema/remove/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/remove/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/system/object/:schema/restore/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/restore/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        ),
        $request,
        $response
    );
});

/**
 * Process Object Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/object/:schema/import', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');
    // don't redirect
    $request->setStage('redirect_uri', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/import',
            $request->getStage('schema')
        ),
        $request,
        $response
    );
});

/**
 * Process Object Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/system/object/:schema/export/:type', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle()->trigger('system-rest-permitted', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    // disable session
    $request->setStage('session', 'false');

    //----------------------------//
    // 3. Render Request
    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/export/%s',
            $request->getStage('schema'),
            $request->getStage('type')
        ),
        $request,
        $response
    );
});
