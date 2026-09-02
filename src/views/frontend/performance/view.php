<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Module;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $performance Performance */

$this->title = $performance->title;
$this->params['layoutTitle'] = $this->title;

$this->params['og:title'] = $this->title;

$this->params['breadcrumbs'][] = ['label' => 'Спектакли', 'url' => ['index']];

$treeScope = new TreeQueryScope(Taxonomy::class);
foreach ($treeScope->parentsQuery($performance->taxonomy)->all() as $parent) {
    if ((int)$parent->depth > 0) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['taxonomy', 'slug' => $parent->slug]];
    }
}

$this->params['breadcrumbs'][] = ['label' => $performance->taxonomy->name, 'url' => ['taxonomy', 'slug' => $performance->taxonomy->slug]];
$this->params['breadcrumbs'][] = $performance->title;

$this->registerMetaTag(['name' => 'title', 'content' => $performance->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $performance->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $performance->meta->description]);

if (Yii::$app->getModule('Config') instanceof Module) {
    $this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
}

?>

<section class="container mt-3 mb-5">
    <header class="pb-3 mb-4 border-bottom">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            <h1 class="mb-0"><?= Html::encode($performance->title) ?></h1>
            <?php if (!empty($performance->age_limit)): ?>
                <span class="badge text-bg-secondary fs-6"><?= Html::encode($performance->age_limit) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($performance->author)): ?>
            <div class="text-secondary mt-2"><?= Html::encode($performance->author) ?></div>
        <?php endif; ?>
        <?php if (!empty($performance->genre)): ?>
            <div class="text-secondary"><?= Html::encode($performance->genre) ?></div>
        <?php endif; ?>
        <?php if (!empty($performance->premiere_date)): ?>
            <div class="text-secondary">Премьера: <?= Yii::$app->formatter->asDate($performance->premiere_date, 'long') ?></div>
        <?php endif; ?>
    </header>

    <div class="accordion" id="performance-accordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-performance-annotation" aria-expanded="true"
                        aria-controls="collapse-performance-annotation">
                    Аннотация
                </button>
            </h2>
            <div id="collapse-performance-annotation" class="accordion-collapse collapse show"
                 data-bs-parent="#performance-accordion">
                <div class="accordion-body">
                    <?= Yii::$app->formatter->asHtml($performance->description, [
                        'Attr.AllowedRel' => ['nofollow'],
                        'HTML.SafeObject' => true,
                        'HTML.SafeIframe' => true,
                        'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                    ]) ?>
                </div>
            </div>
        </div>

        <?php if (strlen($performance->actors) > 0): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-performance-actors" aria-expanded="false"
                            aria-controls="collapse-performance-actors">
                        В ролях
                    </button>
                </h2>
                <div id="collapse-performance-actors" class="accordion-collapse collapse"
                     data-bs-parent="#performance-accordion">
                    <div class="accordion-body">
                        <?= $performance->actors ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (strlen($performance->production_group) > 0): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-performance-production-group" aria-expanded="false"
                            aria-controls="collapse-performance-production-group">
                        Постановочная группа
                    </button>
                </h2>
                <div id="collapse-performance-production-group" class="accordion-collapse collapse"
                     data-bs-parent="#performance-accordion">
                    <div class="accordion-body">
                        <?= $performance->production_group ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (is_array($performance->images) && sizeof($performance->images) > 0): ?>
    <section class="container mb-5">
        <h2 class="h4 text-center mb-4">Фотографии</h2>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php foreach ($performance->images as $image): ?>
                <div class="col">
                    <a href="<?= $image->getUploadUrl('file') ?>"
                       class="d-block ratio ratio-1x1 rounded overflow-hidden shadow-sm"
                       target="_blank" rel="noopener">
                        <img src="<?= $image->getThumbUrl('file', 'thumb') ?>" class="object-fit-cover"
                             alt="Фотография спектакля «<?= Html::encode($performance->title) ?>»"/>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>












