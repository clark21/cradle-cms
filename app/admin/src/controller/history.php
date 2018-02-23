<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the History Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/history/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage('range')) {
        $request->setStage('range', 25);
    }

    if(!$request->hasStage('order')) {
        $request->setStage('order', 'history_created', 'DESC');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        $sortable = [
            'history_activity',
            'history_created'
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
        'history_active',
            'history_remote_address',
            'history_activity'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('history-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-history-search page-admin';
    $data['title'] = cradle('global')->translate('History');
    $body = cradle('/app/admin')->template('history/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');
