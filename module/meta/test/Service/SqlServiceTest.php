<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Meta\Service;

/**
 * SQL service test
 * Meta Model Test
 *
 * @vendor   Acme
 * @package  Meta
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Meta_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'meta_singular' => 'Foobar Singular',
            'meta_plural' => 'Foobar Plural',
            'meta_key' => 'a-Good-slug_1',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['meta_id']);
    }

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['meta_id']);
    }

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['meta_id']);
    }

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'meta_id' => $id,
            'meta_singular' => 'Foobar Singular',
            'meta_plural' => 'Foobar Plural',
            'meta_key' => 'a-Good-slug_1',
        ]);

        $this->assertEquals($id, $actual['meta_id']);
    }

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::exists
     */
    public function testExists()
    { 
        $actual = $this->object->exists('a-Good-slug_1');
        // it returns a boolean so we're expecting it to be true because
        // the slug provided is saved in the database
        $this->assertTrue($actual);
    }
    

    /**
     * @covers Cradle\Module\Meta\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['meta_id']);
    }
}
