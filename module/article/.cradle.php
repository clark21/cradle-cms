<?php //-->

use Cradle\Module\Article\Service as ArticleService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('article', ArticleService::class);

$cradle->preprocess(function($request, $response) {
    $this->package('/module/article')
    
    /**
     * Installer
     */
    ->addMethod('install', function ($request, $response) {
        // call system install

        // call system install placeholder

        // move schema files to config/admin/schema/:module_name

        // do module specific actions
        $response->setError(false, 'Article Module Installed');
    })

    /**
     * Uninstaller
     */
    ->addMethod('uninstall', function ($request, $response) {
        // do module specific actions
        $response->setError(false, 'Article Module Uninstalled');
    });
});
