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
$cradle->get('/admin/history/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 25);
    }

    if (!$request->hasStage('order')) {
        $request->setStage('order', 'history_created', 'DESC');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'history_activity',
            'history_created'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
        'history_active',
            'history_remote_address',
            'history_activity'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
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
    $body = cradle('/module/history')->template('search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Show/Read History Logs
 * based on the given data.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/history/:action/logs', function ($request, $response) {
    if (!$request->hasStage('action')) {
        //Set JSON Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'Invalid History Action',
        ]));
    }

    $data = $request->getStage();

    switch (strtolower($data['action'])) {
        case 'get':
            $request->setStage('filter', 'history_flag', 0);
            $request->setStage('order', 'history_created', 'DESC');

            cradle()->trigger('history-search', $request, $response);

            $results = $response->getResults();

            if ($response->isError()) {
                //Set JSON Content
                return $response->setContent(json_encode([
                    'error'      => true,
                    'message'    => $response->getMessage(),
                    'validation' => $response->getValidation()
                ]));
            }

            //process data
            foreach ($results['rows'] as $key => $value) {
                $timestamp = strtotime($value['history_created']);

                $strTime = array("second", "minute", "hour", "day", "month", "year");
                $length = array("60","60","24","30","12","10");

                $currentTime = time();
                if ($currentTime >= $timestamp) {
                    $diff     = time()- $timestamp;
                    for ($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                        $diff = $diff / $length[$i];
                    }

                    $diff = round($diff);
                    $results['rows'][$key]['ago'] = $diff . " " . $strTime[$i] . "(s) ago ";
                }
            }

            //set message
            $data['message'] = 'New history logs loaded';

            break;
        case 'read':
            //mark all unread logs to read
            cradle()->trigger('history-mark-as-read', $request, $response);

            $results = $response->getResults();

            if ($response->isError()) {
                //Set JSON Content
                return $response->setContent(json_encode([
                    'error'      => true,
                    'message'    => $response->getMessage(),
                ]));
            }

            //set message
            $data['message'] = 'All new history log marked as read';

            break;
        default:
            if ($response->isError()) {
                //Set JSON Content
                return $response->setContent(json_encode([
                    'error'      => true,
                    'message'    => 'Invalid History Action',
                ]));
            }
            break;
    }

    //Set JSON Content
    return $response->setContent(json_encode([
        'error' => false,
        'message' => $data['message'],
        'results' => $results
    ]));
});
