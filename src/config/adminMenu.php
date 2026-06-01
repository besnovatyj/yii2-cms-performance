<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [
    // Performances list
    [
        'label' => 'Performances list',
        'iconClass' => 'bi bi-collection me-1',
        'url' => ['/Performance/backend/performance/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Performance/backend/performance');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Performances',
                    'groupIcon' => 'bi bi-person-video3',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
    // Taxonomies
    [
        'label' => 'Taxonomies',
        'iconClass' => 'bi bi-list-ol me-1',
        'url' => ['/Performance/backend/taxonomy/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Performance/backend/taxonomy');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Performances',
                    'groupIcon' => 'bi bi-person-video3',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
    // Tags
    [
        'label' => 'Tags',
        'iconClass' => 'bi bi-tags me-1',
        'url' => ['/Performance/backend/tag/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Performance/backend/tag');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Performances',
                    'groupIcon' => 'bi bi-person-video3',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
];
