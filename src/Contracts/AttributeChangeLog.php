<?php

namespace DvSoft\AttributeChangeLog\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface AttributeChangeLog
{
    public function subject(): MorphTo;

    public function causer(): MorphTo;

    public function scopeCausedBy(Builder $query, Model $causer): Builder;

    public function scopeForAttribute(Builder $query, string $attribute): Builder;

    public function scopeOnDate(Builder $query, Carbon $date): Builder;

    public function scopeForSubject(Builder $query, Model $subject): Builder;
}
