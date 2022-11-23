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

use ReflectionClass;
use ReflectionMethod;
use Yiiboot\Annotated\AnnotatedMethod;
use Yiiboot\Routing\Annotation\Route as RouteAnnotation;
use Yiiboot\Annotated\AnnotationLoader;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

class RouteCompiler
{
    protected ?string $env;

    private AnnotationLoader $loader;

    protected string $routeAnnotationClass = RouteAnnotation::class;

    /**
     * @var int
     */
    protected int $defaultRouteIndex = 0;

    public function __construct(AnnotationLoader $loader, string $env = null)
    {
        $this->loader = $loader;
        $this->env = $env;
    }

    /**
     * @psalm-suppress DeprecatedMethod
     *
     * @return array
     */
    public function compile(): array
    {
        $compiledRoutes = [];
        $controllers = $this->loader->findClasses($this->routeAnnotationClass);
        foreach ($controllers as $annotatedClass) {
            $controllerClass = $annotatedClass->getClass();
            /* @var RouteAnnotation $controller */
            $controller = $annotatedClass->getAnnotation();

            $collection = new RouteCollection();

            foreach ($this->loader->findMethods($this->routeAnnotationClass, $controllerClass) as $annotatedMethod) {
                $this->addRoute($collection, $annotatedMethod, []);
            }

            $compiledRoutes[] = Group::create($controller->getPath())->routes(...$collection->all());
        }

        return $compiledRoutes;
    }


}
