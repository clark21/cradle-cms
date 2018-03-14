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
 * @package  History
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_History_EventsTest extends TestCase
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
     * history-create
     *
     * @covers Cradle\Module\History\Validator::getCreateErrors
     * @covers Cradle\Module\History\Validator::getOptionalErrors
     * @covers Cradle\Module\History\Service\SqlService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::createDetail
     */
    public function testHistoryCreate()
    {
        $this->request->setStage([
            'history_activity' => 'admin created a cashback',
            'profile_id' => 1,
        ]);

        cradle()->trigger('history-create', $this->request, $this->response);
        $this->assertEquals('admin created a cashback', $this->response->getResults('history_activity'));
        self::$id = $this->response->getResults('history_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * history-detail
     *
     * @covers Cradle\Module\History\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     */
    public function testHistoryDetail()
    {
        $this->request->setStage('history_id', 1);

        cradle()->trigger('history-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('history_id'));
    }

    /**
     * history-remove
     *
     * @covers Cradle\Module\History\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\History\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testHistoryRemove()
    {
        $this->request->setStage('history_id', self::$id);

        cradle()->trigger('history-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('history_id'));
    }

    /**
     * history-restore
     *
     * @covers Cradle\Module\History\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\History\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testHistoryRestore()
    {
        $this->request->setStage('history_id', self::$id);

        cradle()->trigger('history-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('history_id'));
        $this->assertEquals(1, $this->response->getResults('history_active'));
    }

    /**
     * history-search
     *
     * @covers Cradle\Module\History\Service\SqlService::search
     * @covers Cradle\Module\History\Service\ElasticService::search
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getSearch
     */
    public function testHistorySearch()
    {
        cradle()->trigger('history-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'history_id'));
    }

    /**
     * history-update
     *
     * @covers Cradle\Module\History\Service\SqlService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\History\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testHistoryUpdate()
    {
        $this->request->setStage([
            'history_id' => self::$id,
            'history_activity' => 'admin created a cashback',
            'profile_id' => 1,
        ]);

        cradle()->trigger('history-update', $this->request, $this->response);
        $this->assertEquals('admin created a cashback', $this->response->getResults('history_activity'));
        $this->assertEquals(self::$id, $this->response->getResults('history_id'));
    }
}
