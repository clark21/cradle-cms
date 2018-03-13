<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\Auth\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Auth_ValidatorTest extends TestCase
{
    /**
     * @covers Cradle\Module\Auth\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);

        $this->assertEquals('Cannot be empty', $actual['auth_slug']);
        $this->assertEquals('Cannot be empty', $actual['auth_password']);
        $this->assertEquals('Cannot be empty', $actual['confirm']);

        $actual = Validator::getCreateErrors(['auth_slug' => 'john@doe.com']);
        $this->assertEquals('User Exists', $actual['auth_slug']);

        $actual = Validator::getCreateErrors([
            'auth_slug' => 'jane@doe.com',
            'auth_password' => '123abc',
            'confirm' => '123abcd',
        ]);

        $this->assertEquals('Passwords do not match', $actual['confirm']);
    }

    /**
     * @covers Cradle\Module\Auth\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['auth_id']);

        $actual = Validator::getUpdateErrors([
            'auth_slug' => '',
            'auth_id' => 1,
        ]);

        $this->assertEquals('Cannot be empty, if set', $actual['auth_slug']);

        $actual = Validator::getUpdateErrors([
            'auth_slug' => 'jane@doe.com',
            'auth_id' => 1,
        ]);

        $this->assertEquals('Already Taken', $actual['auth_slug']);
    }
}
