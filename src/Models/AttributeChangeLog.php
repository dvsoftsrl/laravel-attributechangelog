<?php

namespace DvSoft\AttributeChangeLog\Models;

use Carbon\Carbon;
use DateTime;
use DvSoft\AttributeChangeLog\Contracts\AttributeChangeLog as AttributeChangeLogContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttributeChangeLog extends Model implements AttributeChangeLogContract
{
    public $guarded = [];

    public $timestamps = false;

    protected $dataTypes = ['boolean', 'integer', 'double', 'float', 'string', 'NULL'];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('attributechangelog.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('attributechangelog.table_name'));
        }

        parent::__construct($attributes);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('attribute', $attribute);
    }

    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeAttributeEditedOn(Builder $query, string $attribute, Carbon $date): Builder
    {
        return $query->forAttribute($attribute)
            ->onDate($date);
    }

    public function scopeAttributeEditedBetween(Builder $query, string $attribute, Carbon $from, Carbon $to): Builder
    {
        return $query->forAttribute($attribute)
            ->whereBetween('created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ]);
    }

    public function scopeEditedSince(Builder $query, Model $subject, Carbon $since): Builder
    {
        return $query->forSubject($subject)
            ->where('created_at', '>=', $since);
    }

    public function scopeLatestForSubject(Builder $query, Model $subject): Builder
    {
        return $query->forSubject($subject)->latest('created_at');
    }

    /**
     * Set the value and type.
     */
    public function setValueAttribute($value)
    {
        $type = gettype($value);

        if (is_array($value)) {
            $this->type = 'array';
            $this->attributes['value'] = json_encode($value);
        } elseif ($value instanceof DateTime) {
            $this->type = 'datetime';
            $this->attributes['value'] = $this->fromDateTime($value);
        } elseif (is_object($value)) {
            $this->type = 'object';
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->type = in_array($type, $this->dataTypes) ? $type : 'string';
            $this->attributes['value'] = $value;
        }
    }

    public function getValueAttribute($value)
    {
        $type = $this->type ?: 'null';

        switch ($type) {
            case 'array':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            case 'datetime':
                return $this->asDateTime($value);
        }

        if (in_array($type, $this->dataTypes)) {
            settype($value, $type);
        }

        return $value;
    }
}
