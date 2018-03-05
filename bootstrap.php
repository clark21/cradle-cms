<?php //-->
require_once 'vendor/autoload.php';

//use the cradle function
Cradle\Framework\Decorator::DECORATE;

return cradle()
    //add bootstrap here
    ->preprocess(include('bootstrap/paths.php'))
    ->preprocess(include('bootstrap/debug.php'))
    ->preprocess(include('bootstrap/errors.php'))
    ->preprocess(include('bootstrap/services.php'))
    ->preprocess(include('bootstrap/timezone.php'))
    ->preprocess(include('bootstrap/session.php'))
    ->preprocess(include('bootstrap/i18n.php'))
    ->preprocess(include('bootstrap/handlebars.php'))
    ->preprocess(include('bootstrap/roles.php'))

    //add packages here
    ->register('cblanquera/cradle-queue')
    ->register('cblanquera/cradle-csrf')
    ->register('cblanquera/cradle-captcha')
    ->register('cradlephp/sink-faucet')

    ->register('/module/auth')
    ->register('/module/history')
    ->register('/module/role')
    ->register('/module/system')
    ->register('/module/utility')
    ->register('/module/article');
