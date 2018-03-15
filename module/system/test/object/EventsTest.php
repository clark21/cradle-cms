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
 * @package  Object
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Object_EventsTest extends TestCase
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
     * @covers Cradle\Module\Role\Validator::getCreateErrors
     * @covers Cradle\Module\Role\Validator::getOptionalErrors
     * @covers Cradle\Module\Role\Service\SqlService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::createDetail
     */
    public function testObjectCreate()
    {
        $this->request->setStage([
            'schema' => 'sample'
        ]);

        cradle()->trigger('system-schema-create', $this->request, $this->response);
        $this->assertEquals('sample', $this->response->getResults('role_name'));

        self::$id = $this->response->getResults('role_id');
        $this->assertTrue(is_numeric(self::$id));
    }
}
