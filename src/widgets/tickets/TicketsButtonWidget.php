<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\widgets\tickets;

use Besnovatyj\Performance\Module;
use Yii;
use yii\base\Widget;

/**
 * Кнопка «Купить билеты» — ссылка на внешний сервис продажи билетов.
 *
 * Адрес и подпись задаются в модуле конфигурации (опции `performance_tickets_url` и
 * `performance_tickets_label`, параметры модуля `tickets.url` и `tickets.label`) и общие для всех
 * постановок. Адрес не задан или модуль не установлен — виджет ничего не выводит, поэтому
 * проверять это в разметке страницы не нужно.
 *
 * Разметка пакета — обычная кнопка Bootstrap 5. Тема подставляет свою через {@see $viewPath}
 * (обычно наследником с другим значением по умолчанию): представления виджета лежат вне
 * `src/views` пакета, и карта путей темы до них не достаёт.
 *
 * ```php
 * <?= TicketsButtonWidget::widget() ?>
 * <?= TicketsButtonWidget::widget(['label' => 'Билеты', 'url' => 'https://example.com/tickets']) ?>
 * ```
 */
class TicketsButtonWidget extends Widget
{
    /** Подпись, если в параметрах модуля она не задана. */
    public const string DEFAULT_LABEL = 'Купить билеты';

    /** Подпись кнопки. null — из параметров модуля. */
    public ?string $label = null;

    /** Адрес ссылки. null — из параметров модуля. */
    public ?string $url = null;

    /**
     * Директория с представлением `button`.
     *
     * null — представление пакета.
     */
    public ?string $viewPath = null;

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
        $params = $this->ticketsParams();

        $url = $this->url ?? trim((string)($params['url'] ?? ''));
        if ($url === '') {
            return '';
        }

        $label = $this->label ?? trim((string)($params['label'] ?? ''));

        return $this->render('button', [
            'url' => $url,
            'label' => $label !== '' ? $label : self::DEFAULT_LABEL,
        ]);
    }

    /**
     * Параметры кнопки из модуля (их выставляет модуль конфигурации).
     *
     * @return array{url?: mixed, label?: mixed} пустой массив — модуль не установлен
     */
    private function ticketsParams(): array
    {
        if (!Yii::$app->hasModule(Module::MODULE_ID)) {
            return [];
        }

        $params = Yii::$app->getModule(Module::MODULE_ID)->params['tickets'] ?? [];

        return is_array($params) ? $params : [];
    }
}
