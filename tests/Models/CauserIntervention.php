<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

class CauserIntervention extends Intervention
{
    protected static ?Actor $explicitCauser = null;

    public function attributeChangeCauser(): ?Actor
    {
        return static::$explicitCauser;
    }

    public static function setExplicitCauser(Actor $actor): void
    {
        static::$explicitCauser = $actor;
    }
}
