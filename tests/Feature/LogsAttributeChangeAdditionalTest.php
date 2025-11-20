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

    $intervention->disableLogging();
    $intervention->update(['name' => 'Delta']);

    expect(AttributeChangeLog::count())->toBe(2);
});
