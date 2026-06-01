<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\readModels;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\Tag;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\Expression;

class PerformanceReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    public function count(): int
    {
        return Performance::find()->active()->count();
    }

    public function getAllByRange(int $offset, int $limit): array
    {
        return Performance::find()->alias('p')->active('p')->orderBy(['sort' => SORT_ASC])->limit($limit)->offset($offset)->all();
    }

    public function getAllIterator(): iterable
    {
        return Performance::find()->alias('p')->active('p')->with('mainImage', 'brand')->each();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->active('p')->with('mainImage');
        return $this->getProvider($query);
    }

    public function getAllByTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->active('p')->with('mainImage', 'taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->andWhere(['p.taxonomy_id' => $ids]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->active('p')->with('mainImage');
        $query->joinWith(['tagAssignments ta'], false);
        $query->andWhere(['ta.tag_id' => $tag->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

//    public function getFeatured($limit): array
//    {
//        return Performance::find()->with('mainImage')->orderBy(['id' => SORT_DESC])->limit($limit)->all();
//    }

    public function getRand($limit): array
    {
        return Performance::find()->active()->orderBy(new Expression('rand()'))->limit($limit)->all();
    }

    public function find($id): ?Performance
    {
        /** @var $performances Performance */
        $performances = Performance::find()->active()->andWhere(['id' => $id])->one();
        return $performances;
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => ['p.id' => SORT_ASC],
                        'desc' => ['p.id' => SORT_DESC],
                    ],
                    'title' => [
                        'asc' => ['p.title' => SORT_ASC],
                        'desc' => ['p.title' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSizeLimit' => [15, 100],
                'pageSize' => 12,
                'pageSizeParam' => false,
            ]
        ]);
    }
}
