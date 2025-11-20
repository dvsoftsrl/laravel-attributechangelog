<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'partners';

    protected $fillable = [
        'name',
    ];
}
