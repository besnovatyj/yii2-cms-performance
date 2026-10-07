<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\forms\backend\season;

use Besnovatyj\Forms\BaseForm;
use Besnovatyj\Performance\entities\season\Season;
use DateTimeImmutable;

/**
 * Форма театрального сезона.
 */
class SeasonForm extends BaseForm
{
    public ?string $name = null;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public function __construct(?Season $season = null, $config = [])
    {
        if ($season) {
            $this->name = $season->name;
            $this->start_date = $season->start_date;
            $this->end_date = $season->end_date;
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'start_date'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'type' => 'string', 'message' => 'Сезон не может закрыться раньше, чем откроется.'],
            [['end_date'], 'default', 'value' => null],
        ];
    }

    /**
     * @return DateTimeImmutable
     */
    public function start(): DateTimeImmutable
    {
        return new DateTimeImmutable((string)$this->start_date);
    }

    /**
     * @return DateTimeImmutable|null null — дата закрытия ещё не назначена
     */
    public function end(): ?DateTimeImmutable
    {
        return ($this->end_date === null || $this->end_date === '') ? null : new DateTimeImmutable($this->end_date);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Название',
            'start_date' => 'Открытие сезона',
            'end_date' => 'Закрытие сезона',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints(): array
    {
        return [
            'name' => 'Например, «2025/2026».',
            'end_date' => 'Пусто — сезон открыт, дата закрытия ещё не назначена. Открытым может быть только последний сезон.',
        ];
    }
}
