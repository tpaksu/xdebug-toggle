# Laravel Valet XDebug Toggler Artisan Command

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tpaksu/xdebug-toggle.svg?style=flat-square)](https://packagist.org/packages/tpaksu/xdebug-toggle)
[![Build Status](https://img.shields.io/travis/tpaksu/xdebug-toggle/master.svg?style=flat-square)](https://travis-ci.org/tpaksu/xdebug-toggle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tpaksu/xdebug-toggle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tpaksu/xdebug-toggle)
[![Total Downloads](https://img.shields.io/packagist/dt/tpaksu/xdebug-toggle.svg?style=flat-square)](https://packagist.org/packages/tpaksu/xdebug-toggle)
[![StyleCI](https://github.styleci.io/repos/7548986/shield)](https://github.styleci.io/repos/271391496)

This package automates the XDebug extension activation/deactivation process by adding a console command to Laravel's artisan command. It does these things:

- Modifying the current INI file used by PHP, located with parsing the "php_info()" output containing the line "Loaded Configuration File: [path/to/php.ini]"
- Restarting the valet NGINX server with the command `valet restart nginx`

If you use something else than Laravel Valet, and want to automate your stuff, you can change the **XDEBUG_SERVICE_RESTART_COMMAND** in your environment file, or the `xdebugtoggle.service_restart_command` configuration in xdebug-toggle.php configuration file to suit your own.

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

You can export the configuration file by running the command:

``` bash
php artisan vendor:publish --provider="Tpaksu\XdebugToggle\XdebugToggleServiceProvider"
```

which includes:

**service_restart_command** : Gives you the option to run a script after you change the *php.ini* line with the new XDebug status. The default is

```bash
valet restart nginx
```

which applies the new php.ini configuration on the PHP running on valet's nginx server.

I tried and succeded with this command on Windows running Laragon with nginx:

```batch
c:/laragon/bin/nginx/nginx-1.12.0/nginx.exe -p c:/laragon/bin/nginx/nginx-1.12.0 -c conf/nginx.conf -s reload
```

which I changed with setting this environment variable on `.env` and ran `php artisan config:cache` to apply the environment changes:

```ini
XDEBUG_SERVICE_RESTART_COMMAND="c:/laragon/bin/nginx/nginx-1.12.0/nginx.exe -p c:/laragon/bin/nginx/nginx-1.12.0 -c conf/nginx.conf -s reload"
```

I could change the configuration setting on `config/xdebug-toggle.php` file too. This would also be a valid modification on the path.

**Note: Don't forget to run `php artisan config:cache` to apply new settings when you change any `.env` parameter or configuration setting. Not only for this package, for all changes inside Laravel.**



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