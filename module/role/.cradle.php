<?php //-->
include_once __DIR__ . '/src/events.php';
include_once __DIR__ . '/src/permission/events.php';

use Cradle\Module\Role\Service as RoleService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('role', RoleService::class);

$cradle->package('/module/role')->addMethod('hasPermissions', function($request, $response) {
    // get default auth id
    $authId = $request->getSession('me', 'auth_id');

    // if auth id override
    if ($request->hasStage('auth_id')) {
        // get auth id
        $authId = $request->getStage('auth_id');
    }

    // get default role permissions
    $permissions = $request->getSession('me', 'role_permissions');

    // if role permission override
    if ($request->hasStage('role_permissions')) {
        // get role permissions
        $permissions = $request->getStage('role_permissions');
    }

    // default redirect
    $redirect = '/login';

    // if redirect is set
    if ($request->hasStage('redirect')) {
        // set redirect
        $redirect = $request->getStage('redirect');
    }

    // redirect to login
    if(!$authId) {
        // if no redirect
        if($request->getStage('redirect') === 'false') {
            // set error
            $response->setError(true, 'Invalid Permissions');

            return false;
        } 

        // default redirect
        return cradle('global')->redirect($redirect);
    }    

    // allow auth id 1
    if($authId == 1) {
        // skip permission check
        return true;
    }

    // initialize router
    $router = new \Cradle\Http\Router;

    // iterate on each permissions
    foreach($permissions as $permission) {
        // validate route
        $router->route(
            $permission['method'], 
            $permission['path'], 
            function($request, $response) {
            //if good, let's end checking
            return false;
        });
    }

    // process router
    $router->process($request, $response);

    //let's interpret the results
    if(!$router->getEventHandler()->getMeta()) {
        //the role passes
        return true;
    }

    // default redirect
    $redirect = '/';    

    // if redirect is set
    if ($request->hasStage('redirect')) {
        // set redirect
        $redirect = $request->getStage('redirect');
    }

    // if no redirect
    if ($request->getStage('redirect') === 'false') {
        // set error
        $response->setError(true, 'Invalid Permissions');

        return false;
    }
    
    // set flash
    cradle('global')->flash('Request not Permitted', 'danger');

    // redirect to default page
    cradle('global')->redirect($redirect);
});
