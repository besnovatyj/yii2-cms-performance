<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\helpers;

use Besnovatyj\Performance\entities\performance\Performance;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class PerformanceHelper
{
    public static function statusList(): array
    {
        return [
            Performance::STATUS_DRAFT => 'Draft',
            Performance::STATUS_ACTIVE => 'Active',
        ];
    }

    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    public static function statusLabel($model): string
    {
        switch ($model->status) {
            case Performance::STATUS_DRAFT:
                $class = 'badge bg-secondary';
                $action = 'activate';
                break;
            case Performance::STATUS_ACTIVE:
                $class = 'badge bg-success';
                $action = 'draft';
                break;
            default:
                $class = 'badge bg-secondary';
                $action = 'activate';
        }

        $text = Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->status), [
            'class' => $class,
        ]);
        $url = Url::to([$action, 'id' => $model->id]);
        return Html::a($text, $url, [
            'data' => [
//                'confirm' => "Сменить статус?",
                'method' => 'post',
            ],
        ]);

    }
}
