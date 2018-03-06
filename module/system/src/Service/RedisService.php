<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\System\Service;

use Cradle\Module\System\Service;
use Cradle\Module\System\Schema as SystemSchema;

use Predis\Client as Resource;

use Cradle\Module\System\Utility\Service\RedisServiceInterface;
use Cradle\Module\System\Utility\Service\AbstractRedisService;

/**
 * Object Redis Service
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class RedisService extends AbstractRedisService implements RedisServiceInterface
{
    /**
     * @const CACHE_SEARCH Cache search key
     */
    const CACHE_SEARCH = 'core-object-search';

    /**
     * @const CACHE_DETAIL Cache detail key
     */
    const CACHE_DETAIL = 'core-object-detail';

    /**
     * @var SystemSchema|null $schema
     */
    protected $schema = null;

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

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     *
     * @return SqlService
     */
    public function setSchema(SystemSchema $schema)
    {
        $this->schema = $schema;
        return $this;
    }
}
