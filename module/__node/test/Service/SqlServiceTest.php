<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Node\Service;

/**
 * SQL service test
 * Node Model Test
 *
 * @vendor   Acme
 * @package  Node
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Node_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Node\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'node_title' => 'Foobar Title',
            'node_slug' => 'a-Good-slug_1',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'node_id' => $id,
            'node_title' => 'Foobar Title',
            'node_slug' => 'a-Good-slug_1',
        ]);

        $this->assertEquals($id, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::exists
     */
    public function testExists()
    { 
        $actual = $this->object->exists('a-Good-slug_1');
        // it returns a boolean so we're expecting it to be true because
        // the slug provided is saved in the database
        $this->assertTrue($actual);
    }
    

    /**
     * @covers Cradle\Module\Node\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::linkUser
     */
    public function testLinkUser()
    {
        $actual = $this->object->linkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['node_id']);
        $this->assertEquals(999, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::unlinkUser
     */
    public function testUnlinkUser()
    {
        $actual = $this->object->unlinkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['node_id']);
        $this->assertEquals(999, $actual['user_id']);
    }
    

    /**
     * @covers Cradle\Module\Node\Service\SqlService::linkNode
     */
    public function testLinkNode()
    {
        $actual = $this->object->linkNode(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['node_id']);
        $this->assertEquals(999, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::unlinkNode
     */
    public function testUnlinkNode()
    {
        $actual = $this->object->unlinkNode(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['node_id']);
        $this->assertEquals(999, $actual['node_id']);
    }

    /**
     * @covers Cradle\Module\Node\Service\SqlService::unlinkNode
     */
    public function testUnlinkAllNode()
    {
        $actual = $this->object->unlinkAllNode(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['node_id']);
    }
    
}
