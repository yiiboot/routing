<?php
/*
 * This file is part of the Yii Boot package.
 *
 * (c) niqingyang <niqy@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yiiboot\Routing\Attribute;

use Attribute;

/**
 * Annotation class for @Route().
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/11/12 22:20
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route
{
    private ?string $path = null;
    private array $localizedPaths = [];
    private array $methods;
    private ?array $host;
    private ?array $middleware;
    private array $env = [];

    /**
     * @param string[]|string $methods
     */
    public function __construct(
        string|array          $path = null,
        private ?string       $name = null,
        array|string          $methods = [],
        private array         $defaults = [],
        private array         $options = [],
        array|string|callable $middleware = [],
        array|string          $host = '',
        private ?int          $priority = null,
        string                $locale = null,
        bool                  $stateless = null,
        private ?bool         $override = null,
        array|string|null     $env = null,
    )
    {
        if (\is_array($path)) {
            $this->localizedPaths = $path;
        } else {
            $this->path = $path;
        }
        $this->setHost($host);
        $this->setMethods($methods);
        $this->setMiddleware($middleware);

        if (null !== $locale) {
            $this->defaults['_locale'] = $locale;
        }

        if (null !== $stateless) {
            $this->defaults['_stateless'] = $stateless;
        }

        if (isset($env)) {
            $this->env = (array) $env;
        }
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setLocalizedPaths(array $localizedPaths): void
    {
        $this->localizedPaths = $localizedPaths;
    }

    public function getLocalizedPaths(): array
    {
        return $this->localizedPaths;
    }

    public function setHost(array|string $host): void
    {
        $this->host = (array) $host;
    }

    public function getHost(): array
    {
        return $this->host ?? [];
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setDefaults(array $defaults): void
    {
        $this->defaults = $defaults;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setMethods(array|string $methods): void
    {
        $this->methods = (array) $methods;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setEnv(array|string|null $env): void
    {
        $this->env = isset($env) ? (array) $env : [];
    }

    public function getEnv(): array
    {
        return $this->env;
    }

    /**
     * @return bool|null
     */
    public function getOverride(): ?bool
    {
        return $this->override;
    }

    /**
     * @param bool|null $override
     */
    public function setOverride(?bool $override): void
    {
        $this->override = $override;
    }

    /**
     * @return array|null
     */
    public function getMiddleware(): array
    {
        return $this->middleware ?? [];
    }

    /**
     * @param array|null $middleware
     */
    public function setMiddleware(array|callable|string $middleware): void
    {
        $this->middleware = (array) $middleware;
    }
}
