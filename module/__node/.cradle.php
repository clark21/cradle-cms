<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Node\Service as NodeService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('node', NodeService::class);
