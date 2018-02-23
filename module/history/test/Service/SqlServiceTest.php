<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\History\Service;

/**
 * SQL service test
 * History Model Test
 *
 * @vendor   Acme
 * @package  History
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_History_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\History\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'history_activity' => 'admin created a cashback',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'history_id' => $id,
            'history_activity' => 'admin created a cashback',
        ]);

        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkUser
     */
    public function testLinkUser()
    {
        $actual = $this->object->linkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkUser
     */
    public function testUnlinkUser()
    {
        $actual = $this->object->unlinkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['user_id']);
    }
    
}
