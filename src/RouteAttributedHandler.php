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

use Psr\Http\Server\MiddlewareInterface;
use Yiiboot\Attributed\AbstractAttributedHandler;
use Yiiboot\Attributed\AttributedClass;
use Yiiboot\Attributed\AttributedMethod;
use Yiiboot\Routing\Attribute\Route as RouteAttribute;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollectorInterface;

/**
 * the Route Annotation handler
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/11/23 20:08
 */
class RouteAttributedHandler extends AbstractAttributedHandler
{
    protected int $defaultRouteIndex = 0;

    protected string $routeAttributeClass = RouteAttribute::class;

    public function __construct(private RouteCollectorInterface $collector, protected ?string $env = null)
    {

    }

    public function getAttribute(): string
    {
        return $this->routeAttributeClass;
    }

    public function handle(array $attributeds): void
    {
        $groups = [];

        foreach ($attributeds as $attributed) {
            if ($attributed instanceof AttributedClass) {
                /* @var RouteAttribute $controller */
                $controller = $attributed->getAttribute();

                $prefix = $controller->getLocalizedPaths() ?: $controller->getPath();

                if (is_array($prefix)) {
                    foreach ($prefix as $locale => $localePrefix) {
                        $groups[$locale] = Group::create($localePrefix)
                            ->middleware(...$controller->getMiddleware())
                            ->hosts(...$controller->getHost());
                    }
                } else {
                    $groups[] = Group::create($prefix)
                        ->middleware(...$controller->getMiddleware())
                        ->hosts(...$controller->getHost());
                }
            } else if ($attributed instanceof AttributedMethod) {
                $groups = $this->addRoute($attributed, $groups);
            }
        }

        if ($groups) {
            $this->collector->addGroup(...array_values($groups));
        }
    }

    /**
     * add route
     *
     * @param AttributedMethod $attributedMethod
     * @param Group[] $groups
     * @return Group[]
     */
    protected function addRoute(AttributedMethod $attributedMethod, array $groups = []): array
    {
        $class = $attributedMethod->getClass();
        $method = $attributedMethod->getMethod();
        /* @var RouteAttribute $annot */
        $annot = $attributedMethod->getAttribute();

        if ($annot->getEnv() && $annot->getEnv() !== $this->env) {
            return $groups;
        }

        $name = $annot->getName() ?? $this->getDefaultRouteName($class, $method);

        $defaults = $annot->getDefaults();
        $options = $annot->getOptions();
        $methods = $annot->getMethods();
        $middleware = $annot->getMiddleware();

        $host = $annot->getHost();
        $override = $annot->getOverride();

        $path = $annot->getLocalizedPaths() ?: $annot->getPath();
        $paths = [];

        if (\is_array($path)) {
            if (\array_is_list($groups)) {
                foreach ($path as $locale => $localePath) {
                    $paths[$locale] = $localePath;
                }
            } else if ($missing = array_diff_key($groups, $path)) {
                throw new \LogicException(sprintf('Route to "%s" is missing paths for locale(s) "%s".', $class->name . '::' . $method->name, implode('", "', array_keys($missing))));
            } else {
                foreach ($path as $locale => $localePath) {
                    if (!isset($groups[$locale])) {
                        throw new \LogicException(sprintf('Route to "%s" with locale "%s" is missing a corresponding prefix in class "%s".', $method->name, $locale, $class->name));
                    }
                    $paths[$locale] = $localePath;
                }
            }
        } else {
            $paths[] = $path;
        }

        foreach ($method->getParameters() as $param) {
            if (isset($defaults[$param->name]) || !$param->isDefaultValueAvailable()) {
                continue;
            }
            foreach ($paths as $locale => $path) {
                if (preg_match(sprintf('/\{%s(?:<.*?>)?\}/', preg_quote($param->name)), $path)) {
                    $defaults[$param->name] = $param->getDefaultValue();
                    break;
                }
            }
        }

        $groups = $groups ?: [
            Group::create()
        ];

        foreach ($groups as $localePrefix => $group) {

            $routes = [];

            foreach ($paths as $locale => $path) {

                if (!array_is_list($groups) && $localePrefix !== $locale && 0 !== $locale) {
                    continue;
                }

                $routeName = 0 !== $locale ? $name . '.' . $locale : (0 !== $localePrefix ? $name . '.' . $localePrefix : $name);
                $route = $this->createRoute($path, $routeName, $defaults, $options, $host, $methods, $middleware, $override);
                $route = $this->configureRoute($route, $class, $method, $annot);
                if (0 !== $locale) {
                    $route = $route->defaults([...$defaults, ...[
                        '_locale' => $locale,
                        '_canonical_route' => $name,
                    ]]);
                }

                $routes[] = $route;
            }

            if ($routes) {
                $groups[$localePrefix] = $group->routes(...$routes)->hosts();
            }
        }

        return $groups;
    }

    protected function createRoute(string $path, ?string $name, array $defaults, array $options, array $host, array $methods, array $middleware, ?bool $override = null): \Yiisoft\Router\Route
    {
        $route = Route::methods($methods ?: ['*'], $path)->defaults($defaults)->middleware(...$middleware)->hosts(...$host);
        if (isset($name)) {
            $route = $route->name($name);
        }
        if (isset($override)) {
            $route = $route->override();
        }
        return $route;
    }

    /**
     * Configures the _controller default parameter of a given Route instance.
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): Route
    {
        if ($class->implementsInterface(MiddlewareInterface::class)) {
            return $route->action($class->getName());
        } else if ('__invoke' === $method->getName()) {
            return $route->action([$class->getName(), '__invoke']);
        } else {
            return $route->action([$class->getName(), $method->getName()]);
        }
    }

    /**
     * Makes the default route name more sane by removing common keywords.
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method): string
    {
        $name = str_replace('\\', '_', $class->name) . '_' . $method->name;
        $name = \function_exists('mb_strtolower') && preg_match('//u', $name) ? mb_strtolower($name, 'UTF-8') : strtolower($name);
        if ($this->defaultRouteIndex > 0) {
            $name .= '_' . $this->defaultRouteIndex;
        }
        ++$this->defaultRouteIndex;

        $name = preg_replace('/(controller)_/', '_', $name);

        if (str_ends_with($method->name, 'Action') || str_ends_with($method->name, '_action')) {
            $name = preg_replace('/action(_\d+)?$/', '\\1', $name);
        }

        return str_replace('__', '_', $name);
    }
}
