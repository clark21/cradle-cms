<?php //-->

use Cradle\Module\Article\Service as ArticleService;
use Cradle\Module\System\Utility\ServiceFactory;

ServiceFactory::register('article', ArticleService::class);

$cradle->preprocess(function($request, $response) {
    $this->package('/module/article')
    
    /**
     * Installer
     */
    ->addMethod('install', function ($request, $response) {
        // set module
        $request->setStage('module', 'article');

        // install schema versions
        cradle()->trigger('system-module-install', $request, $response);

        // do module specific actions
        $response->setError(false, 'Article module has been successfully installed/updated');
    })

    /**
     * Uninstaller
     */
    ->addMethod('uninstall', function ($request, $response) {
        // set module
        $request->setStage('module', 'article');

        // install schema versions
        cradle()->trigger('system-module-uninstall', $request, $response);

        // do module specific actions
        $response->setError(false, 'Article module has been successfully uninstalled');
    });
});
