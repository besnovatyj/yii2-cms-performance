<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\blocks;

use Besnovatyj\Contracts\block\BlockType;
use Besnovatyj\Performance\readModels\ShowcaseReadRepository;

/**
 * Тип блока «Витрина спектаклей» для модуля блоков (`Performance:showcase`).
 *
 * Блок хранит только выбор: какая витрина, какой раскладкой, сколько карточек и куда ведёт ссылка
 * «Вся афиша». Отбор спектаклей, период и фото — настройки самой витрины в её админке, блок их
 * не дублирует. Поставщика данных у типа нет: шаблон выводит витрину виджетом
 * {@see \Besnovatyj\Performance\widgets\showcase\ShowcaseWidget}, который выбирает данные сам.
 *
 * Шаблон по умолчанию — `src/views/blockTypes/showcase.php`; тема перекрывает его overlay-ем
 * представлений модуля: `themes/{тема}/modules/Performance/views/blockTypes/showcase.php`.
 */
final class ShowcaseBlockType
{
    /** Локальный id типа; полный — `Performance:showcase`. */
    public const string ID = 'showcase';

    /** Раскладки витрины: имя представления виджета => подпись. Пакет умеет только сетку. */
    public const array LAYOUTS = [
        'default' => 'Сетка',
        'slider' => 'Лента (если тема её поддерживает)',
    ];

    public function __construct(private readonly ShowcaseReadRepository $showcases)
    {
    }

    public function definition(): BlockType
    {
        // Поле select без вариантов модуль блоков не принимает: пока витрин нет, вариант-заглушка.
        $showcases = $this->showcases->options() ?: ['' => '— витрин ещё нет, создайте в «Спектаклях» —'];

        return new BlockType(
            id: self::ID,
            label: 'Витрина спектаклей',
            view: dirname(__DIR__) . '/views/blockTypes/showcase.php',
            fields: [
                'heading' => ['kind' => 'text', 'label' => 'Заголовок раздела', 'maxLength' => 120,
                    'hint' => 'Пусто — без заголовка.'],
                'showcase' => ['kind' => 'select', 'label' => 'Витрина', 'required' => true, 'options' => $showcases,
                    'hint' => 'Что показывать, за какой период и с какими фото — настраивается в самой витрине. '
                        . 'Выключенная витрина на сайт не выводится.'],
                'layout' => ['kind' => 'select', 'label' => 'Раскладка', 'default' => 'default', 'options' => self::LAYOUTS],
                'limit' => ['kind' => 'select', 'label' => 'Сколько карточек', 'default' => '', 'options' => [
                    '' => 'как задано в витрине', '3' => '3', '4' => '4', '6' => '6', '8' => '8', '9' => '9', '12' => '12',
                ]],
                'more' => ['kind' => 'link', 'label' => 'Ссылка «вся афиша»',
                    'hint' => 'Пусто — без ссылки.'],
                'moreLabel' => ['kind' => 'text', 'label' => 'Подпись ссылки', 'default' => 'Вся афиша', 'maxLength' => 40],
            ],
            hint: 'Спектакли из витрины модуля «Спектакли»: премьеры, репертуар месяца и т.п.',
        );
    }
}
