<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\entities\performance\queries;

use Besnovatyj\Performance\entities\performance\Performance;
use yii\db\ActiveQuery;

class PerformanceQuery extends ActiveQuery
{
    /**
     * @param null $alias
     * @return $this
     */
    public function active($alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Performance::STATUS_ACTIVE,
        ]);
    }
}
