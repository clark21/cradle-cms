<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
*/

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render admin page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-admin-page', function ($request, $response) {
    $navigationRequest = (new Request())->load();
    $navigationResponse = new Response();
    cradle()->trigger(
        'system-schema-search',
        $navigationRequest,
        $navigationResponse
    );

    $navigation = $navigationResponse->getResults();

    $content = cradle('/app/admin')->template(
        '_page',
        array(
            'page' => $response->getPage(),
            'results' => $response->getResults(),
            'content' => $response->getContent(),
            'navigation' => $navigation
        ),
        array(
            'head',
            'foot',
            'sidebar'
        )
    );

    $response->setContent($content);
});
