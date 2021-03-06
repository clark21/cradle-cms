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
 * Render the System Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
    }

    //set a default range
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        foreach ($request->getStage('filter') as $key => $value) {
            //if invalid key format or there is no value
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key) || !strlen($value)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        foreach ($request->getStage('order') as $key => $value) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('system-object-search', $request, $response);

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //form the data
    $data = array_merge(
        //we need to case for things like
        //filter and sort on the template
        $request->getStage(),
        //this is from the search event
        $response->getResults()
    );

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //if there's an active field
    if ($data['schema']['active']) {
        //find it
        foreach ($data['schema']['filterable'] as $i => $filter) {
            //if we found it
            if ($filter === $data['schema']['active']) {
                //remove it from the filters
                unset($data['schema']['filterable'][$i]);
            }
        }

        //reindex filterable
        $data['schema']['filterable'] = array_values($data['schema']['filterable']);
    }

    $data['filterable_relations'] = [];
    foreach ($data['schema']['relations'] as $relation) {
        if ($relation['many'] < 2) {
            $data['filterable_relations'][] = $relation;
        }
    }

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-search page-admin';

    //render the body
    $body = cradle('/module/system')->template('object/search', $data, [
        'object_search_head',
        'object_search_form',
        'object_search_filters',
        'object_search_actions',
        'object_search_row_format',
        'object_search_row_actions'
    ]);

    //set content
    $response
        ->setPage('title', $data['schema']['plural'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Render the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

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

    //for ?copy=1 functionality
    if (empty($data['item']) && is_numeric($request->getStage('copy'))) {
        //table_id, 1 for example
        $request->setStage(
            $schema->getPrimaryFieldName(),
            $request->getStage('copy')
        );

        //get the original table row
        cradle()->trigger('system-object-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect(sprintf(
                '/admin/system/schema/%s/search',
                $request->getStage('schema')
            ));
        }

        //pass the item to the template
        $data['item'] = $response->getResults();

        //add suggestion value for each relation
        foreach ($data['schema']['relations'] as $name => $relation) {
            if ($relation['many'] > 1) {
                continue;
            }

            $suggestion = '_' . $relation['primary2'];

            $suggestionData = $data['item'];
            if ($relation['many'] == 0) {
                if (!isset($data['item'][$relation['name']])) {
                    continue;
                }

                $suggestionData = $data['item'][$relation['name']];

                if (!$suggestionData) {
                    continue;
                }
            }

            try {
                $data['item'][$suggestion] = SystemSchema::i($relation['name'])
                    ->getSuggestionFormat($suggestionData);
            } catch (Exception $e) {
            }
        }
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //if this is a relational process
    if ($request->hasStage('relation')) {
        //also pass the relation to the form
        $data['relation'] = $request->getStage('relation');
    }

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
    $class = 'page-admin-system-object-create page-admin';

    //set the action
    $data['action'] = 'create';

    //determine the title
    $data['title'] = cradle('global')->translate(
        'Create %s',
        $data['schema']['singular']
    );

    //render the body
    $body = cradle('/module/system')->template('object/form', $data, [
        'object_form_fields',
        'object_form_detail',
        'object_form_format'
    ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Render the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

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

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get the original table row
    cradle()->trigger('system-object-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //redirect
        $redirect = sprintf(
            '/admin/system/object/%s/search',
            $request->getStage('schema')
        );

        //this is for flexibility
        if ($request->hasStage('redirect_uri')) {
            $redirect = $request->getStage('redirect_uri');
        }

        //add a flash
        cradle('global')->flash($response->getMessage(), 'error');
        return cradle('global')->redirect($redirect);
    }

    $data['detail'] = $response->getResults();

    //if no item
    if (empty($data['item'])) {
        //pass the item to the template
        $data['item'] = $data['detail'];

        //add suggestion value for each relation
        foreach ($data['schema']['relations'] as $name => $relation) {
            if ($relation['many'] > 1) {
                continue;
            }

            $suggestion = '_' . $relation['primary2'];

            $suggestionData = $data['item'];
            if ($relation['many'] == 0) {
                if (!isset($data['item'][$relation['name']])) {
                    continue;
                }

                $suggestionData = $data['item'][$relation['name']];

                if (!$suggestionData) {
                    continue;
                }
            }

            try {
                $data['item'][$suggestion] = SystemSchema::i($relation['name'])
                    ->getSuggestionFormat($suggestionData);
            } catch (Exception $e) {
            }
        }
    }

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //determine the suggestion
    $data['detail']['suggestion'] = $schema->getSuggestionFormat($data['item']);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    $data['redirect'] = urlencode($request->getServer('REQUEST_URI'));

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-update page-admin';

    //set the action
    $data['action'] = 'update';

    //determine the title
    $data['title'] = cradle('global')->translate(
        'Updating %s',
        $data['schema']['singular']
    );

    //render the body
    $body = cradle('/module/system')->template('object/form', $data, [
        'object_form_fields',
        'object_form_detail',
        'object_form_format'
    ]);

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
 * Process the System Object Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //determine route
    $route = sprintf(
        '/admin/system/object/%s/search',
        $request->getStage('schema')
    );

    //this is for flexibility
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    $action = $request->getStage('bulk-action');
    $ids = $request->getStage($schema->getPrimaryFieldName());

    if (empty($ids)) {
        $response->setError(true, 'No IDs chosen');
        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Process Request
    $errors = [];
    foreach ($ids as $id) {
        //table_id, 1 for example
        $request->setStage($schema->getPrimaryFieldName(), $id);

        //case for actions
        switch ($action) {
            case 'remove':
                cradle()->trigger('system-object-remove', $request, $response);
                break;
            case 'restore':
                cradle()->trigger('system-object-restore', $request, $response);
                break;
            default:
                //set an error
                $response->setError(true, 'No valid action chosen');
                //let the search route handle the rest
                return cradle()->triggerRoute('get', $route, $request, $response);
        }

        if ($response->isError()) {
            $errors[] = $response->getMessage();
        } else {
            cradle()->log(
                sprintf(
                    '%s #%s %s',
                    $schema->getSingular(),
                    $id,
                    $action
                ),
                $request,
                $response
            );
        }
    }

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

    //add a flash
    if (!empty($errors)) {
        cradle('global')->flash(
            'Some items could not be processed',
            'error',
            $errors
        );
    } else {
        cradle('global')->flash(
            sprintf(
                'Bulk action %s successful',
                $action
            ),
            'success'
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //get all the schema field data
    $fields = $schema->getFields();

    //these are invalid types to set
    $invalidTypes = ['none', 'active', 'created', 'updated'];

    //for each field
    foreach ($fields as $name => $field) {
        //if the field is invalid
        if (in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if no value
        if ($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if (//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if (strtoupper($field['default']) === 'NOW()') {
                $field['default'] = date('Y-m-d H:i:s');
            }

            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }
    }

    //TODO make a better way of doing this
    //if(//special shot out to user
    //    in_array('user', $schema->getRelations())
    //    && !$request->hasStage('user_id')
    //) {
    //    $request->setStage('user_id', $request->getSession('me', 'user_id'));
    //}

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/object/%s/create',
            $request->getStage('schema')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good

    //record logs
    cradle()->log(
        sprintf(
            'New %s created',
            $schema->getSingular()
        ),
        $request,
        $response
    );

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

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Created',
        'success',
        $schema->getSingular()
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get all the schema field data
    $fields = $schema->getFields();

    //these are invalid types to set
    $invalidTypes = ['none', 'active', 'created', 'updated'];

    //for each field
    foreach ($fields as $name => $field) {
        //if the field is invalid
        if (in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if no value
        if ($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if (//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if (strtoupper($field['default']) === 'NOW()') {
                $field['default'] = date('Y-m-d H:i:s');
            }

            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/object/%s/update/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good

    //record logs
    cradle()->log(
        sprintf(
            '%s #%s updated',
            $schema->getSingular(),
            $request->getStage('id')
        ),
        $request,
        $response
    );

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

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Updated',
        'success',
        $schema->getSingular()
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the System Object Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/remove/:id', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-remove', $request, $response);

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
        $message = cradle('global')->translate('%s was Removed', $schema->getSingular());
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s #%s removed',
                $schema->getSingular(),
                $request->getStage('id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the System Object Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/restore/:id', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-restore', $request, $response);

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
        $message = cradle('global')->translate('%s was Restored', $schema->getSingular());
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                '%s #%s restored.',
                $schema->getSingular(),
                $request->getStage('id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process Object Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/import', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
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
            'errors' => $errors
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

/**
 * Process Object Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/export/:type', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    // set redirect
    $request->setStage('redirect', '/admin/system/object/' . $request->getStage('schema') . '/search');
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //if exist get schema
    if ($request->hasStage('relation')) {
        $relation = $request->getStage('relation');
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = $schema->getFilterableFieldNames();

        //allow relation primary
        if (isset($relation['schema']['primary'])) {
            $filterable[] = $relation['schema']['primary'];
        }

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = $schema->getSortableFieldNames();

        foreach ($request->getStage('order') as $key => $value) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            }
        }
    }

    $request->setStage('range', 0);

    //trigger job
    cradle()->trigger('system-object-search', $request, $response);

    //get the output type
    $type = $request->getStage('type');
    //get the rows
    $rows = $response->getResults('rows');
    //determine the filename
    $filename = $schema->getPlural() . '-' . date('Y-m-d');

    //flatten all json columns
    foreach ($rows as $i => $row) {
        foreach ($row as $key => $value) {
            //transform oobject to array
            if (is_object($value)) {
                $value = (array) $value;
            }

            //if array, let's flatten
            if (is_array($value)) {
                //if no count
                if (!count($value)) {
                    $rows[$i][$key] = '';
                    continue;
                }

                //if regular array
                if (isset($value[0])) {
                    $rows[$i][$key] = implode(',', $value);
                    continue;
                }

                $rows[$i][$key] = json_encode($value);
                continue;
            }

            //provision for any other conversions needed
        }
    }

    //if the output type is csv
    if ($type === 'csv') {
        //if there are no rows
        if (empty($rows)) {
            //at least give the headers
            $rows = [array_keys($schema->getFields())];
        } else {
            //add the headers
            array_unshift($rows, array_keys($rows[0]));
        }

        //set the output headers
        $response
            ->addHeader('Content-Encoding', 'UTF-8')
            ->addHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv');

        //open a tmp file
        $file = tmpfile();
        //for each row
        foreach ($rows as $row) {
            //add it to the tmp file as a csv
            fputcsv($file, array_values($row));
        }

        //this is the final output
        $contents = '';

        //rewind the file pointer
        rewind($file);
        //and set all the contents
        while (!feof($file)) {
            $contents .= fread($file, 8192);
        }

        //close the tmp file
        fclose($file);

        //set contents
        return $response->setContent($contents);
    }

    //if the output type is xml
    if ($type === 'xml') {
        //recursive xml parser
        $toXml = function ($array, $xml) use (&$toXml) {
            //for each array
            foreach ($array as $key => $value) {
                //if the value is an array
                if (is_array($value)) {
                    //if the key is not a number
                    if (!is_numeric($key)) {
                        //send it out for further processing (recursive)
                        $toXml($value, $xml->addChild($key));
                        continue;
                    }

                    //send it out for further processing (recursive)
                    $toXml($value, $xml->addChild('item'));
                    continue;
                }

                //add the value
                $xml->addChild($key, htmlspecialchars($value));
            }

            return $xml;
        };

        //set up the xml template
        $root = sprintf(
            "<?xml version=\"1.0\"?>\n<%s></%s>",
            $schema->getName(),
            $schema->getName()
        );

        //set the output headers
        $response
            ->addHeader('Content-Encoding', 'UTF-8')
            ->addHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.xml');

        //get the contents
        $contents = $toXml($rows, new SimpleXMLElement($root))->asXML();

        //set the contents
        return $response->setContent($contents);
    }

    //json maybe?

    //set the output headers
    $response
        ->addHeader('Content-Encoding', 'UTF-8')
        ->addHeader('Content-Type', 'text/json; charset=UTF-8')
        ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.json');

    //set content
    $response->set('json', $rows);
});
