<?php

namespace DvSoft\AttributeChangeLog\Tests\Models;

use DvSoft\AttributeChangeLog\Traits\CausesActivity;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use CausesActivity;

    protected $table = 'actors';

    protected $fillable = [
        'name',
    ];
}
