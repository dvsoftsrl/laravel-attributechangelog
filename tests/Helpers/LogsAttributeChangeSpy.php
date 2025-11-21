<?php

namespace DvSoft\AttributeChangeLog\Tests\Helpers;

use DvSoft\AttributeChangeLog\Tests\Models\Intervention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LogsAttributeChangeSpy extends Intervention
{
    protected static array $recordEvents = [];

    public static function shouldRecordPublic(Model $model, string $attribute, array $dirty, string $event): bool
    {
        return static::shouldRecordAttributeChange($model, $attribute, $dirty, $event);
    }

    public static function relationAttributeChangedPublic(string $relation, array $dirty): bool
    {
        return static::relationAttributeChanged($relation, $dirty);
    }

    public static function possibleRelationForeignKeysPublic(string $relation): array
    {
        return static::possibleRelationForeignKeys($relation);
    }

    public static function setRecordEvents(array $events): void
    {
        static::$recordEvents = $events;
    }

    public static function resetRecordEvents(): void
    {
        static::$recordEvents = [];
    }

    public function shouldLogAttributeChangeEventPublic(string $event): bool
    {
        return $this->shouldLogAttributeChangeEvent($event);
    }

    public static function recordEventsPublic(): Collection
    {
        return static::attributeChangeEventsToRecord();
    }
}
