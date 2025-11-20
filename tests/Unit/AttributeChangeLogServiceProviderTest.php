<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider;
use DvSoft\AttributeChangeLog\Exceptions\InvalidConfiguration;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog as AttributeChangeLogModel;
use DvSoft\AttributeChangeLog\Tests\Models\AttributeChangeLogAlternative;

beforeEach(function () {
    config()->set('attributechangelog.attribute_change_log_model', AttributeChangeLogModel::class);
});

it('returns the configured model class', function () {
    config()->set('attributechangelog.attribute_change_log_model', AttributeChangeLogAlternative::class);

    expect(AttributeChangeLogServiceProvider::determineAttributeChangeLogModel())
        ->toBe(AttributeChangeLogAlternative::class);
});

it('throws when the configured class is invalid', function () {
    config()->set('attributechangelog.attribute_change_log_model', \stdClass::class);

    expect(fn () => AttributeChangeLogServiceProvider::determineAttributeChangeLogModel())
        ->toThrow(InvalidConfiguration::class);
});

it('creates an instance of the attribute change log model', function () {
    config()->set('attributechangelog.attribute_change_log_model', AttributeChangeLogAlternative::class);

    $instance = AttributeChangeLogServiceProvider::getAttributeChangeLogModelInstance();

    expect($instance)->toBeInstanceOf(AttributeChangeLogAlternative::class);
});
