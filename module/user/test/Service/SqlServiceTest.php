<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\User\Service;

/**
 * SQL service test
 * User Model Test
 *
 * @vendor   Acme
 * @package  User
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_User_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\User\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'user_name' => 'John Doe',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'user_id' => $id,
            'user_name' => 'John Doe',
        ]);

        $this->assertEquals($id, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::linkComment
     */
    public function testLinkComment()
    {
        $actual = $this->object->linkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkComment
     */
    public function testUnlinkComment()
    {
        $actual = $this->object->unlinkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkComment
     */
    public function testUnlinkAllComment()
    {
        $actual = $this->object->unlinkAllComment(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
    }
    

    /**
     * @covers Cradle\Module\User\Service\SqlService::linkAddress
     */
    public function testLinkAddress()
    {
        $actual = $this->object->linkAddress(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkAddress
     */
    public function testUnlinkAddress()
    {
        $actual = $this->object->unlinkAddress(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkAddress
     */
    public function testUnlinkAllAddress()
    {
        $actual = $this->object->unlinkAllAddress(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
    }
    

    /**
     * @covers Cradle\Module\User\Service\SqlService::linkHistory
     */
    public function testLinkHistory()
    {
        $actual = $this->object->linkHistory(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkHistory
     */
    public function testUnlinkHistory()
    {
        $actual = $this->object->unlinkHistory(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkHistory
     */
    public function testUnlinkAllHistory()
    {
        $actual = $this->object->unlinkAllHistory(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
    }
    

    /**
     * @covers Cradle\Module\User\Service\SqlService::linkUser
     */
    public function testLinkUser()
    {
        $actual = $this->object->linkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkUser
     */
    public function testUnlinkUser()
    {
        $actual = $this->object->unlinkUser(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
        $this->assertEquals(999, $actual['user_id']);
    }

    /**
     * @covers Cradle\Module\User\Service\SqlService::unlinkUser
     */
    public function testUnlinkAllUser()
    {
        $actual = $this->object->unlinkAllUser(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['user_id']);
    }
    
}
