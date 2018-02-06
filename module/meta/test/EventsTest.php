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
 * @package  Meta
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Meta_EventsTest extends PHPUnit_Framework_TestCase
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
     * meta-create
     *
     * @covers Cradle\Module\Meta\Validator::getCreateErrors
     * @covers Cradle\Module\Meta\Validator::getOptionalErrors
     * @covers Cradle\Module\Meta\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testMetaCreate()
    {
        $this->request->setStage([
            'meta_singular' => 'Foobar Singular',
            'meta_plural' => 'Foobar Plural',
            'meta_key' => 'a-Good-slug_1',
        ]);

        cradle()->trigger('meta-create', $this->request, $this->response);
        $this->assertEquals('Foobar Singular', $this->response->getResults('meta_singular'));
        $this->assertEquals('Foobar Plural', $this->response->getResults('meta_plural'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('meta_key'));
        self::$id = $this->response->getResults('meta_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * meta-detail
     *
     * @covers Cradle\Module\Meta\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testMetaDetail()
    {
        $this->request->setStage('meta_id', 1);

        cradle()->trigger('meta-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('meta_id'));
    }

    /**
     * meta-remove
     *
     * @covers Cradle\Module\Meta\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Meta\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testMetaRemove()
    {
        $this->request->setStage('meta_id', self::$id);

        cradle()->trigger('meta-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('meta_id'));
    }

    /**
     * meta-restore
     *
     * @covers Cradle\Module\Meta\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Meta\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testMetaRestore()
    {
        $this->request->setStage('meta_id', 581);

        cradle()->trigger('meta-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('meta_id'));
        $this->assertEquals(1, $this->response->getResults('meta_active'));
    }

    /**
     * meta-search
     *
     * @covers Cradle\Module\Meta\Service\SqlService::search
     * @covers Cradle\Module\Meta\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testMetaSearch()
    {
        cradle()->trigger('meta-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'meta_id'));
    }

    /**
     * meta-update
     *
     * @covers Cradle\Module\Meta\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Meta\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testMetaUpdate()
    {
        $this->request->setStage([
            'meta_id' => self::$id,
            'meta_singular' => 'Foobar Singular',
            'meta_plural' => 'Foobar Plural',
            'meta_key' => 'a-Good-slug_1',
        ]);

        cradle()->trigger('meta-update', $this->request, $this->response);
        $this->assertEquals('Foobar Singular', $this->response->getResults('meta_singular'));
        $this->assertEquals('Foobar Plural', $this->response->getResults('meta_plural'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('meta_key'));
        $this->assertEquals(self::$id, $this->response->getResults('meta_id'));
    }
}
