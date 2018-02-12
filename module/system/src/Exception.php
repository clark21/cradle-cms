<?php //-->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\System;

use BaseException;

/**
 * Resolver exceptions
 *
 * @package  Cradle
 * @category Resolver
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Exception extends BaseException
{
    /**
     * @const string ERROR_NO_MODEL Error template
     */
    const ERROR_NO_SCHEMA = 'Schema is not loaded';

    /**
     * @const string ERROR_CONFIG_NOT_FOUND Error template
     */
    const ERROR_SCHEMA_NOT_FOUND = 'Could not find schema %s.';

    /**
     * Create a new exception for missing Schema
     *
     * @return Exception
     */
    public static function forNoSchema(): Exception
    {
        return new static(static::ERROR_NO_SCHEMA);
    }

    /**
     * Create a new exception for missing config
     *
     * @param *string $name
     *
     * @return ObjectException
     */
    public static function forSchemaNotFound(string $name): ObjectException
    {
        $message = sprintf(static::ERROR_SCHEMA_NOT_FOUND, $name);
        return new static($message);
    }
}
