<?php

namespace DvSoft\AttributeChangeLog\Exceptions;

use DvSoft\AttributeChangeLog\Contracts\AttributeChangeLog;
use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidConfiguration extends Exception
{
    public static function modelIsNotValid(string $className): self
    {
        return new static("The given model class `{$className}` does not implement `".AttributeChangeLog::class.'` or it does not extend `'.Model::class.'`');
    }
}
