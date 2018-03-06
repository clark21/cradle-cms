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
    $navigation = cradle('global')->config('admin/menu');

    $navMatch = function (...$args) use ($request) {
        //$haystack, $needle, $options
        $haystack = $request->get('path', 'string');
        $needle = array_shift($args);
        $options = array_pop($args);

        foreach ($args as $path) {
            $needle .= '/' . $path;
        }

        if (strpos($haystack, $needle) === 0) {
            return $options['fn']();
        }

        return $options['inverse']();
    };

    cradle('global')->handlebars()->registerHelper('nav_match', $navMatch);

    // menu request
    $menuRecordRequest = \Cradle\Http\Request::i();
    // menu response
    $menuRecordResponse = \Cradle\Http\Response::i();

    // set navigation
    $menuRecordRequest->setStage('navigation', $navigation);

    // trigger menu get record count
    cradle()->trigger('menu-get-record-count', $menuRecordRequest, $menuRecordResponse);

    // get navigation
    $navigation = $menuRecordResponse->getResults();

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
            'side',
            'menu'
        )
    );

    $response->setContent($content);
});
