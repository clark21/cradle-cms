<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\User\Service as UserService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('user', UserService::class);
