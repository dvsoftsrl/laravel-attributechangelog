<?php

namespace DvSoft\AttributeChangeLog\Traits;

use DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider;
use DvSoft\AttributeChangeLog\Models\AttributeChangeLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CausesActivity
{
    /** @return MorphMany<AttributeChangeLog, $this> */
    public function actions(): MorphMany
    {
        return $this->morphMany(
            AttributeChangeLogServiceProvider::determineAttributeChangeLogModel(),
            'causer'
        );
    }
}
