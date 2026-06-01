<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\readModels;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

class TaxonomyReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    public function getRoot(): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['depth' => 0])->one();
    }

    /**
     * @return Taxonomy[]
     */
    public function getAll(): array
    {
        return Taxonomy::find()->orderBy('lft')->all();
    }

    public function find(int $id): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['id' => $id])->one();
    }

    public function findBySlug($slug): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['slug' => $slug])->one();
    }

    public function getTreeWithSubsOf(?Taxonomy $taxonomy = null): array
    {
        $query = Taxonomy::find()->andWhere(['status' => 1])->orderBy(['lft' => SORT_ASC]);
        if ($taxonomy) {
            $parents = $this->treeScope->parentsQuery($taxonomy)->all();

            if (!empty($parents)) {
                $parent = $parents[count($parents) - 1];
                $query->andWhere(['>=', 'lft', $parent->lft])->andWhere(['<=', 'rgt', $parent->rgt]);
            } else {
                $query->andWhere(['>=', 'lft', $taxonomy->lft])->andWhere(['<=', 'rgt', $taxonomy->rgt]);
            }
        } else {
            $query->andWhere(['depth' => [0, 1]]);
        }

        return $query->all();
    }
}
