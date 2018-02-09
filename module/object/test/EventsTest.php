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
 * @package  Object
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Object_EventsTest extends PHPUnit_Framework_TestCase
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
     * object-create
     *
     * @covers Cradle\Module\Object\Validator::getCreateErrors
     * @covers Cradle\Module\Object\Validator::getOptionalErrors
     * @covers Cradle\Module\Object\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testObjectCreate()
    {
        $this->request->setStage([
            'object_singular' => 'Foobar Singular',
            'object_plural' => 'Foobar Plural',
            'object_key' => 'a-Good-slug_1',
        ]);

        cradle()->trigger('object-create', $this->request, $this->response);
        $this->assertEquals('Foobar Singular', $this->response->getResults('object_singular'));
        $this->assertEquals('Foobar Plural', $this->response->getResults('object_plural'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('object_key'));
        self::$id = $this->response->getResults('object_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * object-detail
     *
     * @covers Cradle\Module\Object\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testObjectDetail()
    {
        $this->request->setStage('object_id', 1);

        cradle()->trigger('object-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('object_id'));
    }

    /**
     * object-remove
     *
     * @covers Cradle\Module\Object\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Object\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testObjectRemove()
    {
        $this->request->setStage('object_id', self::$id);

        cradle()->trigger('object-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('object_id'));
    }

    /**
     * object-restore
     *
     * @covers Cradle\Module\Object\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Object\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testObjectRestore()
    {
        $this->request->setStage('object_id', 581);

        cradle()->trigger('object-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('object_id'));
        $this->assertEquals(1, $this->response->getResults('object_active'));
    }

    /**
     * object-search
     *
     * @covers Cradle\Module\Object\Service\SqlService::search
     * @covers Cradle\Module\Object\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testObjectSearch()
    {
        cradle()->trigger('object-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'object_id'));
    }

    /**
     * object-update
     *
     * @covers Cradle\Module\Object\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Object\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testObjectUpdate()
    {
        $this->request->setStage([
            'object_id' => self::$id,
            'object_singular' => 'Foobar Singular',
            'object_plural' => 'Foobar Plural',
            'object_key' => 'a-Good-slug_1',
        ]);

        cradle()->trigger('object-update', $this->request, $this->response);
        $this->assertEquals('Foobar Singular', $this->response->getResults('object_singular'));
        $this->assertEquals('Foobar Plural', $this->response->getResults('object_plural'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('object_key'));
        $this->assertEquals(self::$id, $this->response->getResults('object_id'));
    }
}
