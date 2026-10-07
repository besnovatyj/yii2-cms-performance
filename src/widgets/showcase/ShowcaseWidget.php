<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\widgets\showcase;

use Besnovatyj\Performance\readModels\ShowcaseReadRepository;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\Widget;

/**
 * Витрина спектаклей по коду.
 *
 * Отбор, период, фото и порядок настраиваются в админке витрины; виджет только выводит.
 * Нет активной витрины с таким кодом или в ней пусто — ничего не выводит.
 *
 * Разметка пакета — сетка карточек Bootstrap 5 (`views/default.php`). Тема подставляет свою
 * через {@see $viewPath} — обычно наследником с другим значением по умолчанию: представления
 * виджета лежат вне `src/views` пакета, и карта путей темы до них не достаёт. Нескольким
 * раскладкам (сетка, слайдер) — несколько файлов в каталоге темы, выбор через {@see $view}.
 *
 * Параметры приходят и из шорткода, поэтому строковые значения проверяются, а не
 * используются как есть.
 *
 * ```php
 * <?= ShowcaseWidget::widget(['code' => 'premieres']) ?>
 * <?= ShowcaseWidget::widget(['code' => 'premieres', 'limit' => 3, 'view' => 'slider']) ?>
 * ```
 * Шорткод: `[performanceShowcase code=premieres limit=3]`.
 */
class ShowcaseWidget extends Widget
{
    /** Код витрины. */
    public string $code = '';

    /** Сколько карточек вывести; null — сколько задано в витрине. */
    public ?int $limit = null;

    /** Имя представления в {@see getViewPath()}: латиница, цифры, дефис, подчёркивание. */
    public string $view = 'default';

    /** Профиль превью изображения ({@see \Besnovatyj\Performance\entities\performance\Image}). */
    public string $thumbProfile = 'frontend_list';

    /**
     * Директория с представлениями.
     *
     * null — представления пакета.
     */
    public ?string $viewPath = null;

    public function __construct(private readonly ShowcaseReadRepository $showcases, $config = [])
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(): string
    {
        return $this->viewPath ?? parent::getViewPath();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $code = trim($this->code);
        if ($code === '' || !preg_match('/^[a-z0-9_-]+$/', $this->view)) {
            return '';
        }

        $showcase = $this->showcases->findByCode($code);
        if ($showcase === null) {
            return '';
        }

        $today = new DateTimeImmutable('today', new DateTimeZone(Yii::$app->timeZone));
        $cards = $this->showcases->cards($showcase, $today, $this->limit !== null && $this->limit > 0 ? $this->limit : null);
        if ($cards === []) {
            return '';
        }

        return $this->render($this->view, [
            'showcase' => $showcase,
            'cards' => $cards,
            'thumbProfile' => $this->thumbProfile,
        ]);
    }
}
