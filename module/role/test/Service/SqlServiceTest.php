<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\Role\Service;

/**
 * SQL service test
 * Role Model Test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Role_Service_SqlServiceTest extends TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Role\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'role_name' => 'Apple',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'role_id' => $id,
            'role_name' => 'Apple',
        ]);

        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::linkHistory
     */
    public function testLinkHistory()
    {
        $actual = $this->object->linkHistory(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['role_id']);
        $this->assertEquals(999, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::unlinkHistory
     */
    public function testUnlinkHistory()
    {
        $actual = $this->object->unlinkHistory(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['role_id']);
        $this->assertEquals(999, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::unlinkHistory
     */
    public function testUnlinkAllHistory()
    {
        $actual = $this->object->unlinkAllHistory(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['role_id']);
    }

}
