<?php

namespace DvSoft\AttributeChangeLog\Traits;

use Carbon\Carbon;
use DvSoft\AttributeChangeLog\AttributeChangeLogServiceProvider;
use DvSoft\AttributeChangeLog\AttributeChangeLogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogsAttributeChange
{
    public bool $enableLoggingModelsEvents = true;

    public static function bootLogsAttributeChange(): void
    {
        static::attributeChangeEventsToRecord()->each(function ($eventName) {
            static::$eventName(function (Model $model) use ($eventName) {

                if (! $model->shouldLogAttributeChangeEvent($eventName)) {
                    return;
                }

                $changes = $model->attributeValuesToRecord($eventName);

                if (empty($changes)) {
                    return;
                }

                $model->recordAttributeChanges($eventName, $changes);
            });
        });
    }

    public function disableAttributesLogging(): self
    {
        $this->enableLoggingModelsEvents = false;

        return $this;
    }

    public function enableAttributesLogging(): self
    {
        $this->enableLoggingModelsEvents = true;

        return $this;
    }

    public function attributeChangeActivities(): MorphMany
    {
        return $this->morphMany(AttributeChangeLogServiceProvider::determineAttributeChangeLogModel(), 'subject');
    }

    /**
     * Get the event names that should be recorded.
     **/
    protected static function attributeChangeEventsToRecord(): Collection
    {
        if (isset(static::$recordEvents)) {
            return collect(static::$recordEvents);
        }

        return collect([
            'created',
            'updated',
        ]);
    }

    protected function shouldLogAttributeChangeEvent(string $eventName): bool
    {
        $logStatus = app(AttributeChangeLogStatus::class);

        if (! $this->enableLoggingModelsEvents || $logStatus->disabled()) {
            return false;
        }

        return static::attributeChangeEventsToRecord()->contains($eventName);
    }

    public function attributeValuesToRecord(string $processingEvent): array
    {
        // no loggable attributes, no values to be logged!
        if (! count($this->attributesToRecord())) {
            return [];
        }

        return static::collectAttributeChanges($this, $processingEvent);
    }

    public function attributeChangeLogs(): MorphMany
    {
        return $this->morphMany(AttributeChangeLogServiceProvider::determineAttributeChangeLogModel(), 'subject');
    }

    public function lastAttributeChange(string $attribute)
    {
        return $this->attributeChangeLogs()
            ->forAttribute($attribute)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function scopeEditedAttributeOn(Builder $query, string $attribute, Carbon $date)
    {
        return $query->whereHas('attributeChangeLogs', function ($query) use ($attribute, $date) {
            $query->attributeEditedOn($attribute, $date);
        });
    }

    public function attributesToRecord(): array
    {
        if (isset(static::$attributesToBeLogged)) {
            return static::$attributesToBeLogged;
        }

        return $this->getFillable();
    }

    public static function collectAttributeChanges(Model $model, string $event): array
    {
        $changes = [];
        $attributes = $model->attributesToRecord();
        $dirty = $model->getChanges();

        foreach ($attributes as $attribute) {
            if (! static::shouldRecordAttributeChange($model, $attribute, $dirty, $event)) {
                continue;
            }

            if (Str::contains($attribute, '->')) {
                $key = str_replace('->', '.', $attribute);

                $changes[$key] = static::resolveModelJsonAttributeValue($model, $attribute);

                continue;
            }

            if (Str::contains($attribute, '.')) {
                $relatedChanges = self::resolveRelatedModelAttributeValues($model, $attribute);

                if (! empty($relatedChanges)) {
                    $changes += $relatedChanges;
                }

                continue;
            }

            $changes[$attribute] = $model->getAttribute($attribute);
        }

        return $changes;
    }

    protected static function shouldRecordAttributeChange(Model $model, string $attribute, array $dirty, string $event): bool
    {
        if ($event === 'created') {
            return true;
        }

        $attributeKey = Str::contains($attribute, '->')
            ? Str::before($attribute, '->')
            : $attribute;

        if (Str::contains($attributeKey, '.')) {
            $relation = Str::before($attributeKey, '.');

            return static::relationAttributeChanged($relation, $dirty);
        }

        return array_key_exists($attributeKey, $dirty);
    }

    protected static function relationAttributeChanged(string $relation, array $dirty): bool
    {
        if (array_key_exists($relation, $dirty)) {
            return true;
        }

        foreach (static::possibleRelationForeignKeys($relation) as $key) {
            if (array_key_exists($key, $dirty)) {
                return true;
            }
        }

        return false;
    }

    protected static function possibleRelationForeignKeys(string $relation): array
    {
        $snake = Str::snake($relation);
        $camel = Str::camel($relation);

        return array_unique([
            "{$relation}_id",
            "{$relation}_uuid",
            "{$relation}_type",
            "{$snake}_id",
            "{$snake}_uuid",
            "{$snake}_type",
            "{$camel}_id",
            "{$camel}_uuid",
            "{$camel}_type",
        ]);
    }

    protected function recordAttributeChanges(string $_event, array $changes): void
    {
        $attributeChangeLogModel = AttributeChangeLogServiceProvider::determineAttributeChangeLogModel();
        $causer = $this->attributeChangeCauser();

        foreach ($changes as $attribute => $value) {
            $log = new $attributeChangeLogModel;

            $log->subject()->associate($this);
            $log->attribute = $attribute;
            $log->value = $value;
            if ($causer) {
                $log->causer()->associate($causer);
            }
            $log->created_at = $log->freshTimestamp();
            $log->save();
        }
    }

    protected function attributeChangeCauser(): ?Model
    {
        return Auth::user();
    }

    protected static function resolveRelatedModelAttributeValues(Model $model, string $attribute): array
    {
        $relatedModelNames = explode('.', $attribute);
        $relatedAttribute = array_pop($relatedModelNames);

        $attributeName = [];
        $relatedModel = $model;

        do {
            $attributeName[] = $relatedModelName = static::resolveRelatedModelRelationName($relatedModel, array_shift($relatedModelNames));

            $relatedModel = $relatedModel->$relatedModelName ?? $relatedModel->$relatedModelName();
        } while (! empty($relatedModelNames));

        $attributeName[] = $relatedAttribute;

        return [implode('.', $attributeName) => $relatedModel->$relatedAttribute ?? null];
    }

    protected static function resolveRelatedModelRelationName(Model $model, string $relation): string
    {
        return Arr::first([
            $relation,
            Str::snake($relation),
            Str::camel($relation),
        ], function (string $method) use ($model): bool {
            return method_exists($model, $method);
        }, $relation);
    }

    protected static function resolveModelJsonAttributeValue(Model $model, string $attribute): mixed
    {
        $path = explode('->', $attribute);
        $modelAttribute = array_shift($path);
        $modelAttribute = collect($model->getAttribute($modelAttribute));

        return data_get($modelAttribute, implode('.', $path));
    }
}
