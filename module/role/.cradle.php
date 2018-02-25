<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Role\Service as RoleService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('role', RoleService::class);
