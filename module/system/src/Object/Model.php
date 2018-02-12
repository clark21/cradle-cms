<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\System\Object;

use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Helper\InstanceTrait;

/**
 * Formatter layer
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Model
{
    use InstanceTrait;

    /**
     * @var SystemSchema|null $schema
     */
    protected $schema = null;

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     */
    public function __construct(SystemSchema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns a service. To prevent having to define a method per
     * service, instead we roll everything into one function
     *
     * @param *string $name
     * @param string  $key
     *
     * @return object
     */
    public function service($name, $key = 'main')
    {
        return Service::get($name, $key)->setSchema($this);
    }

    /**
     * Returns the formatter
     *
     * @return Formatter
     */
    public function formatter()
    {
        return Formatter::i($this);
    }

    /**
     * Returns the validator
     *
     * @return Validator
     */
    public function validator()
    {
        return Validator::i($this);
    }
}
