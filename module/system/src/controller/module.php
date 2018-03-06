<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Sink\Faucet\Installer as ModuleInstaller;

/**
 * Render the System Module Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/module/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
    }
    
    // modules path
    $path = cradle('global')->path('root') . '/module';

    // load versions
    $versions = cradle('global')->config('version');

    // scan diretory
    $modules = scandir($path);

    // configurable modules
    $configurable = [];

    // iterate on each modules
    foreach($modules as $module) {
        if ($module == '.' || $module == '..') {
            continue;
        }

        // get module init path
        $init = sprintf('/%s/%s/.cradle.php', $path, $module);

        // init path exists?
        if (!file_exists($init)) {
            continue;
        }

        // module detail
        $detail = [
            'label' => ucwords($module),
            'name' => $module,
            'description' => sprintf('%s Module', ucwords($module))
        ];

        // load module
        $init = cradle(sprintf('/module/%s', $module));

        // reflect the object
        $reflection = new ReflectionClass($init);

        // get methods
        $methods = $reflection->getProperty('methods');

        // set accessible
        $methods->setAccessible(true);

        // get methods
        $methods = $methods->getValue($init);

        // has actions?
        $actions = false;

        // installable?
        if (isset($methods['install'])) {
            $detail['install'] = true;
            $actions = true;
        }

        // uninstallable?
        if (isset($methods['uninstall'])) {
            $detail['uninstall'] = true;
            $actions = true;
        }

        // set version
        $detail['version'] = null;

        if (isset($versions[$module])) {
            $detail['version'] = $versions[$module];
        }

        // do we have version?
        if (!is_null($detail['version'])) {
            // get next version
            $nextVersion = ModuleInstaller::getNextVersion($module);

            // install path
            $script = sprintf(
                '%s/%s/install/%s',
                $path,
                $module,
                $nextVersion
            );

            // set module name
            $request->setStage('module', $module);

            // get module versions
            cradle()->trigger('system-module-versions', $request, $response);

            // get versions
            $history = $response->getResults();

            // get latest version
            $latest = array_pop($history);

            // version compare
            if (version_compare($detail['version'], $latest, '<')) {
                // set latest update
                $detail['update'] = $latest;
            }
        }

        // set module actions
        $detail['actions'] = $actions;

        // set module
        $configurable[$module] = $detail;
    }

    $data['rows'] = $configurable;
    $data['total'] = count($configurable);

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-module-search page-admin';
    $data['title'] = cradle('global')->translate('System Modules');
    $body = cradle('/module/system')->template('module/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Process the System Module Install Page
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/module/install/:name', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    
    // modules path
    $path = cradle('global')->path('root') . '/module';

    try {
        // load module init file
        $module = cradle(sprintf('/module/%s', $request->getStage('name')));
    } catch(Cradle\Resolver\ResolverException $e) {
        // set flash
        cradle('global')->flash('Module does not exists', 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // reflect the object
    $reflection = new ReflectionClass($module);

    // get methods
    $methods = $reflection->getProperty('methods');

    // set accessible
    $methods->setAccessible(true);

    // get methods
    $methods = $methods->getValue($module);

    // has actions?
    $actions = false;

    // installable?
    if (!isset($methods['install'])) {
        // set flash
        cradle('global')->flash('Can\'t install the module', 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // trigger module installer
    $module->install($request, $response);

    // is error?
    if ($response->isError()) {
        // set flash
        cradle('global')->flash($response->getMessage(), 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // set flash
    cradle('global')->flash($response->getMessage(), 'success');

    // redirect
    return cradle('global')->redirect('/admin/system/module/search');
});

/**
 * Process the System Module Uninstall Page
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/module/uninstall/:name', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    
    // modules path
    $path = cradle('global')->path('root') . '/module';

    try {
        // load module init file
        $module = cradle(sprintf('/module/%s', $request->getStage('name')));
    } catch(Cradle\Resolver\ResolverException $e) {
        // set flash
        cradle('global')->flash('Module does not exists', 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // reflect the object
    $reflection = new ReflectionClass($module);

    // get methods
    $methods = $reflection->getProperty('methods');

    // set accessible
    $methods->setAccessible(true);

    // get methods
    $methods = $methods->getValue($module);

    // has actions?
    $actions = false;

    // uninstallable?
    if (!isset($methods['uninstall'])) {
        // set flash
        cradle('global')->flash('Can\'t uninstall the module', 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // trigger module uninstaller
    $module->uninstall($request, $response);

    // is error?
    if ($response->isError()) {
        // set flash
        cradle('global')->flash($response->getMessage(), 'error');

        // redirect
        return cradle('global')->redirect('/admin/system/module/search');
    }

    // set flash
    cradle('global')->flash($response->getMessage(), 'success');

    // redirect
    return cradle('global')->redirect('/admin/system/module/search');
});