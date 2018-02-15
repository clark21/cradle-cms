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
    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    $schemaResponse = Response::i()->load();
    cradle()->trigger('system-schema-detail', $request, $schemaResponse);
    $schema = SystemSchema::i($schemaResponse->getResults());

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = $schema->getFilterable();

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        $sortable = $schema->getSortable();

        foreach($request->getStage('order') as $key => $value) {
            if(!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('system-object-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());
    $data['schema'] = [
        'name' => $schema->getTableName(),
        'singular' => $schema->getSingular(),
        'plural' => $schema->getPlural(),
        'primary' => $schema->getPrimary(),
        'active' => $schema->getActive(),
        'listable' => $schema->getListable(),
        'fields' => $schema->getFields(),
        'sortable' => $schema->getSortable(),
    ];

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-object-search page-admin';
    $data['title'] = cradle('global')->translate($schema->getPlural());

    //I need a better when
    cradle('global')
        ->handlebars()
        ->registerHelper('when', function(...$args) {
            //$value1, $operator, $value2, $options
            $options = array_pop($args);
            $value2 = array_pop($args);
            $operator = array_pop($args);

            $value1 = array_shift($args);

            foreach($args as $arg) {
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
        })
        ;



    $body = cradle('/module/system')->template('object/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

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
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $schemaResponse = Response::i()->load();
    cradle()->trigger('system-schema-detail', $request, $schemaResponse);
    $schema = SystemSchema::i($schemaResponse->getResults());

    $data['schema'] = [
        'name' => $schema->getTableName(),
        'singular' => $schema->getSingular(),
        'fields' => $schema->getFields(),
        'files' => $schema->getFiles()
    ];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-object-create page-admin';
    $data['title'] = cradle('global')->translate('Create %s', $data['schema']['singular']);

    //I need a better when
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

    $body = cradle('/module/system')->template('object/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

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
    $data = ['item' => $request->getPost()];

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //if no item
    if(empty($data['item'])) {
        $request->setStage(
            $request->getStage('schema') . '_id',
            $request->getStage('id')
        );

        cradle()->trigger('system-object-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/system/object/'. $request->getStage('schema') .'/search');
        }

        $data['item'] = $response->getResults();
    }

    $schemaResponse = Response::i()->load();
    cradle()->trigger('system-schema-detail', $request, $schemaResponse);
    $schema = SystemSchema::i($schemaResponse->getResults());

    $data['schema'] = [
        'name' => $schema->getTableName(),
        'singular' => $schema->getSingular(),
        'fields' => $schema->getFields(),
        'files' => $schema->getFiles()
    ];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-object-update page-admin';
    $data['title'] = cradle('global')->translate(
        'Updating %s',
        $data['schema']['singular']
    );

    //I need a better when
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

    $body = cradle('/module/system')->template('object/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

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
    $schemaResponse = Response::i()->load();
    cradle()->trigger('system-schema-detail', $request, $schemaResponse);
    $schema = SystemSchema::i($schemaResponse->getResults());

    $fields = $schema->getFields();

    $invalidTypes = ['none', 'active', 'created', 'updated'];
    $requiredFields = $schema->getRequired();

    foreach($fields as $name => $field) {
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

    if(//special shot out to user
        in_array('user', $schema->getRelations())
        && !$request->hasStage('user_id')
    ) {
        $request->setStage('user_id', $request->getSession('me', 'user_id'));
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('system-object-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/admin/system/object/'. $request->getStage('schema') . '/create',
            $request,
            $response
        );
    }

    //it was good
    //add a flash
    cradle('global')->flash($schema->getSingular() . ' was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/system/object/'. $request->getStage('schema') . '/search');
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
    $request->setStage(
        $request->getStage('schema') . '_id',
        $request->getStage('id')
    );

    $schemaResponse = Response::i()->load();
    cradle()->trigger('system-schema-detail', $request, $schemaResponse);
    $schema = SystemSchema::i($schemaResponse->getResults());

    $fields = $schema->getFields();

    $invalidTypes = ['none', 'active', 'created', 'updated'];
    $requiredFields = $schema->getRequired();

    foreach($fields as $name => $field) {
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
    if($response->isError()) {
        $route = '/admin/system/object/'. $request->getStage('schema') .'/update/' . $request->getStage('id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash($schema->getSingular() . ' was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/system/object/'. $request->getStage('schema') .'/search');
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
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('System Object was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/user/search');
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
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('System Object was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/admin/user/search');
});
