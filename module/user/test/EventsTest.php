<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  User
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_User_EventsTest extends PHPUnit_Framework_TestCase
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
     * user-create
     *
     * @covers Cradle\Module\User\Validator::getCreateErrors
     * @covers Cradle\Module\User\Validator::getOptionalErrors
     * @covers Cradle\Module\User\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testUserCreate()
    {
        $this->request->setStage([
            'user_name' => 'John Doe',
        ]);

        cradle()->trigger('user-create', $this->request, $this->response);
        $this->assertEquals('John Doe', $this->response->getResults('user_name'));
        self::$id = $this->response->getResults('user_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * user-detail
     *
     * @covers Cradle\Module\User\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testUserDetail()
    {
        $this->request->setStage('user_id', 1);

        cradle()->trigger('user-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('user_id'));
    }

    /**
     * user-remove
     *
     * @covers Cradle\Module\User\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\User\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUserRemove()
    {
        $this->request->setStage('user_id', self::$id);

        cradle()->trigger('user-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('user_id'));
    }

    /**
     * user-restore
     *
     * @covers Cradle\Module\User\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\User\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUserRestore()
    {
        $this->request->setStage('user_id', 581);

        cradle()->trigger('user-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('user_id'));
        $this->assertEquals(1, $this->response->getResults('user_active'));
    }

    /**
     * user-search
     *
     * @covers Cradle\Module\User\Service\SqlService::search
     * @covers Cradle\Module\User\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testUserSearch()
    {
        cradle()->trigger('user-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'user_id'));
    }

    /**
     * user-update
     *
     * @covers Cradle\Module\User\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\User\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUserUpdate()
    {
        $this->request->setStage([
            'user_id' => self::$id,
            'user_name' => 'John Doe',
        ]);

        cradle()->trigger('user-update', $this->request, $this->response);
        $this->assertEquals('John Doe', $this->response->getResults('user_name'));
        $this->assertEquals(self::$id, $this->response->getResults('user_id'));
    }
}
