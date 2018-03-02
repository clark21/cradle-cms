<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the User Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
            'user_active'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('user-search', $request, $response);

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
    }

    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-user-search page-admin';
    $data['title'] = cradle('global')->translate('Users');
    $body = cradle('/app/admin')->template('user/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
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
 * Render the User Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/create', function($request, $response) {
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

    //for ?copy=1 functionality
    if (empty($data['item']) && is_numeric($request->getStage('copy'))) {
        //table_id, 1 for example
        $request->setStage('user_id',
            $request->getStage('copy')
        );

        //get the original table row
        cradle()->trigger('user-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect('/admin/user/search');
        }

        //pass the item to the template
        $data['item'] = $response->getResults();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-user-create page-admin';
    $data['title'] = cradle('global')->translate('Create User');
    $body = cradle('/app/admin')->template('user/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
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
 * Render the User Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/update/:user_id', function($request, $response) {
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
    $data['title'] = cradle('global')->translate('Updating User');
    $body = cradle('/app/admin')->template('user/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

    //Render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Process the User Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //determine route
    $route = '/admin/user/search';

    //this is for flexibility
    if($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    $action = $request->getStage('bulk-action');
    $ids = $request->getStage('user_id');

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
        $request->setStage(user_id, $id);

        //case for actions
        switch ($action) {
            case 'remove':
                cradle()->trigger('user-remove', $request, $response);
                break;
            case 'restore':
                cradle()->trigger('user-restore', $request, $response);
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
                    'User #%s %s',
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
    $redirect = '/admin/user/search';

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
 * Process the User Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/create', function($request, $response) {
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
        //determine route
        $route = '/admin/user/create';

        //this is for flexibility
        if($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log(
        sprintf(
            'User %s is created',
            $request->getStage('user_slug')
        ),
        $request,
        $response
    );

    //redirect
    $redirect = '/admin/user/search';

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
        'User %s is created',
        $request->getStage('user_slug')
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the User Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/update/:user_id', function($request, $response) {
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
        //determine route
        $route = '/admin/user/update';

        //this is for flexibility
        if($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log(
        sprintf(
            'User #%s is updated',
            $request->getStage('user_id')
        ),
        $request,
        $response
    );

    //redirect
    $redirect = '/admin/user/search';

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
        'User #%s is updated',
        $request->getStage('user_id')
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the User Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/remove/:user_id', function($request, $response) {
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
    $redirect = '/admin/user/search';

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
        $message = cradle('global')->translate('User was Removed');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                'User #%s removed',
                $request->getStage('user_id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the User Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/restore/:user_id', function($request, $response) {
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
    $redirect = '/admin/user/search';

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
        $message = cradle('global')->translate('User was Restored');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                'User #%s restored',
                $request->getStage('user_id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process User Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/import', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for store
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    //get schema data
    cradle()->trigger('user-import', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    //redirect
    $redirect = '/admin/user/search';

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
        return cradle('global')->redirect($redirect);
    }

    //record logs
    cradle()->log('Users was Imported',
        $request,
        $response
    );

    //add a flash
    $message = cradle('global')->translate('Users was Imported');

    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process User Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/export/:type', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for store
    cradle('global')->requireLogin('admin');

    //record logs
    cradle()->log('Users was Exported',
        $request,
        $response
    );

    //----------------------------//
    // 2. Prepare Data
    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
            'user_id',
            'user_name',
            'user_slug'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('order'))) {
        $sortable = [
            'user_name'
        ];

        foreach($request->getStage('order') as $key => $value) {
            if(!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('user-search', $request, $response);

    //get the output type
    $type = $request->getStage('type');
    //get the rows
    $rows = $response->getResults('rows');
    //determine the filename
    $filename = 'Users-' . date('Y-m-d');

    //if the output type is csv
    if($type === 'csv') {
        //if there are no rows
        if(empty($rows)) {
            //at least give the headers
            $rows = [
                'user_id',
                'user_name',
                'user_slug',
                'user_type',
            ];

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
            $row['user_meta'] = !empty($row['user_meta']) ? json_encode($row['user_meta']) : '';
            $row['user_files'] = !empty($row['user_files']) ? json_encode($row['user_files']) : '';

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
        $root = "<?xml version=\"1.0\"?>\n<user></user>";

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
