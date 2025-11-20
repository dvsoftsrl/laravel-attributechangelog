<?php

namespace DvSoft\AttributeChangeLog;

use DvSoft\AttributeChangeLog\Contracts\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Contracts\AttributeChangeLog as ActivityContract;
use DvSoft\AttributeChangeLog\Exceptions\InvalidConfiguration;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog as AttributeChangeLogModel;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AttributeChangeLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-attributechangelog')
            ->hasConfigFile('attributechangelog')
            ->hasMigration('create_attribute_change_logs_table');
    }

    public function registeringPackage()
    {
        $this->app->scoped(AttributeChangeLogStatus::class);
    }

    public static function determineAttributeChangeLogModel(): string
    {
        $attributeChangeModel = config('activitylog.activity_model') ?? AttributeChangeLogModel::class;

        if (! is_a($attributeChangeModel, AttributeChangeLog::class, true)
            || ! is_a($attributeChangeModel, Model::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($attributeChangeModel);
        }

        return $attributeChangeModel;
    }

    public static function getAttributeChangeLogModelInstance(): ActivityContract
    {
        $attributeChangeLogModelClassName = self::determineAttributeChangeLogModel();

        return new $attributeChangeLogModelClassName;
    }
}
