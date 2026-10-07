<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Contracts\adminMenu\AdminMenuLocation;
use Besnovatyj\Contracts\adminMenu\AdminMenuPlacement;

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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Performances',
                    groupIcon: 'bi bi-person-video3',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],
    // Showcases
    [
        'label' => 'Showcases',
        'iconClass' => 'bi bi-easel me-1',
        'url' => ['/Performance/backend/showcase/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Performance/backend/showcase');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Performances',
                    groupIcon: 'bi bi-person-video3',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],
    // Seasons
    [
        'label' => 'Seasons',
        'iconClass' => 'bi bi-calendar-range me-1',
        'url' => ['/Performance/backend/season/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Performance/backend/season');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Performances',
                    groupIcon: 'bi bi-person-video3',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Performances',
                    groupIcon: 'bi bi-person-video3',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],
];
