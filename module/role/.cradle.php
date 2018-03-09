<?php //-->
include_once __DIR__ . '/src/event/auth.php';
include_once __DIR__ . '/src/event/role.php';

include_once __DIR__ . '/src/controller/role.php';

use Cradle\Module\Role\Service as RoleService;
use Cradle\Module\System\Utility\ServiceFactory;

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
    $redirect = '/';

    // if redirect is set
    if ($request->hasStage('redirect_uri')) {
        // set redirect
        $redirect = $request->getStage('redirect_uri');
    }

    // redirect to login
    if(!$authId) {
        // if no redirect
        if($request->getStage('redirect_uri') === 'false') {
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
    $router->process($request, $response, 1);

    //let's interpret the results
    if(!$router->getEventHandler()->getMeta()) {
        //the role passes
        return true;
    }

    // if no redirect
    if ($request->getStage('redirect') === 'false') {
        // set error
        $response->setError(true, 'Invalid Permissions');

        return false;
    }

    // set flash
    cradle('global')->flash('Request not Permitted', 'error');

    // redirect to default page
    cradle('global')->redirect($redirect);
});


$cradle->package('/module/role')->addMethod('template', function (
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

$cradle->preprocess(function($request, $response) {
    $this->package('/module/role')

    /**
     * Installer
     */
    ->addMethod('install', function ($request, $response) {
        // set module
        $request->setStage('module', 'role');

        // install schema versions
        cradle()->trigger('system-module-install', $request, $response);

        // do module specific actions
        $response->setError(false, 'Role Module Installed');
    })

    /**
     * Uninstaller
     */
    ->addMethod('uninstall', function ($request, $response) {
        // set module
        $request->setStage('module', 'role');

        // install schema versions
        cradle()->trigger('system-module-uninstall', $request, $response);

        // do module specific actions
        $response->setError(false, 'Role Module Uninstalled');
    });
});
