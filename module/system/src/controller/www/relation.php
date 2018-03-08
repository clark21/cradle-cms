<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Utility\File;
use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema1/:id/search/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('render', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/search/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
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
$cradle->get('/system/object/:schema1/:id/create/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('render', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/create/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
        ),
        $request,
        $response
    );
});

/**
 * Render the System Object Link Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema1/:id/link/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    // $request->setStage('render', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/link/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
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
$cradle->post('/system/object/:schema1/:id/search/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('redirect_uri', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/%s/search/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
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
$cradle->post('/system/object/:schema1/:id/create/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('redirect_uri', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/%s/create/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
        ),
        $request,
        $response
    );
});

/**
 * Link object to object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/system/object/:schema1/:id/link/:schema2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('redirect_uri', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/%s/link/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
        ),
        $request,
        $response
    );
});

/**
 * Link object from object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema1/:id1/link/:schema2/:id2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('redirect_uri', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/link/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('id1'),
            $request->getStage('schema2'),
            $request->getStage('id2')
        ),
        $request,
        $response
    );
});

/**
 * Unlink object from object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema1/:id1/unlink/:schema2/:id2', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('redirect_uri', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/unlink/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('id1'),
            $request->getStage('schema2'),
            $request->getStage('id2')
        ),
        $request,
        $response
    );
});

/**
 * Process Object Exporting Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema1/:id/export/:schema2/:type', function ($request, $response) {
    //----------------------------//
    $route = sprintf(
            '/admin/system/object/%s/%s/export/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2'),
            $request->getStage('type')
        );

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/export/%s/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2'),
            $request->getStage('type')
        ),
        $request,
        $response
    );
});

/**
 * Process Ajax Object Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/system/object/:schema/:id/import/:schema2', function ($request, $response) {
    //----------------------------//
    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/%s/import/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
        ),
        $request,
        $response
    );
});
