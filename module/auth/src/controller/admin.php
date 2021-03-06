<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Auth Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'auth_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('auth-search', $request, $response);

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-auth-search page-admin';
    $data['title'] = cradle('global')->translate('Authentications');
    $body = cradle('/module/auth')->template('search', $data);

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
 * Render the Auth Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

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
        $request->setStage(
            'auth_id',
            $request->getStage('copy')
        );

        //get the original table row
        cradle()->trigger('auth-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'error');
            return cradle('global')->redirect('/admin/auth/search');
        }

        //pass the item to the template
        $data['item'] = $response->getResults();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-auth-create page-admin';
    $data['title'] = cradle('global')->translate('Create Authentication');
    $body = cradle('/module/auth')->template('form', $data);


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
 * Render the Auth Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // get auth id
    $authId = $request->getStage('id');

    $request->setStage('auth_id', $authId);

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('auth-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/auth/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-auth-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Authentication');
    $body = cradle('/module/auth')->template('form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Process the Auth Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //determine route
    $route = '/admin/auth/search';

    //this is for flexibility
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    $action = $request->getStage('bulk-action');
    $ids = $request->getStage('auth_id');

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
                cradle()->trigger('auth-remove', $request, $response);
                break;
            case 'restore':
                cradle()->trigger('auth-restore', $request, $response);
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
                    'Auth #%s %s',
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
    $redirect = '/admin/auth/search';

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
 * Process the Auth Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //if auth_password has no value make it null
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->setStage('auth_password', null);
    }

    //auth_type is disallowed
    $request->removeStage('auth_type');

    //auth_flag is disallowed
    $request->removeStage('auth_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //determine route
        $route = '/admin/auth/create';

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log(
        sprintf(
            'Auth %s is created',
            $request->getStage('auth_slug')
        ),
        $request,
        $response
    );

    //redirect
    $redirect = '/admin/auth/search';

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
        'Auth %s is created',
        $request->getStage('user_slug')
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the Auth Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // get auth id
    $authId = $request->getStage('id');

    $request->setStage('auth_id', $authId);

    //if auth_password has no value make it null
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->setStage('auth_password', null);
    }

    //auth_type is disallowed
    $request->removeStage('auth_type');

    //auth_flag is disallowed
    $request->removeStage('auth_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //determine route
        $route = '/admin/auth/update';

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log(
        sprintf(
            'Auth #%s is updated',
            $request->getStage('user_id')
        ),
        $request,
        $response
    );

    //redirect
    $redirect = '/admin/auth/search';

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
        'Auth #%s is updated',
        $request->getStage('auth_id')
    ));

    cradle('global')->redirect($redirect);
});

/**
 * Process the Auth Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/remove/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // get auth id
    $authId = $request->getStage('id');

    $request->setStage('auth_id', $authId);
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    $redirect = '/admin/auth/search';

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
        $message = cradle('global')->translate('Auth was Removed');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                'Auth #%s removed',
                $request->getStage('auth_id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Auth Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/restore/:id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // get auth id
    $authId = $request->getStage('id');

    $request->setStage('auth_id', $authId);
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    $redirect = '/admin/auth/search';

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
        $message = cradle('global')->translate('Auth was Restored');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log(
            sprintf(
                'Auth #%s restored',
                $request->getStage('auth_id')
            ),
            $request,
            $response
        );
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process Auth Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/import', function ($request, $response) {
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
    //----------------------------//
    // 3. Process Request
    //get schema data
    cradle()->trigger('auth-import', $request, $response);

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
        'Auths was Imported',
        $request,
        $response
    );

    //add a flash
    $message = cradle('global')->translate('Auths was Imported');

    //Set JSON Content
    return $response->setContent(json_encode([
        'error' => false,
        'message' => $message
    ]));
});

/**
 * Process Auth Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/export/:type', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // set redirect
    $request->setStage('redirect', '/admin/auth/search');

    if (!cradle('/module/role')->hasPermissions($request, $response)) {
        return;
    }

    //record logs
    cradle()->log(
        'Auths was Exported',
        $request,
        $response
    );

    //----------------------------//
    // 2. Prepare Data
    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'auth_id',
            'auth_slug',
            'profile_name'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'auth_slug',
            'profile_name'
        ];

        foreach ($request->getStage('order') as $key => $value) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('auth-search', $request, $response);

    //get the output type
    $type = $request->getStage('type');
    //get the rows
    $rows = $response->getResults('rows');
    //determine the filename
    $filename = 'Auths-' . date('Y-m-d');

    //if the output type is csv
    if ($type === 'csv') {
        //if there are no rows
        if (empty($rows)) {
            //at least give the headers
            $rows = [
                'auth_id',
                'auth_slug',
                'profile_name',
                'auth_type',
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
        foreach ($rows as $row) {
            $row['profile_meta'] = !empty($row['profile_meta']) ? json_encode($row['profile_meta']) : '';
            $row['profile_files'] = !empty($row['profile_files']) ? json_encode($row['profile_files']) : '';

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
        $root = "<?xml version=\"1.0\"?>\n<auth></auth>";

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

/**
 * Process the Verification Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/activate/:auth_id/:hash', function ($request, $response) {
    //get the detail
    cradle()->trigger('auth-detail', $request, $response);

    //form hash
    $authId = $response->getResults('auth_id');
    $authUpdated = $response->getResults('auth_updated');
    $hash = md5($authId.$authUpdated);

    //check the verification hash
    if ($hash !== $request->getStage('hash')) {
        cradle('global')->flash('Invalid verification. Try again.', 'danger');
        return cradle('global')->redirect('/verify');
    }

    //activate
    $request->setStage('auth_active', 1);

    //trigger the job
    cradle()->trigger('auth-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash('Invalid verification. Try again.', 'danger');
        return cradle('global')->redirect('/verify');
    }

    //it was good
    //add a flash
    cradle('global')->flash('Activation Successful', 'success');

    //redirect
    cradle('global')->redirect('/login');
});
