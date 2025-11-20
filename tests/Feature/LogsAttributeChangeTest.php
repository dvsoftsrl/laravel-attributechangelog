<?php

namespace DvSoft\AttributeChangeLog\Tests\Feature;

use Carbon\Carbon;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;

it('creates a row for every changed attribute', function () {
    $intervention = Intervention::create([
        'name' => 'Alpha',
        'status' => 'draft',
    ]);

    $intervention->update([
        'name' => 'Bravo',
        'status' => 'published',
    ]);

    expect(AttributeChangeLog::count())->toBe(4);
    expect($intervention->attributeChangeLogs()->count())->toBe(4);
    expect($intervention->lastAttributeChange('status')->value)->toBe('published');
});

it('allows scoping models edited on an attribute during a date', function () {
    $date = Carbon::yesterday()->startOfDay();

    Carbon::setTestNow($date->copy()->addHours(3));

    Intervention::create([
        'name' => 'Charlie',
        'status' => 'active',
    ]);

    Carbon::setTestNow();

    $results = Intervention::editedAttributeOn('name', $date)->get();

    expect($results)->toHaveCount(1);
});
