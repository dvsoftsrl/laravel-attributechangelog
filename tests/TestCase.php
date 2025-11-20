<?php

namespace DvSoft\AttributeChangeLog\Tests;

use DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'DvSoft\\AttributeChangeLog\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->createInterventionsTable();
        $this->createActorsTable();
        $this->createPartnersTable();
        $this->createAttributeChangeLogTable();
    }

    protected function getPackageProviders($app)
    {
        return [
            AttributeChangeLogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function createInterventionsTable(): void
    {
        Schema::dropIfExists('interventions');

        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('partner_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    protected function createActorsTable(): void
    {
        Schema::dropIfExists('actors');

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function createPartnersTable(): void
    {
        Schema::dropIfExists('partners');

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function createAttributeChangeLogTable(): void
    {
        $tableName = config('attributechangelog.table_name');
        $connection = config('attributechangelog.database_connection') ?? config('database.default');

        $schema = Schema::connection($connection);

        if ($schema->hasTable($tableName)) {
            $schema->drop($tableName);
        }

        $schema->create($tableName, function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->nullableMorphs('causer');
            $table->string('attribute');
            $table->string('type')->nullable();
            $table->text('value')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
}
