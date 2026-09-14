<?php

namespace App\Services;

class MenuBuilder
{
    public static function build(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return collect(config('menu'))
            ->filter(fn (array $item) => static::authorized($user, $item))
            ->map(function (array $item) use ($user) {
                if (! empty($item['children'])) {
                    $item['children'] = collect($item['children'])
                        ->filter(fn (array $child) => static::authorized($user, $child))
                        ->values()
                        ->all();
                }

                return $item;
            })
            ->values()
            ->all();
    }

    protected static function authorized($user, array $item): bool
    {
        if (empty($item['permission'])) {
            return true;
        }

        return $user->canAny((array) $item['permission']);
    }
}
