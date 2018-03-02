<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Service as SystemService;
use Firebase\JWT\JWT;

 /**
  * System Rest Permitted Job
  *
  * @param Request $request
  * @param Response $response
  */
$cradle->on('system-rest-permitted', function($request, $response) {
    // laod config
    $config = cradle('global')->config('rest/jwt');

    // get the whitelist config
    $whitelist = $config['whitelist'];

    // get the request uri
    $uri = $request->getServer('REQUEST_URI');

    // get path only
    $path = explode('?', $uri)[0];

    // is it on our whitelist?
    if (in_array($path, $whitelist)) {
      return;
    }

    // is token set?
    if (!$request->hasStage('__token')) {
      return $response
        ->setError(true, 'Unauthorized Request');
    }

    try {
      // decode the token
      $decoded = JWT::decode(
        $request->getStage('__token'),
        $config['secret'],
        array('HS256')
      );
    } catch(\Exception $e) {
      // throw some error
      return $response->setError(true, $e->getMessage());
    }

    // cast decoded token
    $decoded = (array) $decoded;

    // set the data to stage
    $data = (array) $decoded['data'];

    // get the auth data
    $auth = SystemService::get('sql')
      ->getResource()
      ->search('auth')
      ->filterByAuthSlug($data['auth_slug'])
      ->filterByAuthPassword($data['auth_password'])
      ->filterByAuthType($data['auth_type'])
      ->filterByAuthActive($data['auth_active'])
      ->innerJoinUsing('role_auth', 'auth_id')
      ->innerJoinUsing('role', 'role_id')
      ->getRow();

    // if auth is empty
    if (!$auth) {
      return $response->setError(true, 'Invalid token');
    }

    // set auth to stage for later checking
    $request->setStage('auth_id', $auth['auth_id']);
    // set permissions to stage for later checking
    $request->setStage('role_permissions', $auth['role_permissions']);

    // auto refresh token?
    if ($decoded['autoref']) {
      // sign request
      $signRequest = \Cradle\Http\Request::i();
      // sign response
      $signResponse = \Cradle\Http\Response::i();

      // trigger data signing again
      cradle()->trigger('auth-jwt-sign', $signRequest, $signResponse);

      // do we have error?
      if ($signResponse->isError()) {
        // set token response
        $response
          ->set('json', 'token', [
            'validation' => 'Unable to sign and refresh token'
          ]);
      } else {
        // set encoded token
        $response
          ->set('json', 'token', $signResponse->getResults('token'));
      }
    }

    // set decoded token data
    $request->setStage('jwt', $decoded);
});