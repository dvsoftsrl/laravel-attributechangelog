<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

class JsonIntervention extends Intervention
{
    protected static $attributesToBeLogged = [
        'payload->meta.inner',
    ];

    protected $fillable = [
        'name',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
