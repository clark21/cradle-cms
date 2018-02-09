<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Object\Service as ObjectService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('object', ObjectService::class);
