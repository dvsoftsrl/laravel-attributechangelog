<?php

namespace DvSoft\AttributeChangeLog\Tests\Unit;

use DvSoft\AttributeChangeLog\Tests\Helpers\LogsAttributeChangeSpy;
use Illuminate\Database\Eloquent\Model;

it('always records when the event is created', function () {
    $model = new LogsAttributeChangeSpy();

    expect(LogsAttributeChangeSpy::shouldRecordPublic($model, 'name', [], 'created'))->toBeTrue();
});

it('records json keys when the root attribute is dirty', function () {
    $model = new LogsAttributeChangeSpy();

    $dirty = ['payload' => ['inner' => 'value']];

    expect(LogsAttributeChangeSpy::shouldRecordPublic($model, 'payload->inner', $dirty, 'updated'))->toBeTrue();
});

it('records relation attributes when the foreign key changed', function () {
    $model = new LogsAttributeChangeSpy();

    $dirty = ['partner_id' => 2];

    expect(LogsAttributeChangeSpy::shouldRecordPublic($model, 'partner.name', $dirty, 'updated'))->toBeTrue();
    expect(LogsAttributeChangeSpy::relationAttributeChangedPublic('partner', $dirty))->toBeTrue();
    expect(LogsAttributeChangeSpy::possibleRelationForeignKeysPublic('partner'))->toContain('partner_id');
});

it('detects relation attribute change when relation key is dirty', function () {
    $model = new LogsAttributeChangeSpy();
    $dirty = ['partner' => 5];

    expect(LogsAttributeChangeSpy::relationAttributeChangedPublic('partner', $dirty))->toBeTrue();
});

it('does not record when the attribute is not dirty', function () {
    $model = new LogsAttributeChangeSpy();

    expect(LogsAttributeChangeSpy::shouldRecordPublic($model, 'name', [], 'updated'))->toBeFalse();
});

it('returns false when no relation keys are dirty', function () {
    expect(LogsAttributeChangeSpy::relationAttributeChangedPublic('missing', []))->toBeFalse();
});

it('respects custom record events', function () {
    LogsAttributeChangeSpy::setRecordEvents(['updated']);

    $model = new LogsAttributeChangeSpy();

    expect($model->shouldLogEventPublic('created'))->toBeFalse();
    expect($model->shouldLogEventPublic('updated'))->toBeTrue();
});

afterEach(function () {
    LogsAttributeChangeSpy::resetRecordEvents();
});
