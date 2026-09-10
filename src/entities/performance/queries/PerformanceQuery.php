<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\entities\performance\queries;

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\Taxonomy;
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

    /**
     * Спектакль доступен анонимному посетителю: опубликован сам И лежит в видимом разделе.
     *
     * Одной публикации мало: скрытый раздел не должен «протекать» на фронт своими спектаклями —
     * раздел проверяется целиком, вместе с предками
     * (см. {@see \Besnovatyj\Performance\entities\queries\TaxonomyQuery::visible()}).
     * Спектакль без раздела (`taxonomy_id` NULL) виден: скрывать его не за что.
     *
     * @param string|null $alias алиас таблицы спектаклей, если запрос строится с `alias()`
     */
    public function visible(?string $alias = null): static
    {
        $column = ($alias ? $alias . '.' : '') . 'taxonomy_id';

        return $this->active($alias)->andWhere([
            'or',
            [$column => null],
            [$column => Taxonomy::find()->visible()->select('id')],
        ]);
    }
}
