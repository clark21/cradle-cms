<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Schema as SystemSchema;

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

    cradle()->trigger('system-schema-detail', $request, $response);

    $schema = SystemSchema::i($response->getResults());

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

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-user-create page-admin';
    $data['title'] = cradle('global')->translate('Create System Object');
    $body = cradle('/app/admin')->template('user/form', $data);

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

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('user-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/user/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-user-update page-admin';
    $data['title'] = cradle('global')->translate('Updating System Object');
    $body = cradle('/app/admin')->template('user/form', $data);

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

    //user_slug is disallowed
    $request->removeStage('user_slug');

    //if user_meta has no value make it null
    if ($request->hasStage('user_meta') && !$request->getStage('user_meta')) {
        $request->setStage('user_meta', null);
    }

    //if user_files has no value make it null
    if ($request->hasStage('user_files') && !$request->getStage('user_files')) {
        $request->setStage('user_files', null);
    }

    //user_type is disallowed
    $request->removeStage('user_type');

    //user_flag is disallowed
    $request->removeStage('user_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/user/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('System Object was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/user/search');
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

    //user_slug is disallowed
    $request->removeStage('user_slug');

    //if user_meta has no value make it null
    if ($request->hasStage('user_meta') && !$request->getStage('user_meta')) {
        $request->setStage('user_meta', null);
    }

    //if user_files has no value make it null
    if ($request->hasStage('user_files') && !$request->getStage('user_files')) {
        $request->setStage('user_files', null);
    }

    //user_type is disallowed
    $request->removeStage('user_type');

    //user_flag is disallowed
    $request->removeStage('user_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/user/update/' . $request->getStage('user_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('System Object was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/user/search');
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
