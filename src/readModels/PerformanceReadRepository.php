<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\readModels;

use Besnovatyj\Contracts\search\SearchDocument;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Tags\entities\Tag;
use Besnovatyj\Contracts\tags\TaggedItem;
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
        return Performance::find()->visible()->count();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->visible('p')->with('mainImage');
        return $this->getProvider($query);
    }

    public function getAllByTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->visible('p')->with('mainImage', 'taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->andWhere(['p.taxonomy_id' => $ids]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Performance::find()->alias('p')->visible('p')->with('mainImage');
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
        return Performance::find()->visible()->orderBy(new Expression('rand()'))->limit($limit)->all();
    }

    public function find($id): ?Performance
    {
        /** @var $performances Performance */
        $performances = Performance::find()->visible()->andWhere(['id' => $id])->one();
        return $performances;
    }

    /**
     * Спектакли для сквозного поиска — только публично доступные ({@see PerformanceQuery::visible()}).
     *
     * Генератор с чтением пачками: полная переиндексация не должна держать в памяти всю афишу.
     * Поля отдаются СЫРЫМИ — нормализация текста едина для всех модулей и выполняется модулем поиска.
     *
     * В ключевые слова уходят реквизиты постановки: автор, жанр, состав, коллектив, раздел и теги.
     * Их ищут наравне с названием («Чехов», «комедия», фамилия артиста), но в заголовке карточки
     * им не место.
     *
     * @return iterable<SearchDocument>
     */
    public function searchDocuments(): iterable
    {
        $query = Performance::find()->alias('p')->visible('p')
            ->with(['tags', 'taxonomy', 'mainImage'])
            ->orderBy(['p.id' => SORT_ASC]);

        /** @var Performance $performance */
        foreach ($query->each(100) as $performance) {
            $keywords = array_map(static fn (Tag $tag): string => (string)$tag->name, $performance->tags);

            $keywords[] = $performance->author;
            $keywords[] = $performance->genre;
            $keywords[] = $performance->production_group;
            $keywords[] = $performance->actors;

            if ($performance->taxonomy !== null) {
                $keywords[] = (string)$performance->taxonomy->name;
            }

            yield new SearchDocument(
                type: 'performance.performance',
                entityId: (int)$performance->id,
                route: '/Performance/performance/view',
                params: ['id' => (int)$performance->id],
                title: (string)$performance->title,
                text: (string)$performance->description,
                keywords: implode(' ', array_filter($keywords)),
                // Для спектакля осмысленна дата премьеры, а не дата записи в базе; обе — строковые
                // колонки DATE/DATETIME, поэтому только strtotime() (приведение (int) дало бы год).
                date: $this->performanceTimestamp($performance),
                image: $performance->mainImage?->getThumbUrl('file', 'frontend_list'),
            );
        }
    }

    /**
     * Из переданных id — спектакли, доступные анониму (для счётчиков страницы тега и облака).
     *
     * @param int[] $ids
     * @return int[]
     */
    public function visibleIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        return array_map('intval', Performance::find()->alias('p')->visible('p')->andWhere(['p.id' => $ids])->select('p.id')->column());
    }

    /**
     * Карточки спектаклей для страницы тега модуля Tags — только видимые, в порядке `$ids`.
     *
     * @param int[] $ids
     * @return iterable<TaggedItem>
     */
    public function taggedItems(array $ids): iterable
    {
        if ($ids === []) {
            return;
        }

        /** @var Performance[] $performances */
        $performances = Performance::find()->alias('p')->visible('p')->with('mainImage')->andWhere(['p.id' => $ids])->indexBy('id')->all();

        foreach ($ids as $id) {
            $performance = $performances[$id] ?? null;
            if ($performance === null) {
                continue;
            }
            yield new TaggedItem(
                type: Performance::tagType(),
                entityId: (int)$performance->id,
                route: '/Performance/performance/view',
                params: ['id' => (int)$performance->id],
                title: (string)$performance->title,
                excerpt: null,
                date: $this->performanceTimestamp($performance),
                image: $performance->mainImage?->getThumbUrl('file', 'frontend_list'),
            );
        }
    }

    /**
     * Дата спектакля для карточки выдачи и сортировки по свежести, в виде Unix-timestamp.
     */
    private function performanceTimestamp(Performance $performance): ?int
    {
        foreach ([$performance->premiere_date, $performance->created_at] as $value) {
            if ($value !== null && $value !== '' && ($timestamp = strtotime((string)$value)) !== false) {
                return $timestamp;
            }
        }

        return null;
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                // Спектакли показываем от самой свежей премьеры; записи без даты уходят в конец списка.
                'defaultOrder' => ['premiere_date' => SORT_DESC],
                'attributes' => [
                    'premiere_date' => [
                        'asc' => ['p.premiere_date' => SORT_ASC, 'p.id' => SORT_DESC],
                        'desc' => ['p.premiere_date' => SORT_DESC, 'p.id' => SORT_DESC],
                    ],
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


    /**
     * Спектакли для карты сайта.
     *
     * Тот же инвариант, что у поиска, — только публично доступное. Отличается набор полей: карте
     * нужны название и дата ИЗМЕНЕНИЯ записи, тогда как поиску осмысленно отдавать дату премьеры.
     * Для `lastmod` премьера не годится: краулера интересует, поменялась ли САМА СТРАНИЦА.
     *
     * Свежие постановки идут первыми: если раздел не поместится в один файл, в первой части
     * окажется самое новое.
     *
     * @return iterable<SitemapUrl>
     */
    public function sitemapUrls(): iterable
    {
        $query = Performance::find()->alias('p')->visible('p')->orderBy(['p.id' => SORT_DESC]);

        /** @var Performance $performance */
        foreach ($query->each(200) as $performance) {
            yield new SitemapUrl(
                route: '/Performance/performance/view',
                params: ['id' => (int)$performance->id],
                title: (string)$performance->title,
                // updated_at — колонка DATETIME, а контракт ждёт Unix-timestamp.
                lastModified: $performance->updated_at === null
                    ? null
                    : (strtotime((string)$performance->updated_at) ?: null),
            );
        }
    }

    /**
     * Отпечаток состояния спектаклей для карты сайта: сколько их и когда правили последний раз.
     *
     * Одного `MAX(updated_at)` мало — он не замечает удаления записи, а удалённая страница обязана
     * исчезнуть из карты. Пара «сколько + когда» это закрывает и стоит одного запроса.
     */
    public function sitemapRevision(): string
    {
        $row = Performance::find()->alias('p')->visible('p')
            ->select(['total' => 'COUNT(*)', 'latest' => 'MAX(p.updated_at)'])
            ->asArray()
            ->one();

        return ((string)($row['total'] ?? '0')) . ':' . ((string)($row['latest'] ?? ''));
    }
}
