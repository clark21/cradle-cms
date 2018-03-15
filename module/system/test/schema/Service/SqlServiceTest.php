<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\System\Service;

/**
 * SQL service test
 * Role Model Test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Schema_Service_SqlServiceTest extends TestCase
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
     * @covers Cradle\Module\System\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'role_name' => 'Moderator',
            'role_permissions' => json_encode([
                'path' => '/admin/role/*',
                'label' => 'Role Access',
                'method' => 'all'
            ])
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
}
