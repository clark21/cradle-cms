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
 * Render the System Object Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //set a default range
    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        foreach($request->getStage('filter') as $key => $value) {
            if(!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        foreach($request->getStage('order') as $key => $value) {
            if(!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('system-object-search', $request, $response);

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
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
    if($data['schema']['active']) {
        //find it
        foreach($data['schema']['filterable'] as $i => $filter) {
            //if we found it
            if($filter === $data['schema']['active']) {
                //remove it from the filters
                unset($data['schema']['filterable'][$i]);
            }
        }

        //reindex filterable
        $data['schema']['filterable'] = array_values($data['schema']['filterable']);
    }

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-search page-admin';

    //add custom page helpers
    cradle('global')
        ->handlebars()
        ->registerHelper('when', function(...$args) {
            //$value1, $operator, $value2, $options
            $options = array_pop($args);
            $value2 = array_pop($args);
            $operator = array_pop($args);

            $value1 = array_shift($args);

            foreach($args as $arg) {
                if (isset($value1[$arg])) {
                    $value1 = $value1[$arg];
                }
            }

            $valid = false;

            switch (true) {
                case $operator == '=='   && $value1 == $value2:
                case $operator == '==='  && $value1 === $value2:
                case $operator == '!='   && $value1 != $value2:
                case $operator == '!=='  && $value1 !== $value2:
                case $operator == '<'    && $value1 < $value2:
                case $operator == '<='   && $value1 <= $value2:
                case $operator == '>'    && $value1 > $value2:
                case $operator == '>='   && $value1 >= $value2:
                case $operator == '&&'   && ($value1 && $value2):
                case $operator == '||'   && ($value1 || $value2):
                    $valid = true;
                    break;
            }

            if($valid) {
                return $options['fn']();
            }

            return $options['inverse']();
        })
        ->registerHelper('sorturl', function($key) {
            $query = $_GET;
            $value = null;
            if(isset($query['order'][$key])) {
                $value = $query['order'][$key];
            }

            if(is_null($value)) {
                $query['order'][$key] = 'ASC';
            } else if($value === 'ASC') {
                $query['order'][$key] = 'DESC';
            } else if($value === 'DESC') {
                unset($query['order'][$key]);
            }

            return http_build_query($query);
        })
        ->registerHelper('sortcaret', function($key) {
            $caret = null;
            if(isset($_GET['order'][$key])
                && $_GET['order'][$key] === 'ASC'
            ) {
                $caret = '<i class="fa fa-caret-up"></i>';
            } else if(isset($_GET['order'][$key])
                && $_GET['order'][$key] === 'DESC'
            ) {
                $caret = '<i class="fa fa-caret-down"></i>';
            }

            return $caret;
        })
        ->registerHelper('is_active', function($row, $schema, $options) {
            if(!$schema['active'] || $row[$schema['active']]) {
                return $options['fn']();
            }

            return $options['inverse']();
        })
        ->registerHelper('get_format', function($row, $schema, $options) {
            $columns = [];
            foreach($schema['fields'] as $name => $field) {
                if(!in_array($name, $schema['listable'])) {
                    continue;
                }

                $field['list']['value'] = $row[$name];
                $columns[] = $options['fn']($field['list']);
            }

            return implode('', $columns);
        });

    //render the body
    $body = cradle('/module/system')->template('object/search', $data, [
        'object_filters'
    ]);

    //set content
    $response
        ->setPage('title', $data['schema']['plural'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

    //render page
    cradle()->trigger('render-admin-page', $request, $response);

    //record logs
    cradle()->log(
        sprintf(
            'View %s listing',
            $schema->getSingular()
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
 * Render the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //pass suggestion title field for each relation to the template
    foreach ($data['schema']['relations'] as $name => $relation) {
        $data['schema']['relations'][$name]['suggestion_name'] = '_' . $relation['primary2'];
    }

    //if this is a relational process
    if ($request->hasStage('relation')) {
        //also pass the relation to the form
        $data['relation'] = $request->getStage('relation');
    }

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-create page-admin';

    //determine the title
    $data['title'] = cradle('global')->translate(
        'Create %s',
        $data['schema']['singular']
    );

    //add custom page helpers
    cradle('global')
        ->handlebars()
        ->registerHelper('when', function(...$args) {
            //$value1, $operator, $value2, $options
            $options = array_pop($args);
            $value2 = array_pop($args);
            $operator = array_pop($args);

            $value1 = array_shift($args);

            foreach($args as $arg) {
                if(!isset($value1[$arg])) {
                    $value1 = null;
                    break;
                }

                $value1 = $value1[$arg];
            }

            $valid = false;

            switch (true) {
                case $operator == '=='   && $value1 == $value2:
                case $operator == '==='  && $value1 === $value2:
                case $operator == '!='   && $value1 != $value2:
                case $operator == '!=='  && $value1 !== $value2:
                case $operator == '<'    && $value1 < $value2:
                case $operator == '<='   && $value1 <= $value2:
                case $operator == '>'    && $value1 > $value2:
                case $operator == '>='   && $value1 >= $value2:
                case $operator == '&&'   && ($value1 && $value2):
                case $operator == '||'   && ($value1 || $value2):
                    $valid = true;
                    break;
            }

            if($valid) {
                return $options['fn']();
            }

            return $options['inverse']();
        })
        ->registerHelper('loop', function(...$args) {
            $args = func_get_args();

            //$object, $options
            $options = array_pop($args);
            $object = array_shift($args);

            foreach($args as $arg) {
                if(!isset($object[$arg])) {
                    $object = null;
                    break;
                }

                $object = $object[$arg];
            }

            if (is_scalar($object) || !$object) {
                return $options['inverse']();
            }

            //test foreach
            $keyName = null;
            $valueName = null;
            //see handlebars.js {{#each array as |value, key|}}
            if (strpos($options['args'], ' as |') !== false
                && substr_count($options['args'], '|') === 2
            ) {
                list($tmp, $valueName) = explode('|', $options['args']);

                if (strpos($valueName, ',') !== false) {
                    list($valueName, $keyName) = explode(',', trim($valueName));
                }

                $keyName = trim($keyName);
                $valueName = trim($valueName);
            }

            $buffer = [];
            $object = (array) $object;

            $first = $last = null;

            if (!empty($object)) {
                //get last
                end($object);
                $last = key($object);

                //get first
                reset($object);
                $first = key($object);
            }

            $i = 0;
            foreach ($object as $key => $value) {
                //pass on hash
                if (is_array($value)
                    && isset($options['hash'])
                    && is_array($options['hash'])
                ) {
                    $value = array_merge($value, $options['hash']);
                }

                if (!is_array($value)) {
                    $value = ['this' => $value];
                } else {
                    $value['this'] = $value;
                }

                if ($valueName) {
                    $value[$valueName] = $value['this'];
                }

                if ($keyName) {
                    $value[$keyName] = $key;
                }

                $value['@index'] = $i;
                $value['@key'] = $key;
                $value['@first'] = $first == $key;
                $value['@last'] = $last == $key;

                $buffer[] = $options['fn']($value);
                $i++;
            }

            return implode('', $buffer);
        })
        ->registerHelper('has', function($value, $array, $options) {
            if(!is_array($array)) {
                return $options['inverse']();
            }

            if(isset($array[$value])) {
                return $options['fn']();
            }

            return $options['inverse']();
        });

    //render the body
    $body = cradle('/module/system')->template('object/form', $data, [
        'object_fields'
    ]);

    //if we only want the body
    if($request->getStage('render') === 'body') {
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
 * Render the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/update/:id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //if no item
    if(empty($data['item'])) {
        //table_id, 1 for example
        $request->setStage(
            $schema->getPrimaryFieldName(),
            $request->getStage('id')
        );

        //get the original table row
        cradle()->trigger('system-object-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect(sprintf(
                '/admin/system/object/%s/search',
                $request->getStage('schema')
            ));
        }

        //pass the item to the template
        $data['item'] = $response->getResults();

        //add suggestion value for each relation
        foreach ($data['schema']['relations'] as $name => $relation) {
            $suggestion = '_' . $relation['primary2'];
            try {
                $data['item'][$suggestion] = SystemSchema::i($relation['name'])
                    ->getSuggestionFormat($data['item']);
            } catch(Exception $e) {}
        }
    }

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //pass suggestion title field for each relation to the template
    foreach ($data['schema']['relations'] as $name => $relation) {
        $data['schema']['relations'][$name]['suggestion_name'] = '_' . $relation['primary2'];
    }

    //----------------------------//
    // 3. Render Template
    //set the class name
    $class = 'page-admin-system-object-update page-admin';

    //determine the title
    $data['title'] = cradle('global')->translate(
        'Updating %s',
        $data['schema']['singular']
    );

    //add custom page helpers
    cradle('global')
        ->handlebars()
        ->registerHelper('when', function(...$args) {
            //$value1, $operator, $value2, $options
            $options = array_pop($args);
            $value2 = array_pop($args);
            $operator = array_pop($args);

            $value1 = array_shift($args);

            foreach($args as $arg) {
                if(!isset($value1[$arg])) {
                    $value1 = null;
                    break;
                }

                $value1 = $value1[$arg];
            }

            $valid = false;

            switch (true) {
                case $operator == '=='   && $value1 == $value2:
                case $operator == '==='  && $value1 === $value2:
                case $operator == '!='   && $value1 != $value2:
                case $operator == '!=='  && $value1 !== $value2:
                case $operator == '<'    && $value1 < $value2:
                case $operator == '<='   && $value1 <= $value2:
                case $operator == '>'    && $value1 > $value2:
                case $operator == '>='   && $value1 >= $value2:
                case $operator == '&&'   && ($value1 && $value2):
                case $operator == '||'   && ($value1 || $value2):
                    $valid = true;
                    break;
            }

            if($valid) {
                return $options['fn']();
            }

            return $options['inverse']();
        })
        ->registerHelper('loop', function(...$args) {
            $args = func_get_args();

            //$object, $options
            $options = array_pop($args);
            $object = array_shift($args);

            foreach($args as $arg) {
                if(!isset($object[$arg])) {
                    $object = null;
                    break;
                }

                $object = $object[$arg];
            }

            if (is_scalar($object) || !$object) {
                return $options['inverse']();
            }

            //test foreach
            $keyName = null;
            $valueName = null;
            //see handlebars.js {{#each array as |value, key|}}
            if (strpos($options['args'], ' as |') !== false
                && substr_count($options['args'], '|') === 2
            ) {
                list($tmp, $valueName) = explode('|', $options['args']);

                if (strpos($valueName, ',') !== false) {
                    list($valueName, $keyName) = explode(',', trim($valueName));
                }

                $keyName = trim($keyName);
                $valueName = trim($valueName);
            }

            $buffer = [];
            $object = (array) $object;

            $first = $last = null;

            if (!empty($object)) {
                //get last
                end($object);
                $last = key($object);

                //get first
                reset($object);
                $first = key($object);
            }

            $i = 0;
            foreach ($object as $key => $value) {
                //pass on hash
                if (is_array($value)
                    && isset($options['hash'])
                    && is_array($options['hash'])
                ) {
                    $value = array_merge($value, $options['hash']);
                }

                if (!is_array($value)) {
                    $value = ['this' => $value];
                } else {
                    $value['this'] = $value;
                }

                if ($valueName) {
                    $value[$valueName] = $value['this'];
                }

                if ($keyName) {
                    $value[$keyName] = $key;
                }

                $value['@index'] = $i;
                $value['@key'] = $key;
                $value['@first'] = $first == $key;
                $value['@last'] = $last == $key;

                $buffer[] = $options['fn']($value);
                $i++;
            }

            return implode('', $buffer);
        })
        ->registerHelper('has', function($value, $array, $options) {
            if(!is_array($array)) {
                return $options['inverse']();
            }

            if(isset($array[$value])) {
                return $options['fn']();
            }

            return $options['inverse']();
        });

    //render the body
    $body = cradle('/module/system')->template('object/form', $data, [
        'object_fields'
    ]);

    //if we only want the body
    if($request->getStage('render') === 'body') {
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
 * Process the System Object Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //get all the schema field data
    $fields = $schema->getFields();

    //these are invalid types to set
    $invalidTypes = ['none', 'active', 'created', 'updated'];
    //get the required fields
    $requiredFields = $schema->getRequiredFieldNames();

    //for each field
    foreach($fields as $name => $field) {
        //if the field is invalid
        if(in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        if(//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }

        if(//if this field is required
            in_array($name, $requiredFields)
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            //set the default
            $request->setStage($name, null);
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
    if($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/object/%s/create',
            $request->getStage('schema')
        );

        //this is for flexibility
        if($request->hasStage('route')) {
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
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Created', 'success',
        $schema->getSingular()
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the System Object Search Page Filtered by Relation
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

/**
 * Process the System Object Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/update/:id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

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
    //get the required fields
    $requiredFields = $schema->getRequiredFieldNames();

    //for each field
    foreach($fields as $name => $field) {
        //if the field is invalid
        if(in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        if(//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }

        if(//if this field is required
            in_array($name, $requiredFields)
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            //set the default
            $request->setStage($name, null);
            continue;
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //if the event returned an error
    if($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/object/%s/update/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        );

        //this is for flexibility
        if($request->hasStage('route')) {
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
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    //add a flash
    cradle('global')->flash(sprintf(
        '%s was Updated', 'success',
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
$cradle->get('/admin/system/object/:schema/remove/:id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

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
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    if($response->isError()) {
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
$cradle->get('/admin/system/object/:schema/restore/:id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

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
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

    if($response->isError()) {
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
$cradle->post('/admin/system/object/:schema/import', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for store
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $schema = SystemSchema::i($request->getStage('schema'));

    //----------------------------//
    // 3. Process Request
    //get schema data
    cradle()->trigger('system-object-import', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //redirect
    $redirect = sprintf(
        '/admin/system/object/%s/search',
        $schema->getName()
    );

    //if the import event returned errors
    if($response->isError()) {
        $errors = [];
        //loop through each row
        foreach($response->getValidation() as $i => $validation) {
            //and loop through each error
            foreach ($validation as $key => $error) {
                //add the error
                $errors[] = sprintf('ROW %s - %s: %s', $i, $key, $error);
            }
        }

        //set the flash
        cradle('global')->flash(
            $response->getMessage(),
            'error',
            $errors
        );

        //redirect
        cradle('global')->redirect($redirect);
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

    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process Object Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/system/object/:schema/export/:type', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for store
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //get schema data
    $schema = SystemSchema::i($request->getStage('schema'));

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = $schema->getFilterableFieldNames();

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        $sortable = $schema->getSortableFieldNames();

        foreach($request->getStage('order') as $key => $value) {
            if(!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('system-object-search', $request, $response);

    //get the output type
    $type = $request->getStage('type');
    //get the rows
    $rows = $response->getResults('rows');
    //determine the filename
    $filename = $schema->getPlural() . '-' . date('Y-m-d');

    //record logs
    cradle()->log(
        sprintf(
            '%s was Exported',
            $schema->getPlural()
        ),
        $request,
        $response
    );

    //if the output type is csv
    if($type === 'csv') {
        //if there are no rows
        if(empty($rows)) {
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
        foreach($rows as $row) {
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
    if($type === 'xml') {
        //recursive xml parser
        $toXml = function($array, $xml) use (&$toXml) {
            //for each array
            foreach($array as $key => $value) {
                //if the value is an array
                if(is_array($value)) {
                    //if the key is not a number
                    if(!is_numeric($key)) {
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
