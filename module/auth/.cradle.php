<?php //-->
include_once __DIR__ . '/src/controller/admin.php';
include_once __DIR__ . '/src/controller/www.php';
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Auth\Service as AuthService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('auth', AuthService::class);

$cradle->preprocess(function($request, $response) {
    $this->package('/module/auth')
    /**
     * Add Template Builder
     *
     */
    ->addMethod('template', function ($file, array $data = [], $partials = []) {
        // get the root directory
        $root = __DIR__ . '/src/template/';

        // check for partials
        if (!is_array($partials)) {
            $partials = [$partials];
        }

        $paths = [];

        foreach ($partials as $partial) {
            //Sample: product_comment => product/_comment
            //Sample: flash => _flash
            $path = str_replace('_', '/', $partial);
            $last = strrpos($path, '/');

            if($last !== false) {
                $path = substr_replace($path, '/_', $last, 1);
            }

            $path = $path . '.html';

            if (strpos($path, '_') === false) {
                $path = '_' . $path;
            }

            $paths[$partial] = $root . $path;
        }

        $file = $root . $file . '.html';

        //render
        return cradle('global')->template($file, $data, $paths);
    });
});
