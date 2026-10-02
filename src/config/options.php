<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

/**
 * Опции модуля для yii2-cms-config.
 *
 * Значения приезжают в Yii::$app->getModule('Performance')->params, откуда их читают виджеты модуля.
 * Значения по умолчанию — в config/config.php.
 */
return [
    'performance_tickets_url' => [
        'path' => 'modules.Performance.params.tickets.url',
        'label' => 'Адрес покупки билетов',
        'description' => 'Ссылка кнопки «Купить билеты» на странице постановки. Пусто — кнопка не выводится',
        'category' => 'Performance',
        'rules' => [
            ['string'],
            ['url'],
        ],
        'inputOptions' => [
            'type' => 'input',
        ],
    ],
    'performance_tickets_label' => [
        'path' => 'modules.Performance.params.tickets.label',
        'label' => 'Подпись кнопки покупки билетов',
        'description' => 'Текст кнопки со ссылкой на покупку билетов',
        'category' => 'Performance',
        'rules' => [
            ['required'],
            ['string', 'max' => 100],
        ],
        'inputOptions' => [
            'type' => 'input',
        ],
    ],
];
