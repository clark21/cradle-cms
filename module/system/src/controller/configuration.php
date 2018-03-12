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

    $data['type'] = $request->getStage('type');

    //
    // We need to normalize the configuration
    // so that we can do recursive templating
    // on the front end.
    //
    function normalize($configuration) {
        // normalized array
        $normalized = [];

        // iterate on each configuration
        foreach($configuration as $key => $value) {
            // if config is an array
            if (is_array($value)) {
                // loop through
                $normalized[] = [
                    'key' => $key,
                    'value' => null,
                    'children' => normalize($value)
                ];

                continue;
            }

            // set config data
            $normalized[] = [
                'key' => $key,
                'value' => $value,
                'children' => null
            ];
        }

        return $normalized;
    }

    // normalize config
    $data['item'] = normalize($data['item']);

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