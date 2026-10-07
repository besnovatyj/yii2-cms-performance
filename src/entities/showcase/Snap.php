<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

/**
 * Выравнивание относительной нижней границы назад, к началу периода.
 */
enum Snap: string
{
    /** Граница как есть: ровно N месяцев назад. */
    case None = 'none';

    /** К 1 января года, в который попала граница. */
    case Year = 'year';

    /** К открытию сезона, в который попала граница (последний сезон, открывшийся не позже неё). */
    case Season = 'season';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::None => 'Не выравнивать',
            self::Year => 'К началу календарного года',
            self::Season => 'К открытию театрального сезона',
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
