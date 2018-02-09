<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Object\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Object
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Object_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Object\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Singular is required', $actual['object_singular']);
        $this->assertEquals('Plural is required', $actual['object_plural']);
        $this->assertEquals('Keyword is required', $actual['object_key']);
    }

    /**
     * @covers Cradle\Module\Object\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['object_id']);
    }
}
