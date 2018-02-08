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
 * @package  Node
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Node_EventsTest extends PHPUnit_Framework_TestCase
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
     * node-create
     *
     * @covers Cradle\Module\Node\Validator::getCreateErrors
     * @covers Cradle\Module\Node\Validator::getOptionalErrors
     * @covers Cradle\Module\Node\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testNodeCreate()
    {
        $this->request->setStage([
            'node_title' => 'Foobar Title',
            'node_slug' => 'a-Good-slug_1',
            'user_id' => 1,
        ]);

        cradle()->trigger('node-create', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('node_title'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('node_slug'));
        self::$id = $this->response->getResults('node_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * node-detail
     *
     * @covers Cradle\Module\Node\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testNodeDetail()
    {
        $this->request->setStage('node_id', 1);

        cradle()->trigger('node-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('node_id'));
    }

    /**
     * node-remove
     *
     * @covers Cradle\Module\Node\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Node\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testNodeRemove()
    {
        $this->request->setStage('node_id', self::$id);

        cradle()->trigger('node-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('node_id'));
    }

    /**
     * node-restore
     *
     * @covers Cradle\Module\Node\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Node\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testNodeRestore()
    {
        $this->request->setStage('node_id', 581);

        cradle()->trigger('node-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('node_id'));
        $this->assertEquals(1, $this->response->getResults('node_active'));
    }

    /**
     * node-search
     *
     * @covers Cradle\Module\Node\Service\SqlService::search
     * @covers Cradle\Module\Node\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testNodeSearch()
    {
        cradle()->trigger('node-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'node_id'));
    }

    /**
     * node-update
     *
     * @covers Cradle\Module\Node\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Node\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testNodeUpdate()
    {
        $this->request->setStage([
            'node_id' => self::$id,
            'node_title' => 'Foobar Title',
            'node_slug' => 'a-Good-slug_1',
            'user_id' => 1,
        ]);

        cradle()->trigger('node-update', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('node_title'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('node_slug'));
        $this->assertEquals(self::$id, $this->response->getResults('node_id'));
    }
}
