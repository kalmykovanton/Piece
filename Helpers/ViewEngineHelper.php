<?php

namespace Piece\Helpers;

use InvalidArgumentException;

/**
 * Class ViewEngineHelper.
 * @package Piece\Helpers
 */
trait ViewEngineHelper
{
    /**
     * @param array $params
     * @return array
     */
    protected function secureParams(array $params = [])
    {
        if (empty($params)) {
            return [];
        }

        array_walk_recursive($params, function(&$value, $key){
            $value = htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        });

        return $params;
    }

    /**
     * @param string $path
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function checkPath($path)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException('Path must be a string.');
        }

        return (file_exists($path)) ? true : false;
    }
}
