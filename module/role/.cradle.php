<?php //-->
include_once __DIR__ . '/src/events.php';
include_once __DIR__ . '/src/permission/events.php';

use Cradle\Module\Role\Service as RoleService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('role', RoleService::class);

$cradle->package('/module/role')->addMethod('hasPermissions', function($request) {
    //get permissions
        $permissions = $request->getSession('me');

        /* Example Format: */
        $permissions = [
            [
                'label' => 'Access to Users',
                'method' => 'get',
                'path' => '/admin/user/*'
            ],
            [
                'label' => 'Access to Posts',
                'method' => 'get',
                'path' => '/admin/role/update/*'
            ]
        ];

        $router = new \Cradle\Http\Router;

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
