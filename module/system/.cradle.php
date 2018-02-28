<?php //-->
include_once __DIR__ . '/src/event/auth.php';
include_once __DIR__ . '/src/event/schema.php';
include_once __DIR__ . '/src/event/object.php';
include_once __DIR__ . '/src/event/rest.php';

include_once __DIR__ . '/src/controller/auth.php';
include_once __DIR__ . '/src/controller/schema.php';
include_once __DIR__ . '/src/controller/object.php';
include_once __DIR__ . '/src/controller/relation.php';
include_once __DIR__ . '/src/controller/rest.php';

use Cradle\Module\System\Service as SystemService;
use Cradle\Module\System\Object\Service as ObjectService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('object', ObjectService::class);
ServiceFactory::register('system', SystemService::class);

$cradle->package('/module/system')->addMethod('template', function (
    $path,
    array $data = array(),
    $partials = array()
) {
    // get the root directory
    $root = __DIR__ . '/src/template/';

    //render
    $handlebars = cradle('global')->handlebars();

    // check for partials
    if (!is_array($partials)) {
        $partials = array($partials);
    }

    foreach ($partials as $partial) {
        //Sample: product_comment => product/_comment
        //Sample: flash => _flash
        $file = str_replace('_', '/_', $partial) . '.html';

        if (strpos($file, '_') === false) {
            $file = '_' . $file;
        }

        // register the partial
        $handlebars->registerPartial($partial, file_get_contents($root . $file));
    }

    // set the main template
    $template = $handlebars->compile(file_get_contents($root . $path . '.html'));
    return $template($data);
});
