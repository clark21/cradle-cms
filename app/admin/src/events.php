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
    $objectRequest = \Cradle\Http\Request::i();
    // create new response
    $objectResponse = \Cradle\Http\Response::i();

    // trigger object search
    cradle()->trigger('object-search', $objectRequest, $objectResponse);

    // get results
    $results = $objectResponse->getResults('rows');

    // map results
    $navigation = array_map(function($object) {
        return [
            'label' => ucwords($object['object_plural']),
            'href'  => sprintf('/admin/object/%s/search', $object['object_key'])
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
