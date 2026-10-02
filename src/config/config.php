<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [
    'id' => 'Performance',
    'params' => [
        'iconClass' => 'bi bi-person-video3',

        // Кнопка покупки билетов (виджет TicketsButtonWidget). Адрес и подпись задаются в модуле
        // конфигурации, пустой адрес — кнопка не выводится.
        'tickets' => [
            'url' => '',
            'label' => 'Купить билеты',
        ],

        'directories' => true, // Если для работы модуля необходимы директории для статики
    ],
];
