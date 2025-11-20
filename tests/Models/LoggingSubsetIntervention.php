<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

class LoggingSubsetIntervention extends Intervention
{
    protected static $attributesToBeLogged = [
        'status',
    ];
}
