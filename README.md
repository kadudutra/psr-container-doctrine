# psr-container-doctrine: Doctrine Factories for PSR-11 Containers

[![Latest Stable Version](https://poser.pugx.org/roave/psr-container-doctrine/v/stable)](https://packagist.org/packages/roave/psr-container-doctrine)
[![Total Downloads](https://poser.pugx.org/roave/psr-container-doctrine/downloads)](https://packagist.org/packages/roave/psr-container-doctrine)
[![Build Status](https://api.travis-ci.org/roave/psr-container-doctrine.png?branch=master)](http://travis-ci.org/roave/psr-container-doctrine)
[![Coverage Status](https://coveralls.io/repos/roave/psr-container-doctrine/badge.png?branch=master)](https://coveralls.io/r/roave/psr-container-doctrine)

[Doctrine](https://github.com/doctrine) factories for [PSR-11 containers](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md).

This package provides a set of factories to be used with containers using the PSR-11 standard for an easy
Doctrine integration in a project. This project was originally written by
[@DASPRiD](https://github.com/DASPRiD/container-interop-doctrine) but maintenance has been taken over by Roave.

## Installation

The easiest way to install this package is through composer:

```bash
$ composer require kadudutra/psr-container-doctrine
```

## Configuration

In the general case where you are only using a single connection, it's enough to define the entity manager factory:

```php
return [
    'dependencies' => [
        'factories' => [
            'doctrine.entity_manager.orm_default' => \KaduDutra\PsrContainerDoctrine\EntityManagerFactory::class,
        ],
    ],
];
```

If you want to add a second connection, or use another name than "orm_default", you can do so by using the static
variants of the factories:

```php
return [
    'dependencies' => [
        'factories' => [
            'doctrine.entity_manager.orm_other' => [\KaduDutra\PsrContainerDoctrine\EntityManagerFactory::class, 'orm_other'],
        ],
    ],
];
```

You can also define an alias to retrieve an entity manager instance using `::class` capability:
```php
return [
    'aliases' => [
        'doctrine.entity_manager.orm_default' => Doctrine\ORM\EntityManagerInterface::class,
    ],
];
```

Each factory supplied by this package will by default look for a registered factory in the container. If it cannot find
one, it will automatically pull its dependencies from on-the-fly created factories. This saves you the hassle of
registering factories in your container which you may not need at all. Of course, you can always register those
factories when required. The following additional factories are available:

- ```\KaduDutra\PsrContainerDoctrine\CacheFactory``` (doctrine.cache.*)
- ```\KaduDutra\PsrContainerDoctrine\ConnectionFactory``` (doctrine.connection.*)
- ```\KaduDutra\PsrContainerDoctrine\ConfigurationFactory``` (doctrine.configuration.*)
- ```\KaduDutra\PsrContainerDoctrine\DriverFactory``` (doctrine.driver.*)
- ```\KaduDutra\PsrContainerDoctrine\EventManagerFactory``` (doctrine.event_manager.*)
- ```\KaduDutra\PsrContainerDoctrine\MigrationsConfigurationFactory``` (doctrine.migrations.*)

Each of those factories supports the same static behavior as the entity manager factory. For container specific
configurations, there are a few examples provided in the example directory:

- [Aura.Di](example/aura-di.php)
- [PimpleInterop](example/pimple-interop.php)
- [Laminas\ServiceManager](example/laminas-servicemanager.php)

## Example configuration

A complete example configuration can be found in [example/full-config.php](example/full-config.php). Please note that
the values in there are the defaults, and don't have to be supplied when you are not changing them. Keep your own
configuration as minimal as possible. A minimal configuration can be found in
[example/minimal-config.php](example/minimal-config.php)

## Migrations

If you want to expose the migration commands, you have to map the command name to `MigrationsCommandFactory`.  This factory needs migrations config setup.
For `ExecuteCommand` example:

```php
return [
    'dependencies' => [
        'factories' => [
            \Doctrine\Migrations\Tools\Console\Command\ExecuteCommand::class => \KaduDutra\PsrContainerDoctrine\MigrationsCommandFactory::class,
        ],
    ],
];
```

You can find a full list of available commands in [example/full-config.php](example/full-config.php).

## Using the Doctrine CLI

In order to be able to use the CLI tool of Doctrine, you need to set-up a ```cli-config.php``` file in your project
directory. That file is generally quite short, and should look something like this for you:

```php
<?php
$container = require 'config/container.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet(
    $container->get('doctrine.entity_manager.orm_default')
);
```

After that, you can simply invoke ```php vendor/bin/doctrine```.

### Multiple connections

It gets a little trickier when you have multiple entity managers. Doctrine itself has no way to handle that itself, so
a possible way would be to have two separate directories with two unique ```cli-config.php``` files. You then invoke the
doctrine CLI from each respective directory. Since the CLI is looking for the config file in the current working
directory, it will then always use the one from the directory you are currently in.

The following code can be used for multiple connections, but it got the drawback, that you won't see the `--em=...`
option within the help section of each command.

```php
<?php
$container = require 'config/container.php';

$input = new \Symfony\Component\Console\Input\ArgvInput();

/** @var string $em */
$em = $input->getParameterOption('--em', 'orm_default');

// hack to remove the --em option, cause it's not supported by the original ConsoleRunner.
foreach ($_SERVER['argv'] as $i => $arg) {
    if (0 === strpos($arg, '--em=')) {
        unset($_SERVER['argv'][$i]);
    }
}

try {
    $entityManager = $this->container->get('doctrine.entity_manager.'.$em);
} catch (\Psr\Container\NotFoundExceptionInterface $serviceNotFoundException) {
    throw new \InvalidArgumentException(sprintf('Missing entity manager with name "%s"', $entityManagerName));
}

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
```
