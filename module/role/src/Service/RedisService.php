<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Role\Service;

use Cradle\Module\Role\Service;

use Predis\Client as Resource;

use Cradle\Module\System\Utility\Service\RedisServiceInterface;
use Cradle\Module\System\Utility\Service\AbstractRedisService;

/**
 * Role Redis Service
 *
 * @vendor   Acme
 * @package  role
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class RedisService extends AbstractRedisService implements RedisServiceInterface
{
    /**
     * @const CACHE_SEARCH Cache search key
     */
    const CACHE_SEARCH = 'core-role-search';

    /**
     * @const CACHE_DETAIL Cache detail key
     */
    const CACHE_DETAIL = 'core-role-detail';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->sql = Service::get('sql');
        $this->elastic = Service::get('elastic');
    }
}
