<?php
/*
 * This file is part of the Yii Boot package.
 *
 * (c) niqingyang <niqy@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Yiiboot\Routing\RouteAnnotatedHandler;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\RouteCollectorInterface;

return [
    RouteAnnotatedHandler::class => [
        '__construct()' => [
            'collector' => Reference::to(RouteCollectorInterface::class),
            'env' => $_ENV['YII_ENV'] ?? null
        ]
    ]
];
