<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Firebase\JWT\JWT;

/**
 * Auth JWT Sign Job
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-jwt-sign', function ($request, $response) {
    // load jwt config
    $config = cradle('global')->config('jwt');

    // get the issuer
    $issuer = $config['issuer'];

    // if custom issuer is set
    if ($request->hasStage('issuer')) {
        // get custom issuer
        $issuer = $request->getStage('issuer');
    }

    // get the audience
    $audience = $config['audience'];

    // if custom audience is set
    if ($request->hasStage('audience')) {
        // get custom audience
        $audience = $request->getStage('audience');
    }

    // auto refresh the token expiration
    // on every request, if this is set
    // every successful request will return
    // a newly signed token, whitelisted url's
    // will skip token refresh process
    $autoRefresh = false;

    // if auto refresh
    if ($request->hasStage('autoref')) {
        // get auto refresh
        $autoRefresh = true;
    }

    // get current timestamp
    $time = time();

    // default expiration
    $expiration = $time + (60 * 60);

    // if expiration is set
    if ($request->hasStage('expiration')) {
        // get expiration
        $expiration = $request->getStage('expiration');
    }

    // get the data
    $data = $request->getStage('data');
    
    // create the token
    $token = [
        'iss' => $issuer,
        'aud' => $audience,
        'iat' => $time,
        'nbf' => $time + 5,
        'exp' => $expiration,
        'data' => $data,
        'autoref' => $autoRefresh
    ];

    try {
        // encode the data
        $encoded = JWT::encode($token, $config['secret']);
    } catch(\Exception $e) {        
        // throw error message
        return $response->setError(true, $e->getMessage());
    }

    // return the token
    return $response->setError(false)->setResults(['token' => $encoded]);
});