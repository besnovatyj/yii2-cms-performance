<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesDirectories;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;
use Besnovatyj\Contracts\menu\MenuTarget;
use Besnovatyj\Contracts\menu\MenuTargetProvider;
use Besnovatyj\Contracts\search\SearchSource;
use Besnovatyj\Contracts\search\SearchableProvider;
use Besnovatyj\Contracts\sitemap\ChangeFrequency;
use Besnovatyj\Contracts\sitemap\SitemapFreshness;
use Besnovatyj\Contracts\sitemap\SitemapProvider;
use Besnovatyj\Contracts\sitemap\SitemapSection;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use Besnovatyj\Contracts\tags\TaggableProvider;
use Besnovatyj\Contracts\tags\TagSource;
use Besnovatyj\Contracts\upload\ThumbnailSource;
use Besnovatyj\Contracts\upload\ThumbnailSourceProvider;
use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\readModels\PerformanceReadRepository;
use Besnovatyj\Performance\readModels\TaxonomyReadRepository;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

class Module extends CmsModule implements
    DeclaresModule, 
    ProvidesDirectories,
    ProvidesMigrations, ProvidesOptions, MenuTargetProvider, SearchableProvider,
    SitemapProvider, SitemapFreshness, TaggableProvider, ThumbnailSourceProvider
{
    public const bool EDITABLE = true;
    public const string MODULE_ID = 'Performance';
    public static function moduleId(): string { return self::MODULE_ID; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }
    public static function options(): array { return require __DIR__.'/config/options.php'; }
    public static function migrationPath(): string { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }
    public static function directories(): array { return ['@static/origin/Performance','@static/cache/Performance'];}

    /**
     * Цели для построения пунктов меню. Реализация {@see MenuTargetProvider};
     * вызывается только модулем меню, если он установлен.
     *
     * @return MenuTarget[]
     */
    public function menuTargets(): array
    {
        return [
            new MenuTarget('/Performance/performance/taxonomy', 'Таксономия постановок', 'slug'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string,string>
     */
    public function menuCandidates(string $route): array
    {
        return match (ltrim($route, '/')) {
            'Performance/performance/taxonomy' => $this->taxonomySlugMap(),
            default => [],
        };
    }

    /**
     * Карта `slug => подпись` (с отступом по глубине дерева) для таксономий постановок.
     *
     * @return array<string,string>
     */
    private function taxonomySlugMap(): array
    {
        return (new TreeQueryScope(Taxonomy::class))->dropdownTree(keyAttribute: 'slug', indent: '— ');
    }

    /**
     * Модели модуля с превью. Реализация {@see ThumbnailSourceProvider}; вызывается только модулем
     * загрузок (сквозной прогрев превью), если он установлен.
     *
     * @return ThumbnailSource[]
     */
    public function thumbnailSources(): array
    {
        return [
            new ThumbnailSource(Image::class, 'file', 'Изображения спектаклей'),
        ];
    }

    /**
     * Контент модуля для сквозного поиска. Реализация {@see SearchableProvider}; вызывается
     * только модулем поиска, если он установлен.
     *
     * @return SearchSource[]
     */
    public function searchSources(): array
    {
        return [
            new SearchSource('performance.performance', 'Спектакли', 1.0, 'bi bi-mask'),
            new SearchSource('performance.taxonomy', 'Разделы афиши', 0.7, 'bi bi-diagram-3'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchDocuments(string $type): iterable
    {
        return match ($type) {
            'performance.performance' => (new PerformanceReadRepository())->searchDocuments(),
            'performance.taxonomy' => (new TaxonomyReadRepository())->searchDocuments(),
            default => [],
        };
    }


    /**
     * Спектакли — участники общего словаря тегов. Реализация {@see TaggableProvider}; вызывается модулем
     * тегов для страницы `/tag/<slug>` и облака. Ключ — тот же `performance.performance`, что у поиска и карты.
     *
     * @return TagSource[]
     */
    public function tagSources(): array
    {
        return [
            new TagSource(Performance::tagType(), 'Спектакли', 'bi bi-mask'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function visibleTaggedIds(string $type, array $ids): array
    {
        return match ($type) {
            Performance::tagType() => new PerformanceReadRepository()->visibleIds($ids),
            default => [],
        };
    }

    /**
     * {@inheritdoc}
     */
    public function taggedItems(string $type, array $ids): iterable
    {
        return match ($type) {
            Performance::tagType() => new PerformanceReadRepository()->taggedItems($ids),
            default => [],
        };
    }

    /**
     * Разделы карты сайта. Реализация {@see SitemapProvider}; вызывается только модулем карты,
     * если он установлен.
     *
     * Разделов два, и это не дублирование: «Афиша» — навигационная ветка (список и его разделы),
     * «Спектакли» — сами постановки. Каждый режется в свой файл, включается и взвешивается
     * отдельно, а на человеческой карте даёт свой блок.
     *
     * @return SitemapSection[]
     */
    public function sitemapSections(): array
    {
        return [
            new SitemapSection(
                key: 'performance.taxonomy',
                label: 'Афиша',
                changeFrequency: ChangeFrequency::Weekly,
                priority: 0.7,
                order: 50,
                icon: 'bi bi-diagram-3',
            ),
            new SitemapSection(
                key: 'performance.performance',
                label: 'Спектакли',
                changeFrequency: ChangeFrequency::Monthly,
                priority: 0.7,
                order: 55,
                icon: 'bi bi-mask',
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sitemapUrls(string $section): iterable
    {
        return match ($section) {
            'performance.performance' => (new PerformanceReadRepository())->sitemapUrls(),
            'performance.taxonomy' => $this->taxonomySitemapUrls(),
            default => [],
        };
    }

    /**
     * {@inheritdoc}
     *
     * Отпечаток есть только у постановок: в дереве разделов колонок времени нет
     * (см. {@see TaxonomyReadRepository::sitemapUrls()}).
     */
    public function sitemapRevision(string $section): ?string
    {
        return match ($section) {
            'performance.performance' => (new PerformanceReadRepository())->sitemapRevision(),
            default => null,
        };
    }

    /**
     * Разделы афиши, а перед ними — сам список.
     *
     * Список — корень ветки и для робота, и для читателя: на человеческой карте он открывает блок,
     * в XML это обычный адрес с высоким приоритетом. Отдельным разделом карты его заводить незачем —
     * раздел из одного адреса только засоряет и настройки, и индекс файлов.
     *
     * @return iterable<SitemapUrl>
     */
    private function taxonomySitemapUrls(): iterable
    {
        yield new SitemapUrl(
            route: '/Performance/performance/index',
            title: 'Афиша',
            changeFrequency: ChangeFrequency::Weekly,
            priority: 0.9,
        );

        yield from (new TaxonomyReadRepository())->sitemapUrls();
    }
}
