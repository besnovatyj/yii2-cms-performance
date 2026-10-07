<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\forms\backend\showcase;

use Besnovatyj\Forms\BaseForm;
use Besnovatyj\Performance\entities\showcase\FromMode;
use Besnovatyj\Performance\entities\showcase\Order;
use Besnovatyj\Performance\entities\showcase\Rule;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\Snap;
use Besnovatyj\Performance\entities\showcase\Source;
use Besnovatyj\Performance\entities\showcase\ToMode;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use DateTimeImmutable;

/**
 * Форма витрины спектаклей: код, название и правило отбора.
 */
class ShowcaseForm extends BaseForm
{
    public ?string $code = null;
    public ?string $name = null;
    public int $sort = 0;
    public string $source = 'rule';
    public ?int $taxonomy_id = null;
    public bool $with_descendants = true;
    public string $from_mode = 'none';
    public ?string $from_date = null;
    public int $from_offset_months = 12;
    public string $from_snap = 'none';
    public string $to_mode = 'today';
    public string $order_by = 'premiere_desc';
    public ?int $items_limit = null;

    private ?Showcase $_showcase = null;

    public function __construct(?Showcase $showcase = null, $config = [])
    {
        if ($showcase) {
            $this->code = $showcase->code;
            $this->name = $showcase->name;
            $this->sort = (int)$showcase->sort;
            $this->source = (string)$showcase->source;
            $this->taxonomy_id = $showcase->taxonomy_id !== null ? (int)$showcase->taxonomy_id : null;
            $this->with_descendants = (bool)$showcase->with_descendants;
            $this->from_mode = (string)$showcase->from_mode;
            $this->from_date = $showcase->from_date;
            $this->from_offset_months = (int)$showcase->from_offset_months;
            $this->from_snap = (string)$showcase->from_snap;
            $this->to_mode = (string)$showcase->to_mode;
            $this->order_by = (string)$showcase->order_by;
            $this->items_limit = $showcase->items_limit !== null ? (int)$showcase->items_limit : null;
            $this->_showcase = $showcase;
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['code'], 'match', 'pattern' => '/^[a-z0-9\-_]+$/', 'message' => 'Код может содержать только латинские буквы в нижнем регистре, цифры, дефис и подчёркивание.'],
            [['code'], 'unique', 'targetClass' => Showcase::class, 'filter' => $this->_showcase ? ['<>', 'id', $this->_showcase->id] : null],
            [['sort'], 'integer'],
            [['source'], 'in', 'range' => array_keys(Source::options())],
            [['taxonomy_id'], 'integer'],
            [['taxonomy_id'], 'exist', 'targetClass' => Taxonomy::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['with_descendants'], 'boolean'],
            [['from_mode'], 'in', 'range' => array_keys(FromMode::options())],
            [['from_date'], 'date', 'format' => 'php:Y-m-d'],
            [['from_date'], 'required', 'when' => fn(): bool => $this->from_mode === FromMode::Fixed->value, 'whenClient' => 'function () { return false; }'],
            [['from_offset_months'], 'integer', 'min' => 0, 'max' => 240],
            [['from_snap'], 'in', 'range' => array_keys(Snap::options())],
            [['to_mode'], 'in', 'range' => array_keys(ToMode::options())],
            [['order_by'], 'in', 'range' => array_keys(Order::options())],
            [['items_limit'], 'integer', 'min' => 1],
        ];
    }

    /**
     * Правило из введённых значений. Неизвестные значения перечислений заменяются
     * значениями по умолчанию: метод зовёт и предпросмотр периода до валидации.
     *
     * @return Rule
     */
    public function rule(): Rule
    {
        $fromMode = FromMode::tryFrom($this->from_mode) ?? FromMode::None;
        $fromDate = $this->parseDate($this->from_date);
        if ($fromMode === FromMode::Fixed && $fromDate === null) {
            $fromMode = FromMode::None;
        }

        return new Rule(
            source: Source::tryFrom($this->source) ?? Source::Rule,
            taxonomyId: $this->taxonomy_id,
            withDescendants: $this->with_descendants,
            fromMode: $fromMode,
            fromDate: $fromDate,
            fromOffsetMonths: max(0, $this->from_offset_months),
            fromSnap: Snap::tryFrom($this->from_snap) ?? Snap::None,
            toMode: ToMode::tryFrom($this->to_mode) ?? ToMode::Today,
            order: Order::tryFrom($this->order_by) ?? Order::PremiereDesc,
            limit: $this->items_limit !== null && $this->items_limit > 0 ? $this->items_limit : null,
        );
    }

    /**
     * Дерево разделов для выпадающего списка: id => отступ+название.
     *
     * @return array<int, string>
     */
    public function taxonomiesList(): array
    {
        return new TreeQueryScope(Taxonomy::class)->dropdownTree();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'code' => 'Код',
            'name' => 'Название',
            'sort' => 'Сортировка',
            'source' => 'Откуда брать спектакли',
            'taxonomy_id' => 'Раздел афиши',
            'with_descendants' => 'Включая подразделы',
            'from_mode' => 'Премьера: нижняя граница',
            'from_date' => 'Дата',
            'from_offset_months' => 'Месяцев назад от сегодня',
            'from_snap' => 'Выровнять границу',
            'to_mode' => 'Премьера: верхняя граница',
            'order_by' => 'Порядок',
            'items_limit' => 'Сколько выводить',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints(): array
    {
        return [
            'code' => 'По коду витрину вызывают тема и шорткод.',
            'source' => '«По правилу» — спектакли отбираются разделом и периодом премьеры. «Ручной список» — только добавленные вручную; раздел и период не действуют.',
            'from_offset_months' => '12 — год. Граница сдвигается сама каждый день.',
            'from_snap' => 'Например, 12 месяцев + «к открытию сезона» — с открытия сезона, в который попала дата год назад.',
            'to_mode' => '«По закрытие текущего сезона» включает объявленные, но ещё не состоявшиеся премьеры.',
            'items_limit' => 'Пусто — все.',
        ];
    }

    /**
     * @param string|null $value
     * @return DateTimeImmutable|null
     */
    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date === false ? null : $date;
    }
}
