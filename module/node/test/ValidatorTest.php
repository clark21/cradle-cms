<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Node\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Node
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Node_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Node\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Title is required', $actual['node_title']);
        $this->assertEquals('Slug is required', $actual['node_slug']);
    }

    /**
     * @covers Cradle\Module\Node\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['node_id']);
    }
}
