<?php

namespace DvSoft\AttributeChangeLog\Tests\Feature;

use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;
use DvSoft\AttributeChangeLog\Tests\Models\JsonIntervention;
use DvSoft\AttributeChangeLog\Tests\Models\Partner;
use DvSoft\AttributeChangeLog\Tests\Models\RelationIntervention;

it('records nested json attribute segments', function () {
    $model = JsonIntervention::create([
        'name' => 'Json',
        'status' => 'initial',
        'payload' => ['meta' => ['inner' => 'first']],
    ]);

    $model->update([
        'payload' => ['meta' => ['inner' => 'second']],
    ]);

    $changes = AttributeChangeLog::where('attribute', 'payload.meta.inner')->orderByDesc('id')->first();

    expect($changes->value)->toBe('second');
});

it('tracks relation attr changes when the foreign key switches', function () {
    $partnerA = Partner::create(['name' => 'A']);
    $partnerB = Partner::create(['name' => 'B']);

    $model = RelationIntervention::create([
        'name' => 'Linked',
        'partner_id' => $partnerA->getKey(),
    ]);

    $model->update(['partner_id' => $partnerB->getKey()]);

    $values = AttributeChangeLog::where('attribute', 'partner.name')
        ->orderBy('id')
        ->pluck('value')
        ->all();

    expect($values)->toHaveCount(2);
    expect($values[0])->toBe('A');
    expect($values[1])->toBe('A');
});

it('exposes activities relation for the subject', function () {
    $model = Intervention::create([
        'name' => 'Activity',
        'status' => 'active',
    ]);

    expect($model->attributeChangeActivities()->count())->toBeGreaterThan(0);
});
