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
 * Render the System Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('render', 'false');

    //now let the original search take over
    cradle()->triggerRoute(
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
 * Render the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema/create', function ($request, $response) {
    //----------------------------//
    // get json data only
    $request->setStage('render', 'false');

    //now let the original create take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/create',
            $request->getStage('schema')
        ),
        $request,
        $response
    );
});

/**
 * Render the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/system/object/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('render', 'false');

    //now let the original update take over
    cradle()->triggerRoute(
        'get',
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
 * Process the System Object Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('redirect_uri', 'false');

    //now let the original post search take over
    cradle()->triggerRoute(
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
 * Process the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/system/object/:schema/create', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('redirect_uri', 'false');

    //now let the original post create take over
    cradle()->triggerRoute(
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
 * Process the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/system/object/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('redirect_uri', 'false');

    //now let the original post update take over
    cradle()->triggerRoute(
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
$cradle->get('/system/object/:schema/remove/:id', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('redirect_uri', 'false');

    //now let the original remove take over
    cradle()->triggerRoute(
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
$cradle->get('/system/object/:schema/restore/:id', function ($request, $response) {
    //----------------------------//
    // get json response data only
    $request->setStage('redirect_uri', 'false');

    //now let the original restore take over
    cradle()->triggerRoute(
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
$cradle->post('/system/object/:schema/import', function ($request, $response) {
    //----------------------------//
    //trigger original import route
    cradle()->triggerRoute(
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
$cradle->get('/system/object/:schema/export/:type', function ($request, $response) {
    //----------------------------//
    //trigger original export route
    cradle()->triggerRoute(
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
