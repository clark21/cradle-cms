<?php //-->

/**
 * Render the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/', function ($request, $response) {
    //Prepare body
    $data = [];

    //Render body
    $class = 'page-home branding';
    $title = cradle('global')->translate('Cradle PHP');
    $body = cradle('/app/www')->template('index', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the System Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/download', function($request, $response) {
    $location = $request->getStage('location');
    $filename = $request->getStage('filename');

    $response
        ->addHeader('Content-Encoding', 'UTF-8')
        ->addHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv');

    $response->setContent(file_get_contents($location));
});
