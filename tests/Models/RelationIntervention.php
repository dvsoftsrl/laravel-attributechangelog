<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

class RelationIntervention extends Intervention
{

    protected static $attributesToBeLogged = [
        'partner.name',
    ];

    protected $fillable = [
        'name',
        'status',
        'partner_id',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
