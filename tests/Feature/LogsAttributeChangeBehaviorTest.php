<?php

namespace DvSoft\AttributeChangeLog\Tests\Feature;

use DvSoft\AttributeChangeLog\AttributeChangeLogStatus;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Actor;
use DvSoft\AttributeChangeLog\Tests\Models\CauserIntervention;
use DvSoft\AttributeChangeLog\Tests\Models\EmptyAttributesIntervention;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;
use DvSoft\AttributeChangeLog\Tests\Models\LoggingSubsetIntervention;

it('respects custom attributesToBeLogged definitions', function () {
    $model = LoggingSubsetIntervention::create([
        'name' => 'Subset',
        'status' => 'green',
    ]);

    $model->update(['status' => 'red']);

    expect(AttributeChangeLog::count())->toBe(2);
    expect($model->attributeChangeLogs()->pluck('attribute')->unique()->all())->toBe(['status']);
});

it('uses the overridden attributeChangeCauser', function () {
    $actor = Actor::create(['name' => 'Causer']);
    CauserIntervention::setExplicitCauser($actor);

    $model = CauserIntervention::create([
        'name' => 'Causer',
        'status' => 'initial',
    ]);

    $model->update(['status' => 'done']);

    expect(AttributeChangeLog::whereNotNull('causer_id')->count())->toBeGreaterThan(0);
    expect(AttributeChangeLog::orderByDesc('id')->first()->causer_id)->toBe($actor->getKey());
});

it('does not write logs when logging is globally disabled', function () {
    config()->set('attributechangelog.enabled', false);
    $this->app->forgetInstance(AttributeChangeLogStatus::class);

    $model = Intervention::create([
        'name' => 'NoLog',
        'status' => 'hidden',
    ]);

    $model->update(['status' => 'shown']);

    expect(AttributeChangeLog::count())->toBe(0);
});

it('skips logging when there are no configured attributes', function () {
    AttributeChangeLog::truncate();

    $model = EmptyAttributesIntervention::create([
        'name' => 'NoAttrs',
        'status' => 'silent',
    ]);

    $model->update(['name' => 'StillSilent']);

    expect(AttributeChangeLog::count())->toBe(0);
});
