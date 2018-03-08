<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\Auth\Service;

use Cradle\Module\Auth\Service\SqlService;
use Cradle\Module\Auth\Service\RedisService;
use Cradle\Module\Auth\Service\ElasticService;
use Cradle\Module\System\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Auth_ServiceTest extends TestCase
{
    /**
     * @covers Cradle\Module\Auth\Service::get
     */
    public function testGet()
    {
        $actual = Service::get('sql');
        $this->assertTrue($actual instanceof SqlService || $actual instanceof NoopService);

        $actual = Service::get('redis');
        $this->assertTrue($actual instanceof RedisService || $actual instanceof NoopService);

        $actual = Service::get('elastic');
        $this->assertTrue($actual instanceof ElasticService || $actual instanceof NoopService);
    }
}
