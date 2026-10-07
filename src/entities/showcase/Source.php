<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

/**
 * Откуда витрина берёт спектакли.
 */
enum Source: string
{
    /** Спектакли отбираются правилом (раздел, период премьеры); элементы витрины — поправки к отбору. */
    case Rule = 'rule';

    /** Состав витрины собирается вручную; элементы витрины и есть её состав. */
    case Manual = 'manual';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Rule => 'По правилу',
            self::Manual => 'Ручной список',
        };
    }

    /**
     * Значения для выпадающего списка.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(array_map(static fn(self $c): array => [$c->value, $c->label()], self::cases()), 1, 0);
    }
}
