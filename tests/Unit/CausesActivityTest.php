<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Models\Actor;
use DvSoft\AttributeChangeLog\Tests\Models\Intervention;

it('links actions back to the causer', function () {
    $actor = Actor::create(['name' => 'Luca']);
    $subject = Intervention::create([
        'name' => 'Primo',
        'status' => 'pending',
    ]);

    AttributeChangeLog::create([
        'subject_type' => Intervention::class,
        'subject_id' => $subject->getKey(),
        'causer_type' => Actor::class,
        'causer_id' => $actor->getKey(),
        'attribute' => 'status',
        'value' => 'pending',
        'type' => 'string',
    ]);

    expect($actor->actions()->count())->toBe(1);
});
