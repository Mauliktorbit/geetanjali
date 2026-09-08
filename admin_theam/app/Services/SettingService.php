<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    protected string $cacheKey = 'app.settings.map';

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        return $this->castValue($all[$key]['value'], $all[$key]['type']);
    }

    public function set(string $key, mixed $value, string $group = 'general', ?string $type = null): Setting
    {
        $type ??= $this->detectType($value);
        $stored = $this->serializeValue($value, $type);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $stored,
                'type' => $type,
            ]
        );

        $this->flush();

        return $setting;
    }

    public function getGroup(string $group): array
    {
        $result = [];

        foreach ($this->all() as $key => $row) {
            if ($row['group'] === $group) {
                $result[$key] = $this->castValue($row['value'], $row['type']);
            }
        }

        return $result;
    }

    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    public function all(): array
    {
        return Cache::remember($this->cacheKey, 3600, function () {
            return Setting::query()
                ->get(['key', 'value', 'type', 'group'])
                ->mapWithKeys(fn (Setting $s) => [
                    $s->key => [
                        'value' => $s->value,
                        'type' => $s->type,
                        'group' => $s->group,
                    ],
                ])
                ->all();
        });
    }

    public function flush(): void
    {
        Cache::forget($this->cacheKey);
    }

    protected function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    protected function serializeValue(mixed $value, string $type): ?string
    {
        return match ($type) {
            'boolean', 'bool' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => $value === null ? null : (string) $value,
        };
    }

    protected function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'decimal' => (float) $value,
            'json', 'array' => json_decode($value ?? '[]', true),
            default => $value,
        };
    }
}
