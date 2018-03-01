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

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
    }

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

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
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
                unset($query['order']);
                $query['order'][$key] = 'ASC';
            } else if($value === 'ASC') {
                unset($query['order']);
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

                $field['list']['name'] = $name;
                $field['list']['value'] = $row[$name];
                $columns[] = $options['fn']($field['list']);
            }

            return implode('', $columns);
        })
        ->registerHelper('get_suggestion', function($schema, $data) {
            return SystemSchema::i($schema)->getSuggestionFormat($data);
        })
        ->registerHelper('relation_primary', function($relation, $data) {
            if(isset($data[$relation['primary']])) {
                return $data[$relation['primary']];
            }
        })
        ->registerHelper('filtertoquery', function($key = null, $value = '') {
            $query = $_GET;
            $query['filter'][$key] = $value;
            return http_build_query($query);
        });

    // cradle()->inspect($data['schema']['relations']);exit;

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
        if($response->isError()) {
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
            $suggestion = '_' . $relation['primary2'];
            try {
                $data['item'][$suggestion] = SystemSchema::i($relation['name'])
                    ->getSuggestionFormat($data['item']);
            } catch(Exception $e) {}
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

    //pass suggestion title field for each relation to the template
    foreach ($data['schema']['relations'] as $name => $relation) {
        $data['schema']['relations'][$name]['suggestion_name'] = '_' . $relation['primary2'];
    }

    //if this is a relational process
    if ($request->hasStage('relation')) {
        //also pass the relation to the form
        $data['relation'] = $request->getStage('relation');
    }

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
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

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

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
            //redirect
            $redirect = sprintf(
                '/admin/system/object/%s/search',
                $request->getStage('schema')
            );

            //this is for flexibility
            if($request->hasStage('redirect_uri')) {
                $redirect = $request->getStage('redirect_uri');
            }

            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect($redirect);
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

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
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

    //pass suggestion title field for each relation to the template
    foreach ($data['schema']['relations'] as $name => $relation) {
        $data['schema']['relations'][$name]['suggestion_name'] = '_' . $relation['primary2'];
    }

    //determine valid relations
    $data['valid_relations'] = [];
    cradle()->trigger('system-schema-search', $request, $response);
    foreach($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
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
 * Process the System Object Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/system/object/:schema/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

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
    if($request->hasStage('route')) {
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

        if($response->isError()) {
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
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
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

    //for each field
    foreach($fields as $name => $field) {
        //if the field is invalid
        if(in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if no value
        if($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if(//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if(strtoupper($field['default']) === 'NOW()') {
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

    //for each field
    foreach($fields as $name => $field) {
        //if the field is invalid
        if(in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if no value
        if($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if(//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if(strtoupper($field['default']) === 'NOW()') {
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

    //if there is a specified redirect
    if($request->hasStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if($redirect === 'false') {
        return;
    }

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
