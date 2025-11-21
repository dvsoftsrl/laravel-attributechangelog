# Attribute change logger for eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dvsoftsrl/laravel-attributechangelog.svg?style=flat-square)](https://packagist.org/packages/dvsoftsrl/laravel-attributechangelog)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dvsoftsrl/laravel-attributechangelog/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dvsoftsrl/laravel-attributechangelog/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dvsoftsrl/laravel-attributechangelog/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dvsoftsrl/laravel-attributechangelog/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dvsoftsrl/laravel-attributechangelog.svg?style=flat-square)](https://packagist.org/packages/dvsoftsrl/laravel-attributechangelog)

This package keeps track of every attribute that changes on an Eloquent model by storing one log row per attribute plus metadata about the acting model. It includes helpers to filter logs by attribute, subject, date, and causer so you can answer questions such as “when was `status` last changed on this model?” or “which models had `stage` updated yesterday?”

## Features

-   Automatically listen to the `created`/`updated` events (customizable via `$recordEvents`)
-   Persist each mutated attribute as a separate log entry with its `subject`, `causer`, `attribute`, and resolved `value`
-   Support relation attributes and JSON-path segments (e.g. `order.customer.name`, `payload->meta.inner`)
-   Expose fluent scopes for filtering by attribute, causer, date ranges, and subjects

## Installation

You can install the package via composer:

```bash
composer require dvsoftsrl/laravel-attributechangelog
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider" --tag="laravel-attributechangelog-migrations"
```

_Note_: The default migration assumes you are using integers for your model IDs. If you are using UUIDs, or some other format, adjust the format of the `subject_id` and `causer_id` fields in the published migration before continuing.

After publishing the migration you can create the `activity_log` table by running the migrations:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider" --tag="laravel-attributechangelog-config"
```

This is the contents of the published config file:

```php
return [
    'enabled' => env('ATTRIBUTE_CHANGE_LOGGER_ENABLED', true),
    'attribute_change_log_model' => \DvSoft\AttributeChangeLog\Models\AttributeChangeLog::class,
    'table_name' => env('ATTRIBUTE_CHANGE_TABLE_NAME', 'activity_log'),
    'database_connection' => env('ATTRIBUTE_CHANGE_DB_CONNECTION'),
];
```

You can override the log model (must implement `DvSoft\AttributeChangeLog\Contracts\AttributeChangeLog`) or change the table/connection before running the migrations.

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-attributechangelog-views"
```

## Usage

```php
use DvSoft\AttributeChangeLog\Traits\LogsAttributeChange;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use LogsAttributeChange;

    protected $fillable = ['name', 'status'];
}
```

The trait will listen for the configured events and write one `AttributeChangeLog` row per attribute. Each log entry records the current value, root subject, optional JSON path, and optional `causer`. You can customize which attributes should be watched by overriding `$attributesToBeLogged`.

```php
$intervention = Intervention::find(1);
$intervention->status = 'published';
$intervention->save();

$lastStatusChange = $intervention->attributeChangeLogs()
    ->forAttribute('status')
    ->latest('created_at')
    ->first();

$yesterdayLogs = Intervention::editedAttributeOn('status', today()->subDay())->get();
```

Models that need to act as causers can use `DvSoft\AttributeChangeLog\Traits\CausesActivity` to expose the inverse morph relationship.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Luca Dell'Orto](https://github.com/luca-dellorto)
-   [Stefano Vergani](https://github.com/stefano-vergani)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
