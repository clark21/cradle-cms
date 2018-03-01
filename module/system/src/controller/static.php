<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

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
