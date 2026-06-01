<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\forms\backend\search;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\helpers\PerformanceHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Besnovatyj\Performance\entities\performance\Performance;

class PerformanceSearch extends Model
{
    public int|null $id = null;
    public int|null $taxonomy_id = null;
    public string|null $title = null;
    public string|null $author = null;
    public string|null $genre = null;
    public string|null $description = null;
    public string|null $production_group = null;
    public string|null $actors = null;
    public string|null $age_limit = null; // TODO для поиска фильтровать только до INT
    public int|null $status = null;

    public function rules(): array
    {
        return [
            [['taxonomy_id', 'status','id'], 'integer'],
            [['title', 'author', 'genre', 'age_limit'], 'string', 'max' => 255],
            [['description', 'production_group', 'actors'], 'string'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Performance::find()->with('mainImage', 'taxonomy');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'taxonomy_id' => $this->taxonomy_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'author', $this->author]);
        $query->andFilterWhere(['like', 'genre', $this->genre]);
        $query->andFilterWhere(['like', 'description', $this->description]);
        $query->andFilterWhere(['like', 'production_group', $this->production_group]);
        $query->andFilterWhere(['like', 'actors', $this->actors]);
        $query->andFilterWhere(['like', 'age_limit', $this->age_limit]); // TODO для поиска фильтровать только до INT

        return $dataProvider;
    }

    public function taxonomiesList(): array
    {
        $scope = new TreeQueryScope(Taxonomy::class);
        return $scope->dropdownTree();
    }

    public function statusList(): array
    {
        return PerformanceHelper::statusList();
    }
}
