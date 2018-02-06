<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Auth\Service as AuthService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('auth', AuthService::class);
