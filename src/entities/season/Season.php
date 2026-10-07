<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\season;

use DateTimeImmutable;
use DomainException;
use yii\db\ActiveRecord;

/**
 * Театральный сезон: даты открытия и закрытия назначает руководство театра, поэтому они
 * хранятся для каждого сезона отдельно, а не вычисляются по календарю.
 *
 * Дату закрытия часто назначают ближе к концу сезона, поэтому её может не быть: такой сезон
 * открыт. Открытый сезон — только последний (это проверяет сервис управления сезонами).
 *
 * @property int $id
 * @property string $name
 * @property string $start_date
 * @property string|null $end_date null — сезон открыт, дата закрытия ещё не назначена
 */
class Season extends ActiveRecord
{
    /**
     * @param string $name
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable|null $end null — дата закрытия ещё не назначена
     * @return self
     */
    public static function create(string $name, DateTimeImmutable $start, ?DateTimeImmutable $end): self
    {
        $season = new static();
        $season->edit($name, $start, $end);
        return $season;
    }

    /**
     * @param string $name
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable|null $end null — дата закрытия ещё не назначена
     * @return void
     */
    public function edit(string $name, DateTimeImmutable $start, ?DateTimeImmutable $end): void
    {
        if (trim($name) === '') {
            throw new DomainException('Название сезона не может быть пустым.');
        }
        if ($end !== null && $end < $start) {
            throw new DomainException('Сезон не может закрыться раньше, чем откроется.');
        }

        $this->name = $name;
        $this->start_date = $start->format('Y-m-d');
        $this->end_date = $end?->format('Y-m-d');
    }

    /**
     * Дата закрытия ещё не назначена.
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->end_date === null || $this->end_date === '';
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%performance_seasons}}';
    }
}
