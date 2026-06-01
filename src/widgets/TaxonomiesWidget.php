<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\widgets;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\readModels\TaxonomyReadRepository;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Виджет со списком категорий для сайдбара фронтэнда
 */
class TaxonomiesWidget extends Widget
{
    /** @var Taxonomy|null */
    public ?Taxonomy $active;
    private TaxonomyReadRepository $categories;

    public function __construct(TaxonomyReadRepository $categories, $config = [])
    {
        parent::__construct($config);
        $this->categories = $categories;
    }

    public function run(): string
    {
        return Html::tag('ul', implode(PHP_EOL, array_map(function (Taxonomy $taxonomy) {
            $active = $this->active && ($this->active->id == $taxonomy->id);
            $class = $active ? 'list-group-item active' : 'list-group-item';
            return '<li class="' . $class . '">' . Html::a(
                Html::encode($taxonomy->name),
                ['/Performance/performance/taxonomy', 'slug' => $taxonomy->slug]
            ) . '<small>' . $taxonomy->countPerformancesByTaxonomy() . '</small></li>';
        }, $this->categories->getAll())), [
            'class' => 'list-group list-group-flush',
        ]);
    }
}
