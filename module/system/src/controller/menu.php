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
$cradle->get('/admin/system/menu', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    cradle()->trigger('system-schema-search', $request, $response);

    $data = [
        'schemas' => $response->getResults('rows')
    ];

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-menu page-admin';
    $data['title'] = cradle('global')->translate('Menu Builder');
    $body = cradle('/module/system')->template('menu', $data, [
        'menu_item',
        'menu_input'
    ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    cradle()->trigger('render-admin-page', $request, $response);
});
