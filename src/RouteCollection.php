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

use InvalidArgumentException;
use Yiiboot\Routing\Exception\RouteCircularReferenceException;
use Yiisoft\Router\Route;

class RouteCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<string, Route>
     */
    private array $routes = [];

    /**
     * @var array<string, Alias>
     */
    private array $aliases = [];

    /**
     * @var array<string, int>
     */
    private array $priorities = [];

    public function __clone()
    {
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * It implements \IteratorAggregate.
     *
     * @return \ArrayIterator<string, Route>
     * @see all()
     *
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Gets the number of Routes in this collection.
     */
    public function count(): int
    {
        return \count($this->routes);
    }

    public function add(string $name, Route $route, int $priority = 0)
    {
        unset($this->routes[$name], $this->priorities[$name], $this->aliases[$name]);

        $this->routes[$name] = $route;

        if ($priority) {
            $this->priorities[$name] = $priority;
        }
    }

    /**
     * Returns all routes in this collection.
     *
     * @return array<string, Route>
     */
    public function all(): array
    {
        if ($this->priorities) {
            $priorities = $this->priorities;
            $keysOrder = array_flip(array_keys($this->routes));
            uksort($this->routes, static function ($n1, $n2) use ($priorities, $keysOrder) {
                return (($priorities[$n2] ?? 0) <=> ($priorities[$n1] ?? 0)) ?: ($keysOrder[$n1] <=> $keysOrder[$n2]);
            });
        }

        return $this->routes;
    }

    /**
     * Gets a route by name.
     */
    public function get(string $name): ?Route
    {
        $visited = [];
        while (null !== $alias = $this->aliases[$name] ?? null) {
            if (false !== $searchKey = array_search($name, $visited)) {
                $visited[] = $name;

                throw new RouteCircularReferenceException($name, \array_slice($visited, $searchKey));
            }

            if ($alias->isDeprecated()) {
                $deprecation = $alias->getDeprecation($name);

                trigger_deprecation($deprecation['package'], $deprecation['version'], $deprecation['message']);
            }

            $visited[] = $name;
            $name = $alias->getId();
        }

        return $this->routes[$name] ?? null;
    }

    /**
     * Removes a route or an array of routes by name from the collection.
     *
     * @param string|string[] $name The route name or an array of route names
     */
    public function remove(string|array $name)
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n], $this->priorities[$n], $this->aliases[$n]);
        }
    }

    /**
     * Adds a route collection at the end of the current set by appending all
     * routes of the added collection.
     */
    public function addCollection(self $collection)
    {
        // we need to remove all routes with the same names first because just replacing them
        // would not place the new route at the end of the merged array
        foreach ($collection->all() as $name => $route) {
            unset($this->routes[$name], $this->priorities[$name], $this->aliases[$name]);
            $this->routes[$name] = $route;

            if (isset($collection->priorities[$name])) {
                $this->priorities[$name] = $collection->priorities[$name];
            }
        }

        foreach ($collection->getAliases() as $name => $alias) {
            unset($this->routes[$name], $this->priorities[$name], $this->aliases[$name]);

            $this->aliases[$name] = $alias;
        }
    }

    /**
     * Sets an alias for an existing route.
     *
     * @param string $name The alias to create
     * @param string $alias The route to alias
     *
     * @throws InvalidArgumentException if the alias is for itself
     */
    public function addAlias(string $name, string $alias): Alias
    {
        if ($name === $alias) {
            throw new InvalidArgumentException(sprintf('Route alias "%s" can not reference itself.', $name));
        }

        unset($this->routes[$name], $this->priorities[$name]);

        return $this->aliases[$name] = new Alias($alias);
    }

    /**
     * @return array<string, Alias>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}
