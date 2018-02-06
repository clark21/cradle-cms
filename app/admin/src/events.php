<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
*/

/**
 * Render admin page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-admin-page', function ($request, $response) {
    // create new request
    $metaRequest = \Cradle\Http\Request::i();
    // create new response
    $metaResponse = \Cradle\Http\Response::i();

    // trigger meta search
    cradle()->trigger('meta-search', $metaRequest, $metaResponse);

    // get results
    $results = $metaResponse->getResults('rows');

    // map results
    $navigation = array_map(function($meta) {
        return [
            'label' => ucwords($meta['meta_plural']),
            'href'  => sprintf('/admin/node/%s/search', $meta['meta_key'])
        ];
    }, $results);

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
            'foot'
        )
    );

    $response->setContent($content);
});
