<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use Carbon\Carbon;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Actor;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;

it('casts values of different types', function () {
    $log = new AttributeChangeLog;

    $log->value = ['foo' => 'bar'];
    expect($log->type)->toBe('array');
    expect($log->value)->toBe(['foo' => 'bar']);

    $log->value = (object) ['nested' => 'value'];
    expect($log->type)->toBe('object');
    expect($log->value)->toMatchObject((object) ['nested' => 'value']);

    $date = Carbon::now();
    $log->value = $date;
    expect($log->type)->toBe('datetime');
    assert($log->value instanceof Carbon);

    $log->value = 42;
    expect($log->type)->toBe('integer');
    expect($log->value)->toBe(42);
});

it('applies its scopes correctly', function () {
    $subject = Intervention::create([
        'name' => 'Scout',
        'status' => 'new',
    ]);

    AttributeChangeLog::truncate();

    $yesterday = Carbon::yesterday()->startOfDay()->addHours(5);
    $today = Carbon::today()->startOfDay()->addHours(2);

    AttributeChangeLog::create([
        'subject_type' => Intervention::class,
        'subject_id' => $subject->getKey(),
        'attribute' => 'name',
        'value' => 'Scout',
        'type' => 'string',
        'created_at' => $yesterday,
    ]);

    AttributeChangeLog::create([
        'subject_type' => Intervention::class,
        'subject_id' => $subject->getKey(),
        'attribute' => 'status',
        'value' => 'new',
        'type' => 'string',
        'created_at' => $today,
    ]);

    expect(AttributeChangeLog::attributeEditedOn('name', $yesterday)->count())->toBe(1);
    expect(AttributeChangeLog::attributeEditedBetween('status', $yesterday, $today)->count())->toBe(1);
    expect(AttributeChangeLog::editedSince($subject, $yesterday)->count())->toBe(2);
    expect(AttributeChangeLog::latestForSubject($subject)->first()->attribute)->toBe('status');
});

it('filters logs by causer', function () {
    $subject = Intervention::create([
        'name' => 'Scout',
        'status' => 'agent',
    ]);

    $actor = Actor::create(['name' => 'Agent']);

    AttributeChangeLog::create([
        'subject_type' => Intervention::class,
        'subject_id' => $subject->getKey(),
        'attribute' => 'status',
        'value' => 'agent',
        'type' => 'string',
        'causer_type' => Actor::class,
        'causer_id' => $actor->getKey(),
    ]);

    $otherActor = Actor::create(['name' => 'Other']);

    AttributeChangeLog::create([
        'subject_type' => Intervention::class,
        'subject_id' => $subject->getKey(),
        'attribute' => 'status',
        'value' => 'agent',
        'type' => 'string',
        'causer_type' => Actor::class,
        'causer_id' => $otherActor->getKey(),
    ]);

    expect(AttributeChangeLog::causedBy($actor)->count())->toBe(1);
});
