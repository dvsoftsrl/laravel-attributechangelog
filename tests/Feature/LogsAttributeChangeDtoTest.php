<?php

namespace DvSoft\AttributeChangeLog\Tests\Feature;

use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use DvSoft\AttributeChangeLog\Tests\Helpers\StatusDto;
use DvSoft\AttributeChangeLog\Tests\Models\DtoIntervention;

it('remembers the exact object logged for DTO attributes', function () {
    $model = DtoIntervention::create([
        'name' => 'DTO',
        'status' => new StatusDto('initial'),
    ]);

    $model->update([
        'status' => new StatusDto('approved'),
    ]);

    $log = AttributeChangeLog::where('attribute', 'status')
        ->orderByDesc('id')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->value_class)->toBe(StatusDto::class);
    expect($log->value)->toBeInstanceOf(StatusDto::class);
    expect($log->value->value())->toBe('approved');
});
