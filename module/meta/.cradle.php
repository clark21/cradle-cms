<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Meta\Service as MetaService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('meta', MetaService::class);
