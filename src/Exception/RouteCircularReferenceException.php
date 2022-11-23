<?php

namespace Yiiboot\Routing\Exception;

use RuntimeException;

class RouteCircularReferenceException extends RuntimeException
{
    public function __construct(string $routeId, array $path)
    {
        parent::__construct(sprintf('Circular reference detected for route "%s", path: "%s".', $routeId, implode(' -> ', $path)));
    }
}
