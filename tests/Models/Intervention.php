<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

use DvSoft\AttributeChangeLog\Traits\LogsAttributeChange;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use LogsAttributeChange;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $table = 'interventions';
}
