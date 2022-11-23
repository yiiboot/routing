<?php
/*
 * This file is part of the Yii Boot package.
 *
 * (c) niqingyang <niqy@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yiiboot\Routing;

use Psr\Container\ContainerInterface;
use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\Router\RouteCollector;

class RoutingServiceProvider implements ServiceProviderInterface
{

    public function getDefinitions(): array
    {
        return [];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
