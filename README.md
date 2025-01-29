# Laravel Web Mailer
This package contains a web mailer which will catch all the sent emails. Then, you can view it visiting the route `/web-inbox`.
The emails will be stored as a file in the storage folder.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creagia/laravel-web-mailer.svg?style=flat-square)](https://packagist.org/packages/creagia/laravel-web-mailer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/creagia/laravel-web-mailer/run-tests.yml?label=tests)](https://github.com/creagia/laravel-web-mailer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/creagia/laravel-web-mailer/php-cs-fixer.yml?label=code%20style)](https://github.com/creagia/laravel-web-mailer/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/creagia/laravel-web-mailer.svg?style=flat-square)](https://packagist.org/packages/creagia/laravel-web-mailer)

<p align="center"><img src="/art/screenshot.webp" alt="Inbox screenshot"></p>

## Requirements
This package requires PHP 8.1 and Laravel 9. If you need compatibility with older versions,
check the [alternatives](#alternatives) section.

## Installation
You can install the package via composer:
```bash
composer require creagia/laravel-web-mailer
```

After that, open your `config/mail.php` file and add the web mailer entry in the `mailers` configuration array.
```php
'mailers' => [
    // ...
    'web' => [
        'transport' => 'web',
    ],
],
```

Finally, you can enable the web mailer setting the defined mailer in your .env file:
```
MAIL_MAILER=web
```

We recommend you to publish the configuration file to be able to perform some customizations
```bash
php artisan vendor:publish --tag="web-mailer-config"
```

### Inbox URL
The default URL to view the emails is `/web-inbox`. You can change it, adding the below entry to your .env file:
```
WEB_MAILER_ROUTE_PREFIX="your-custom-url"
```

### Route protection
By default, the inbox URL is authorized for everybody who has the link. If you need to add some protection, you can modify the `middleware` array on the `config/web-mailer.php` file. 

### Delete all stored emails
```bash
php artisan laravel-web-mailer:clear-all
```

### Delete stored emails older than N days
```bash
php artisan laravel-web-mailer:cleanup
```
You can run or schedule the command `laravel-web-mailer:cleanup` to delete the emails older than N days. By default, it will delete the emails older than 7 days. You can customize the number of days changing the `delete_emails_older_than_days` parameter on the `config/web-mailer.php` file.


## Testing
```bash
composer test
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

<a name="alternatives"></a>
## Alternatives
- [tkeer/mailbase](https://github.com/tkeer/mailbase)

## Contributing
Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities
Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits
- [Xavier Muntané](https://github.com/xmuntane)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
