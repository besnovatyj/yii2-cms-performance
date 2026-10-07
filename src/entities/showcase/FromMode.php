<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

/**
 * Как задана нижняя граница даты премьеры.
 */
enum FromMode: string
{
    /** Без нижней границы. */
    case None = 'none';

    /** С конкретной даты. */
    case Fixed = 'fixed';

    /** N месяцев назад от сегодняшнего дня (граница сдвигается сама). */
    case Relative = 'relative';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::None => 'Без ограничения',
            self::Fixed => 'С конкретной даты',
            self::Relative => 'N месяцев назад от сегодня',
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
