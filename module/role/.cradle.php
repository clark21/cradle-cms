<?php //-->
include_once __DIR__ . '/src/events.php';
include_once __DIR__ . '/src/permission/events.php';

use Cradle\Module\Role\Service as RoleService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('role', RoleService::class);

$cradle->package('/module/role')->addMethod('hasPermissions', function($authId, $permissions = []) {
    // redirect to login
    if(!$authId) {
        return cradle('global')->redirect('/login');
    }

    // allow auth id 1
    if($authId == 1) {
        return true;
    }

    $router = new \Cradle\Http\Router;

    $request = cradle()->getRequest();
    $response = cradle()->getResponse();

    foreach($permissions as $permission) {
        $router->route($permission['method'], $permission['path'], function($request, $response) {
            //if good, let's end checking
            return false;
        });
    }

    $router->process($request, $response);

    //let's interpret the results
    if(!$router->getEventHandler()->getMeta()) {
        //the role passes
        return true;
    }

    //if we are here, then let's throw an error
    return false;
});
