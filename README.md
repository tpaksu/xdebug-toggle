# Laravel Valet XDebug Toggler Artisan Command

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tpaksu/xdebug-toggle.svg?style=flat-square)](https://packagist.org/packages/tpaksu/xdebug-toggle)
[![Build Status](https://img.shields.io/travis/tpaksu/xdebug-toggle/master.svg?style=flat-square)](https://travis-ci.org/tpaksu/xdebug-toggle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tpaksu/xdebug-toggle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tpaksu/xdebug-toggle)
[![Total Downloads](https://img.shields.io/packagist/dt/tpaksu/xdebug-toggle.svg?style=flat-square)](https://packagist.org/packages/tpaksu/xdebug-toggle)

This package automates the XDebug extension activation/deactivation process by adding a console command to Laravel's artisan command. It does these things:

- Modifying the current INI file used by PHP, located with parsing the "php_info()" output containing the line "Loaded Configuration File: [path/to/php.ini]"
- Restarting the valet NGINX server with the command `valet restart nginx`

If you use something else than Laravel Valet, and want to automate your stuff, you can change the command in `src\Commands\XdebugToggle@restartServices` method to suit your own.

## Installation

You can install the package as a development requirement via composer:

```bash
composer require tpaksu/xdebug-toggle --dev
```

## Usage

To enable XDebug in current environment:

``` bash
php artisan xdebug on
```

To disable it:

``` bash
php artisan xdebug off
```

### Configuration

The only configurable thing is the service restart command (since 0.1.1), which you can alter with an environment
variable named `XDEBUG_SERVICE_RESTART_COMMAND`. This defaults to `valet restart nginx` if not set to anything else.


### Testing

Any tests written yet. But I suppose I should.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details. Any contributions are welcome.

### Security

If you discover any security related issues, please email tpaksu@gmail.com instead of using the issue tracker.

## Credits

- [Taha PAKSU](https://github.com/tpaksu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.