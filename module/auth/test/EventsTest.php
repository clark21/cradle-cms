<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Auth_EventsTest extends TestCase
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Request $response
     */
    protected $response;

    /**
     * @var int $id
     */
    protected static $id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->request->load();
        $this->response->load();
    }

    /**
     * auth-create
     *
     * @covers Cradle\Module\Auth\Validator::getCreateErrors
     * @covers Cradle\Module\Auth\Validator::getOptionalErrors
     * @covers Cradle\Module\Auth\Service\SqlService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::createDetail
     */
    public function testAuthCreate()
    {
        $this->request->setStage([
            'auth_slug' => 'john@doe.com',
            'user_id' => 1,
        ]);

        cradle()->trigger('auth-create', $this->request, $this->response);
        $this->assertEquals('john@doe.com', $this->response->getResults('auth_slug'));
        self::$id = $this->response->getResults('auth_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * auth-detail
     *
     * @covers Cradle\Module\Auth\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     */
    public function testAuthDetail()
    {
        $this->request->setStage('auth_id', 1);

        cradle()->trigger('auth-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('auth_id'));
    }

    /**
     * auth-remove
     *
     * @covers Cradle\Module\Auth\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Auth\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAuthRemove()
    {
        $this->request->setStage('auth_id', self::$id);

        cradle()->trigger('auth-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('auth_id'));
    }

    /**
     * auth-restore
     *
     * @covers Cradle\Module\Auth\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Auth\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAuthRestore()
    {
        $this->request->setStage('auth_id', 581);

        cradle()->trigger('auth-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('auth_id'));
        $this->assertEquals(1, $this->response->getResults('auth_active'));
    }

    /**
     * auth-search
     *
     * @covers Cradle\Module\Auth\Service\SqlService::search
     * @covers Cradle\Module\Auth\Service\ElasticService::search
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getSearch
     */
    public function testAuthSearch()
    {
        cradle()->trigger('auth-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'auth_id'));
    }

    /**
     * auth-update
     *
     * @covers Cradle\Module\Auth\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Auth\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAuthUpdate()
    {
        $this->request->setStage([
            'auth_id' => self::$id,
            'auth_slug' => 'john@doe.com',
            'user_id' => 1,
        ]);

        cradle()->trigger('auth-update', $this->request, $this->response);
        $this->assertEquals('john@doe.com', $this->response->getResults('auth_slug'));
        $this->assertEquals(self::$id, $this->response->getResults('auth_id'));
    }
}
