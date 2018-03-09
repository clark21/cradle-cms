<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Configuration Page
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/configuration', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    
    // default type
    if (!$request->hasStage('type')) {
        $request->setStage('type', 'none');
    }

    // valid types
    $valid = ['none', 'general', 'deploy', 'rest-jwt', 'service', 'test'];

    // valid type ?
    if (!in_array($request->getStage('type'), $valid)) {
        cradle('global')->flash('Please select a valid configuration', 'error');
        return cradle('global')->redirect('/admin/system/configuration');
    }

    // get the file type
    $file = $request->getStage('type');

    // switch between config to load
    switch($file) {
        case 'general' :
            $data['item'] = cradle('global')->config('settings');
            break;
        
        case 'deploy' :
            $data['item'] = cradle('global')->config('deploy');
            break;

        case 'rest-jwt' :
            $data['item'] = cradle('global')->config('rest/jwt');
            break;

        case 'service' :
            $data['item'] = cradle('global')->config('services');
            break;
        
        case 'test' :
            $data['item'] = cradle('global')->config('test');
            break;

        default :
            $data['item'] = [];
    }

    $data = array_merge($request->getStage(), $data['item']);

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-configuration-search page-admin';
    $data['title'] = cradle('global')->translate('System Configuration');
    $body = cradle('/module/system')->template('configuration', $data, [
        'configuration_item',
        'configuration_input'
    ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});