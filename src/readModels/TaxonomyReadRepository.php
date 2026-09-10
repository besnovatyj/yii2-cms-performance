<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\readModels;

use Besnovatyj\Contracts\search\SearchDocument;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

class TaxonomyReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    /**
     * Корневой раздел дерева — только видимый: корень такой же полноценный раздел, как
     * остальные, и снятый с публикации показываться не должен.
     */
    public function getRoot(): ?Taxonomy
    {
        return Taxonomy::find()->visible()->andWhere(['depth' => 0])->one();
    }

    /**
     * @return Taxonomy[]
     */
    public function getAll(): array
    {
        return Taxonomy::find()->visible()->orderBy('lft')->all();
    }

    public function find(int $id): ?Taxonomy
    {
        return Taxonomy::find()->visible()->andWhere(['id' => $id])->one();
    }

    /**
     * Раздел по slug для фронтенда — только доступный анонимному посетителю: снятый с
     * публикации (или лежащий в скрытой ветке) не должен открываться по прямой ссылке.
     */
    public function findBySlug($slug): ?Taxonomy
    {
        return Taxonomy::find()->visible()->andWhere(['slug' => $slug])->one();
    }

    /**
     * Разделы афиши для сквозного поиска — только видимые целиком, вместе с предками
     * ({@see \Besnovatyj\Performance\entities\queries\TaxonomyQuery::visible()}).
     *
     * @return iterable<SearchDocument>
     */
    public function searchDocuments(): iterable
    {
        $query = Taxonomy::find()->visible()->orderBy(['id' => SORT_ASC]);

        /** @var Taxonomy $taxonomy */
        foreach ($query->each(100) as $taxonomy) {
            yield new SearchDocument(
                type: 'performance.taxonomy',
                entityId: (int)$taxonomy->id,
                route: '/Performance/performance/taxonomy',
                params: ['slug' => $taxonomy->slug],
                title: (string)$taxonomy->name,
                text: (string)$taxonomy->description,
            );
        }
    }

    public function getTreeWithSubsOf(?Taxonomy $taxonomy = null): array
    {
        $query = Taxonomy::find()->visible()->orderBy(['lft' => SORT_ASC]);
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
