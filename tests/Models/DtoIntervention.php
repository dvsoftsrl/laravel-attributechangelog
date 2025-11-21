<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

use DvSoft\AttributeChangeLog\Traits\LogsAttributeChange;
use DvSoft\AttributeChangeLog\Tests\Helpers\StatusDtoCast;
use Illuminate\Database\Eloquent\Model;

class DtoIntervention extends Model
{
    use LogsAttributeChange;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => StatusDtoCast::class,
    ];

    protected $table = 'interventions';
}
