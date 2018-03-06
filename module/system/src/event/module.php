<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Sink\Faucet\Installer as ModuleInstaller;

/**
 * System Module Install Job
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-module-install', function ($request, $response) {
    // if module name is not set
    if (!$request->hasStage('module')) {
        // set error
        return $response->setError(true, 'Module not found');
    }

    // get the module name
    $module = $request->getStage('module');

    // get the install scripts folder
    $scripts = sprintf(
        '%s/%s/%s/install',
        cradle('global')->path('root'),
        'module',
        $module
    );

    // install folder exists?
    if (!file_exists($scripts)) {
        // set error
        return $response->setError(true, 'Can\'t find module install folder');
    }

    try {
        // execute installer
        ModuleInstaller::install($module);
    } catch(\Exception $e) {
        // set error
        return $response->setError(true, 'Unable to install module');
    }
});

/**
 * System Module Versions Job
 * 
 * Returns the valid list of module
 * version history in descending order.
 * This is based at the getNextVersion 
 * function of the Faucet\Installer class.
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-module-versions', function ($request, $response) {
    // get module
    $module = $request->getStage('module');

    //module root
    $root = cradle('global')->path('module');

    $install = $root . '/' . $module . '/install';

    //if there is no install
    if(!is_dir($install)) {
        return '0.0.1';
    }

    //collect and organize all the versions
    $versions = [];
    $files = scandir($install, 0);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || is_dir($install . '/' . $file)) {
            continue;
        }

        //get extension
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if ($extension !== 'php'
            && $extension !== 'sh'
            && $extension !== 'sql'
        ) {
            continue;
        }

        //get base as version
        $version = pathinfo($file, PATHINFO_FILENAME);

        //validate version
        if (!(version_compare($version, '0.0.1', '>=') >= 0)) {
            continue;
        }

        $versions[] = $version;
    }

    // set results
    $response->setResults($versions);
});