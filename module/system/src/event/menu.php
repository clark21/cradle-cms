<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Menu Get Record Count Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('menu-get-record-count', function ($request, $response) {
    // get navigation
    $navigation = $request->getStage('navigation');

    // get the schema name
    $schema = cradle('global')->config('services')['sql-main']['name'];

    // get table record count
    $recordCount = \Cradle\Module\System\Service::get('sql')
        ->getSchemaTableRecordCount($schema);

    // map navigation
    function map_navigation($navigation, $recordCount)
    {
        // iterate on each navigation
        foreach ($navigation as $key => $value) {
            // do we have child navigation?
            if (isset($value['children'])
            && is_array($value['children'])) {
                // recurse through child navigations
                $navigation[$key]['children'] = map_navigation($value['children'], $recordCount);
            }

            // iterate on each record count
            foreach ($recordCount as $count) {
                // build out the criteria
                $criteria = sprintf('/%s/search', $count['table_name']);

                // check the path based on criteria
                if (strpos($value['path'], $criteria) > 0) {
                    // set the record count
                    $navigation[$key]['records'] = $count['table_rows'];
                }
            }
        }

        return $navigation;
    }

    // map through navigation and set record count
    $navigation = map_navigation($navigation, $recordCount);

    // set response
    return $response->setResults($navigation);
});
