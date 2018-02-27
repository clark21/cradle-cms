<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;
use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/search/:schema2/:id', function($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = $request->getStage('schema1');
    $schema2 = SystemSchema::i($request->getStage('schema2'));
    $request->setStage('filter', $schema2->getPrimaryFieldName(), $id);

    //remove the data from stage
    //because we wont need it anymore
    $request
        ->removeStage('id')
        ->removeStage('schema1')
        ->removeStage('schema2');

    //get the schema detail
    $detailRequest = Request::i()->load();
    $detailResponse = Response::i()->load();

    $detailRequest
        //let the event know what schema we are using
        ->setStage('schema', $schema2->getName())
        //table_id, 1 for example
        ->setStage($schema2->getPrimaryFieldName(), $id);

    //now get the actual table row
    cradle()->trigger('system-object-detail', $detailRequest, $detailResponse);

    //get the table row
    $results = $detailResponse->getResults();
    //and determine the title of the table row
    //this will be used on the breadcrumbs and title for example
    $suggestion = $schema2->getSuggestionFormat($results);

    //pass all the relational data we collected
    $request
        ->setStage('relation', 'schema', $schema2->getAll())
        ->setStage('relation', 'data', $results)
        ->setStage('relation', 'suggestion', $suggestion);

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/search',
            $schema1
        ),
        $request,
        $response
    );
});

/**
 * Render the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/create/:schema2/:id', function($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = $request->getStage('schema1');
    $schema2 = SystemSchema::i($request->getStage('schema2'));
    $request->setStage('filter', $schema2->getPrimaryFieldName(), $id);

    //remove the data from stage
    //because we wont need it anymore
    $request
        ->removeStage('id')
        ->removeStage('schema1')
        ->removeStage('schema2');

    //get the schema detail
    $detailRequest = Request::i()->load();
    $detailResponse = Response::i()->load();

    $detailRequest
        //let the event know what schema we are using
        ->setStage('schema', $schema2->getName())
        //table_id, 1 for example
        ->setStage($schema2->getPrimaryFieldName(), $id);

    //now get the actual table row
    cradle()->trigger('system-object-detail', $detailRequest, $detailResponse);

    //get the table row
    $results = $detailResponse->getResults();
    //and determine the title of the table row
    //this will be used on the breadcrumbs and title for example
    $suggestion = $schema2->getSuggestionFormat($results);

    //pass all the relational data we collected
    $request
        ->setStage('relation', 'schema', $schema2->getAll())
        ->setStage('relation', 'data', $results)
        ->setStage('relation', 'suggestion', $suggestion);

    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/create',
            $schema1
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema1/search/:schema2/:id', function($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = SystemSchema::i($request->getStage('schema2'));

    //setup the redirect now, kasi we will change it later
    $redirect = sprintf(
        '/admin/system/object/%s/search/%s/%s',
        $schema1->getName(),
        $schema2->getName(),
        $id
    );

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //pass all the relational data we collected
    $request
        ->setStage('route', $redirect)
        ->setStage('redirect_uri', $redirect);

    //now let the original create take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/search',
            $schema1->getName()
        ),
        $request,
        $response
    );
});

/**
 * Process the System Object Create Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema1/create/:schema2/:id', function($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = SystemSchema::i($request->getStage('schema2'));

    //setup the redirect now, kasi we will change it later
    $redirect = sprintf(
        '/admin/system/object/%s/search/%s/%s',
        $schema1->getName(),
        $schema2->getName(),
        $id
    );

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //pass all the relational data we collected
    $request
        ->setStage('route', sprintf(
            '/admin/system/object/%s/create/%s/%s',
            $schema1->getName(),
            $schema2->getName(),
            $id
        ))
        ->setStage('redirect_uri', 'false');

    //now let the original create take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/create',
            $schema1->getName()
        ),
        $request,
        $response
    );

    //if there's an error or there's content
    if ($response->isError() || $response->hasContent()) {
        return;
    }

    //so it must have been successful
    //lets link the tables now
    $primary1 = $schema1->getPrimaryFieldName();
    $primary2 = $schema2->getPrimaryFieldName();

    //set the stage to link
    $request
        ->setStage('schema2', $schema1->getName())
        ->setStage('schema1', $schema2->getName())
        ->setStage($primary1, $response->getResults($primary1))
        ->setStage($primary2, $id);

    //now link it
    cradle()->trigger('system-object-link', $request, $response);

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Created', 'success',
        $schema1->getSingular()
    ));

    cradle('global')->redirect($redirect);
});
