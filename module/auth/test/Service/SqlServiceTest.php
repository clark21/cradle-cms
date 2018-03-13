<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\Auth\Service;

/**
 * SQL service test
 * Auth Model Test
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Auth_Service_SqlServiceTest extends TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'auth_slug' => 'jane@doe.com',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['auth_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'auth_id' => $id,
            'auth_slug' => 'jane@doe.com',
        ]);

        $this->assertEquals($id, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::exists
     */
    public function testExists()
    {
        $actual = $this->object->exists('jane@doe.com');
        // it returns a boolean so we're expecting it to be true because
        // the slug provided is saved in the database
        $this->assertTrue($actual);
    }


    /**
     * @covers Cradle\Module\Auth\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['auth_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Auth\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['auth_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

}
