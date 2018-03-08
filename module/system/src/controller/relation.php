<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Utility\File;
use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/:id/search/:schema2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //re routing will take care of this
    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema1'));
    $relation = $schema->getRelations($request->getStage('schema2'));

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = SystemSchema::i($request->getStage('schema2'));
        $relation = $schema->getRelations($request->getStage('schema1'));
    }

    if (empty($relation) || $relation['many'] < 2) {
        $redirect = sprintf(
            '/admin/system/object/%s/search',
            $request->getStage('schema1')
        );

        //if there is a specified redirect
        if ($request->getStage('redirect_uri')) {
            //set the redirect
            $redirect = $request->getStage('redirect_uri');
        }

        //add a flash
        $message = cradle('global')->translate('Invalid relation');
        cradle('global')->flash($message, 'error');
        cradle('global')->redirect($redirect);
    }

    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = $request->getStage('schema2');
    $request->setStage('filter', $schema1->getPrimaryFieldName(), $id);

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
        ->setStage('schema', $schema1->getName())
        //table_id, 1 for example
        ->setStage($schema1->getPrimaryFieldName(), $id);

    //now get the actual table row
    cradle()->trigger('system-object-detail', $detailRequest, $detailResponse);

    //get the table row
    $results = $detailResponse->getResults();
    //and determine the title of the table row
    //this will be used on the breadcrumbs and title for example
    $suggestion = $schema1->getSuggestionFormat($results);

    //pass all the relational data we collected
    $request
        ->setStage('relation', 'schema', $schema1->getAll())
        ->setStage('relation', 'data', $results)
        ->setStage('relation', 'suggestion', $suggestion);

    //----------------------------//
    // 3. Render Template
    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/search',
            $schema2
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
$cradle->get('/admin/system/object/:schema1/:id/create/:schema2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //re routing will take care of this
    //----------------------------//
    // 2. Prepare Data
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = $request->getStage('schema2');
    $request->setStage('filter', $schema1->getPrimaryFieldName(), $id);

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
        ->setStage('schema', $schema1->getName())
        //table_id, 1 for example
        ->setStage($schema1->getPrimaryFieldName(), $id);

    //now get the actual table row
    cradle()->trigger('system-object-detail', $detailRequest, $detailResponse);

    //get the table row
    $results = $detailResponse->getResults();
    //and determine the title of the table row
    //this will be used on the breadcrumbs and title for example
    $suggestion = $schema1->getSuggestionFormat($results);

    //pass all the relational data we collected
    $request
        ->setStage('relation', 'schema', $schema1->getAll())
        ->setStage('relation', 'data', $results)
        ->setStage('relation', 'suggestion', $suggestion);

    //----------------------------//
    // 3. Render Template
    //now let the original search take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/create',
            $schema2
        ),
        $request,
        $response
    );
});

