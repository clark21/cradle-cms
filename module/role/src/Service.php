<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Role;

use Cradle\Module\Role\Service\SqlService;
use Cradle\Module\Role\Service\RedisService;
use Cradle\Module\Role\Service\ElasticService;

use Cradle\Module\System\Utility\Service\NoopService;
use Cradle\Module\System\Utility\ServiceInterface;

/**
 * Service layer
 *
 * @vendor   Acme
 * @package  role
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Service implements ServiceInterface
{
    /**
     * Returns a service. To prevent having to define a method per
     * service, instead we roll everything into one function
     *
     * @param *string $name
     * @param string  $key
     *
     * @return object
     */
    public static function get($name, $key = 'main')
    {
        if (in_array($name, ['sql', 'redis', 'elastic'])) {
            $resource = cradle()->package('global')->service($name . '-' . $key);

            if ($resource) {
                if ($name === 'sql') {
                    return new SqlService($resource);
                }

                if ($name === 'redis') {
                    return new RedisService($resource);
                }

                if ($name === 'elastic') {
                    return new ElasticService($resource);
                }
            }
        }

        return new NoopService();
    }
}
