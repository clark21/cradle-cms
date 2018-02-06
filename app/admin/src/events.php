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
    // search meta's
    cradle()->trigger('meta-search', $request, $response);

    // get the results
    $results = $response->getResults('rows');

    // custom navigation
    $navigation = [];

    // iterate on each results
    foreach($results as $result) {
        // set the navigation
        $navigation[] = [
            'label' => $result['meta_plural'],
            'href' => sprintf('/admin/%s/search', $result['meta_slug'])
        ];
    }

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

    cradle()->inspect($content);

    $response->setContent($content);
});