/**
 * Render the System Object Link Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/:id/link/:schema2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema1'));

    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //this next set will use redirect, so we need to find it out now
    //redirect
    $redirect = sprintf(
        '/admin/system/object/%s/%s/search/%s',
        $request->getStage('schema1'),
        $request->getStage('id'),
        $request->getStage('schema2')
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //pass the relation
    $relation = $request->getStage('schema2');
    $table = $data['schema']['name'] . '_' . $relation;

    //if we can't find the relation
    if (!isset($data['schema']['relations'][$table])) {
        //try reverse
        $table = $relation . '_' . $data['schema']['name'];
        $relations = $schema->getReverseRelations();

        //wala talaga
        if (!isset($relations[$table])) {
            //set a message
            $message = cradle('global')->translate('Relation does not exist');

            //if we dont want to redirect
            if ($redirect === 'false') {
                return $response->setError(true, $message);
            }

            cradle('global')->flash($message, 'error');
            return cradle('global')->redirect($redirect);
        }

        //fake it
        $data['schema']['relations'][$table] = $relations[$table]['source'];
        $data['schema']['relations'][$table]['primary1'] = $relations[$table]['primary2'];
        $data['schema']['relations'][$table]['primary2'] = $relations[$table]['primary1'];
    }

    //this is the main relation we are dealing with
    $data['relation'] = $data['schema']['relations'][$table];

    $request->setStage('schema', $request->getStage('schema1'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get the original table row
    cradle()->trigger('system-object-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
        return cradle('global')->redirect($redirect);
    }

    //pass the item to the template
    $data['row'] = $response->getResults();

    //make a suggestion
    $data['row']['suggestion'] = $schema->getSuggestionFormat($data['row']);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //pass suggestion title field to the template
    $data['schema']['relations'][$table]['suggestion_name'] = '_' . $data['schema']['relations'][$table]['primary2'];

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    //if we only want the data
    if ($request->getStage('render') === 'false') {
        return $response->setJson($data);
    }

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-link page-admin';

    //determine the title
    $data['title'] = cradle('global')->translate(
        'Linking %s',
        $data['relation']['singular']
    );

    //render the body
    $body = cradle('/module/system')->template('object/link', $data);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Process the System Object Search Page Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema1/:id/search/:schema2', function ($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = SystemSchema::i($request->getStage('schema2'));

    //setup the redirect now, kasi we will change it later
    $redirect = sprintf(
        '/admin/system/object/%s/%s/search/%s',
        $schema1->getName(),
        $id,
        $schema2->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
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
            $schema2->getName()
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
$cradle->post('/admin/system/object/:schema1/:id/create/:schema2', function ($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = SystemSchema::i($request->getStage('schema2'));

    //setup the redirect now, kasi we will change it later
    $redirect = sprintf(
        '/admin/system/object/%s/%s/search/%s',
        $schema1->getName(),
        $id,
        $schema2->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    // setup the route
    $route = sprintf(
        '/admin/system/object/%s/%s/create/%s',
        $schema1->getName(),
        $id,
        $schema2->getName()
    );

    //if there is a specified route
    if ($request->hasStage('route')) {
        //set the route
        $route = $request->getStage('route');
    }

    //pass all the relational data we collected
    $request
        ->setStage('route', $route)
        ->setStage('redirect_uri', 'false');

    //now let the original create take over
    cradle()->triggerRoute(
        'post',
        sprintf(
            '/admin/system/object/%s/create',
            $schema2->getName()
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

    if ($primary1 == $primary2) {
        $primary1 = sprintf('%s_1', $primary1);
        $primary2 = sprintf('%s_2', $primary2);
    }

    //set the stage to link
    $request
        ->setStage('schema1', $schema1->getName())
        ->setStage('schema2', $schema2->getName())
        ->setStage($primary1, $id)
        ->setStage($primary2, $response->getResults($schema2->getPrimaryFieldName()));

    //now link it
    cradle()->trigger('system-object-link', $request, $response);

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Created',
        'success',
        $schema2->getSingular()
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Link object to object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema1/:id/link/:schema2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema1'));
    $relation = $schema->getRelations($request->getStage('schema2'));

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = SystemSchema::i($request->getStage('schema2'));
        $relation = $schema->getRelations($request->getStage('schema1'));

        $id1 = $request->getStage('id');
        $id2 = $request->getStage($relation['primary1']);
    } else {
        $id1 = $request->getStage('id');
        $id2 = $request->getStage($relation['primary2']);
    }

    //redirect
    $redirect = sprintf(
        '/admin/system/object/%s/search/%s/%s',
        $request->getStage('schema2'),
        $request->getStage('schema1'),
        $request->getStage('id')
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    $request
        ->setStage('id1', $id1)
        ->setStage('id2', $id2)
        ->setStage('redirect_uri', 'false');

    //----------------------------//
    // 3. Process Request
    $route = sprintf(
        '/admin/system/object/%s/%s/link/%s/%s',
        $request->getStage('schema1'),
        $request->getStage('id1'),
        $request->getStage('schema2'),
        $request->getStage('id2')
    );

    cradle()->triggerRoute('get', $route, $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/object/%s/%s/link/%s',
            $request->getStage('schema1'),
            $request->getStage('id'),
            $request->getStage('schema2')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate(
        '%s was linked to %s',
        $schema->getSingular(),
        $relation['singular']
    );

    cradle('global')->flash($message, 'success');

    //record logs
    cradle()->log(
        sprintf(
            '%s #%s linked to %s #%s',
            $schema->getSingular(),
            $request->getStage('id1'),
            $relation['singular'],
            $request->getStage('id2')
        ),
        $request,
        $response
    );

    cradle('global')->redirect($redirect);
});

/**
 * Link object from object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/:id1/link/:schema2/:id2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema1'));
    $relation = $schema->getRelations($request->getStage('schema2'));

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = SystemSchema::i($request->getStage('schema2'));
        $relation = $schema->getRelations($request->getStage('schema1'));

        $request->setStage($relation['primary1'], $request->getStage('id2'));
        $request->setStage($relation['primary2'], $request->getStage('id1'));
    } else {
        $request->setStage($relation['primary1'], $request->getStage('id1'));
        $request->setStage($relation['primary2'], $request->getStage('id2'));
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-link', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //redirect
    $redirect = sprintf(
        '/admin/system/object/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate(
            '%s was linked to %s',
            $schema->getSingular(),
            $relation['singular']
        );

        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s #%s linked to %s #%s',
                $schema->getSingular(),
                $request->getStage('id1'),
                $relation['singular'],
                $request->getStage('id2')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Unlink object from object
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/:id1/unlink/:schema2/:id2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema1'));
    $relation = $schema->getRelations($request->getStage('schema2'));

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = SystemSchema::i($request->getStage('schema2'));
        $relation = $schema->getRelations($request->getStage('schema1'));

        $request->setStage($relation['primary1'], $request->getStage('id2'));
        $request->setStage($relation['primary2'], $request->getStage('id1'));
    } else {
        $request->setStage($relation['primary1'], $request->getStage('id1'));
        $request->setStage($relation['primary2'], $request->getStage('id2'));
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-unlink', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //redirect
    $redirect = sprintf(
        '/admin/system/object/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = cradle('global')->translate(
            '%s was unlinked from %s',
            $schema->getSingular(),
            $relation['singular']
        );

        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s #%s unlinked from %s #%s',
                $schema->getSingular(),
                $request->getStage('id1'),
                $relation['singular'],
                $request->getStage('id2')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process Object Exporting Filtered by Relation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema1/:id/export/:schema2/:type', function ($request, $response) {
    //variable list
    $id = $request->getStage('id');
    $schema1 = SystemSchema::i($request->getStage('schema1'));
    $schema2 = $request->getStage('schema2');
    $request->setStage('filter', $schema1->getPrimaryFieldName(), $id);

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
        ->setStage('schema', $schema1->getName())
        //table_id, 1 for example
        ->setStage($schema1->getPrimaryFieldName(), $id);

    //now get the actual table row
    cradle()->trigger('system-object-detail', $detailRequest, $detailResponse);

    //get the table row
    $results = $detailResponse->getResults();
    //and determine the title of the table row
    //this will be used on the breadcrumbs and title for example
    $suggestion = $schema1->getSuggestionFormat($results);

    //pass all the relational data we collected
    $request
        ->setStage('relation', 'schema', $schema1->getAll())
        ->setStage('relation', 'data', $results)
        ->setStage('relation', 'suggestion', $suggestion);

    //now let the original export take over
    cradle()->triggerRoute(
        'get',
        sprintf(
            '/admin/system/object/%s/export/%s',
            $schema2,
            $request->getStage('type')
        ),
        $request,
        $response
    );
});

/**
 * Process Ajax Object Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/:id/import/:schema2', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        //Set JSON Content
        return $response->setContent(json_encode([
            'error' => true,
            'message' => 'Unauthorized.'
        ]));
    }

    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema'));

    //----------------------------//
    // 3. Process Request
    //get schema data
    cradle()->trigger('system-object-import', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //if the import event returned errors
    if ($response->isError()) {
        $errors = [];
        //loop through each row
        foreach ($response->getValidation() as $i => $validation) {
            //and loop through each error
            foreach ($validation as $key => $error) {
                //add the error
                $errors[] = sprintf('ROW %s - %s: %s', $i, $key, $error);
            }
        }

        //Set JSON Content
        return $response->setContent(json_encode([
            'error' => true,
            'message' => $response->getMessage(),
            'errores' => $errors
        ]));
    }

    //record logs
    cradle()->log(
        sprintf(
            '%s was Imported',
            $schema->getPlural()
        ),
        $request,
        $response
    );

    //add a flash
    $message = cradle('global')->translate(sprintf(
        '%s was Imported',
        $schema->getPlural()
    ));

    //Set JSON Content
    return $response->setContent(json_encode([
        'error' => false,
        'message' => $message
    ]));
});
