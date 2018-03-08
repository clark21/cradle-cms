<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Role\Service as RoleService;

/**
 * Auth Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-detail', function ($request, $response) {
    //if the auth-detail from auth returned an error
    if ($response->isError()) {
        //do nothing
        return;
    }

    //this/these will be used a lot
    $roleSql = RoleService::get('sql');

    // get response results
    $results = $response->getResults();

    // get role detail
    $roles = $roleSql->getRoleDetail($results['auth_id']);

    // merge results
    $results = array_merge($results, $roles);

    // set response results
    $response->setResults($results);
});
