<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use DvSoft\AttributeChangeLog\AttributeChangeLogStatus;
use Illuminate\Config\Repository;

it('reflects the enabled flag from config', function () {
    $config = new Repository(['attributechangelog' => ['enabled' => false]]);

    $status = new AttributeChangeLogStatus($config);

    expect($status->disabled())->toBeTrue();
    expect($status->enable())->toBeTrue();
    expect($status->disabled())->toBeFalse();
});

it('can be disabled explicitly', function () {
    $config = new Repository(['attributechangelog' => ['enabled' => true]]);

    $status = new AttributeChangeLogStatus($config);

    expect($status->disable())->toBeFalse();
    expect($status->disabled())->toBeTrue();
});
