<?php

namespace DvSoft\AttributeChangeLog\Tests\Feature;

use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;

it('does not log updates when logging is disabled', function () {
    $intervention = Intervention::create([
        'name' => 'Gamma',
        'status' => 'draft',
    ]);

    expect(AttributeChangeLog::count())->toBeGreaterThan(0);

    $intervention->disableAttributesLogging();
    $intervention->update(['name' => 'Delta']);

    expect(AttributeChangeLog::count())->toBe(2);
});

it('does log updates when logging is enabled', function () {
    $intervention = Intervention::make([
        'name' => 'Gamma',
        'status' => 'draft',
    ]);
    $intervention->disableAttributesLogging();
    $intervention->save();
    expect(AttributeChangeLog::count())->toBe(0);

    $intervention->enableAttributesLogging();
    $intervention->update(['name' => 'Delta']);

    expect(AttributeChangeLog::count())->toBeGreaterThan(0);
});
