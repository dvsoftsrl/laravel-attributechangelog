<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use DvSoft\AttributeChangeLog\Exceptions\InvalidConfiguration;

it('formats the invalid configuration message', function () {
    $exception = InvalidConfiguration::modelIsNotValid('FooModel');

    expect($exception->getMessage())->toContain('FooModel');
});
