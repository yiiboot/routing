<p align="center">
    <a href="https://github.com/yiiboot" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/118281946?s=600&u=b16475d97095b69a8f500ec2f29b8d05c3d02b3a&v=4" height="100px">
    </a>
    <h1 align="center">YiiBoot Routing</h1>
    <br>
</p>

[//]: # ([![Latest Stable Version]&#40;https://poser.pugx.org/yiiboot/routing/v/stable.png&#41;]&#40;https://packagist.org/packages/yiiboot/_____&#41;)

[//]: # ([![Total Downloads]&#40;https://poser.pugx.org/yiiboot/routing/downloads.png&#41;]&#40;https://packagist.org/packages/yiiboot/_____&#41;)

[//]: # ([![Build status]&#40;https://github.com/yiiboot/routing/workflows/build/badge.svg&#41;]&#40;https://github.com/yiiboot/_____/actions?query=workflow%3Abuild&#41;)

[//]: # ([![Scrutinizer Code Quality]&#40;https://scrutinizer-ci.com/g/yiiboot/routing/badges/quality-score.png?b=master&#41;]&#40;https://scrutinizer-ci.com/g/yiiboot/_____/?branch=master&#41;)

[//]: # ([![Code Coverage]&#40;https://scrutinizer-ci.com/g/yiiboot/routing/badges/coverage.png?b=master&#41;]&#40;https://scrutinizer-ci.com/g/yiiboot/_____/?branch=master&#41;)

[//]: # ([![Mutation testing badge]&#40;https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%yiiboot%2F_____%2Fmaster&#41;]&#40;https://dashboard.stryker-mutator.io/reports/github.com/yiiboot/_____/master&#41;)

[//]: # ([![static analysis]&#40;https://github.com/yiiboot/routing/workflows/static%20analysis/badge.svg&#41;]&#40;https://github.com/yiiboot/_____/actions?query=workflow%3A%22static+analysis%22&#41;)

[//]: # ([![type-coverage]&#40;https://shepherd.dev/github/yiiboot/routing/coverage.svg&#41;]&#40;https://shepherd.dev/github/yiiboot/_____&#41;)

An way to define an route with the Route PHP attribute. This allows to configure the route inside
its class, without having to add any configuration in external files

## Requirements

- PHP 8.1 or higher.

## Installation

The package could be installed with composer:

```shell
composer require yiiboot/routing
```

## General usage

the `config/params.php`
```php
return [
    // ...
    'yiiboot/attributed' => [
        'paths' => [
            dirname(__DIR__) . '/src/Controller'
        ]
    ]
];
```

the `src/Controller/CustomController.php`

```php
namespace App\Controller;

use Yiiboot\Routing\Attribute\Route;use Yiisoft\Router\CurrentRoute;

#[Route('/customs', name:'customs.', middleware: [
    FormatDataResponseAsJson::class
])]
final class CustomController
{
    #[Route('/{page:\d+}', name: 'list', method: 'GET', defaults: ['page' => 1])]
    public function list(): ResponseInterface
    {
        // ...
    }

    #[Route('/{id:\d+}', name: 'view', method: 'GET')]
    public function view(CurrentRoute $route): ResponseInterface
    {
        $id = $route->getArgument('id');
        // ...
    }

    #[Route(name: 'create', method: 'POST')]
    public function create(): ResponseInterface
    {
        // ...
    }

    #[Route('/{id:\d+}', name: 'delete', method: 'DELETE')]
    public function delete(CurrentRoute $route): ResponseInterface
    {
        $id = $route->getArgument('id');
        // ...
    }
}
```

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Code style

Use [Rector](https://github.com/rectorphp/rector) to make codebase follow some specific rules or
use either newest or any specific version of PHP:

```shell
./vendor/bin/rector
```

### Dependencies

Use [ComposerRequireChecker](https://github.com/maglnet/ComposerRequireChecker) to detect transitive
[Composer](https://getcomposer.org/) dependencies.

## License

The Yii Routing is free software. It is released under the terms of the Apache-2.0 License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Boot](https://github.com/yiiboot).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiiboot)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Boot-green.svg?style=flat)](https://www.yiiframework.com/)

## Inspired && Thanks

- [Yii Software](https://github.com/yiisoft)
- [Yii Stack](https://github.com/yiistack)
- [Symfony](https://github.com/symfony/symfony)
