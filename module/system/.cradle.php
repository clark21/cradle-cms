<?php //-->
include_once __DIR__ . '/src/event/auth.php';
include_once __DIR__ . '/src/event/schema.php';
include_once __DIR__ . '/src/event/module.php';
include_once __DIR__ . '/src/event/menu.php';
include_once __DIR__ . '/src/event/object.php';
include_once __DIR__ . '/src/event/rest.php';

include_once __DIR__ . '/src/controller/schema.php';
include_once __DIR__ . '/src/controller/module.php';
include_once __DIR__ . '/src/controller/object.php';
include_once __DIR__ . '/src/controller/relation.php';
include_once __DIR__ . '/src/controller/rest.php';
include_once __DIR__ . '/src/controller/menu.php';
include_once __DIR__ . '/src/controller/static.php';

include_once __DIR__ . '/src/controller/www/object.php';
include_once __DIR__ . '/src/controller/www/relation.php';

use Cradle\Module\System\Service as SystemService;
use Cradle\Module\System\Object\Service as ObjectService;
use Cradle\Module\System\Utility\ServiceFactory;

ServiceFactory::register('object', ObjectService::class);
ServiceFactory::register('system', SystemService::class);

$cradle->preprocess(function($request, $response) {
    $extensions = $this->package('global')->path('public') . '/json/extensions.json';
    $json = file_get_contents($extensions);
    Cradle\Module\System\Utility\File::$extensions = json_decode($json, true);

    //add helpers
    $handlebars = cradle('global')->handlebars();
    include __DIR__ . '/src/helpers.php';

    $this->package('/module/system')
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
