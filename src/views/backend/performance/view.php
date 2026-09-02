<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\helpers\PerformanceHelper;
use Besnovatyj\Images\widgets\upload\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $performance Performance */
/* @var $absoluteFrontendUrl string */

$this->title = $performance->title;
$this->params['breadcrumbs'][] = ['label' => 'Performances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a('Create', ['create'], ['class' => 'btn  btn-success']) ?>
    <?php if ($performance->isActive()): ?>
        <?= Html::a('To draft', ['draft', 'id' => $performance->id], ['class' => 'btn  btn-warning', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('To active', ['activate', 'id' => $performance->id], ['class' => 'btn  btn-success', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?= Html::a('Update', ['update', 'id' => $performance->id], ['class' => 'btn  btn-primary']) ?>
    <?= Html::a('Delete', ['delete', 'id' => $performance->id], [
        'class' => 'btn  btn-danger',
        'data' => [
            'confirm' => 'Are you sure?',
            'method' => 'post',
        ],
    ]) ?>

    <a class="btn  btn-secondary" target="_blank"
       href="<?= $absoluteFrontendUrl; ?>">
        <i class="bi bi-eye"></i>
    </a>
</p>

<div class="container">
    <div class="row">
        <div class="col-sm">
            <!--COMMON-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Common</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-common" role="button"
                       aria-expanded="true" aria-controls="collapseCommon"></a>
                </div>
                <div class="collapse show" id="collapse-common">
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $performance,
                            'attributes' => [
                                'id',
                                'title',
                                'author',
                                'genre',
                                'age_limit',
                                'premiere_date:date',
                                'created_at:datetime',
                                'updated_at:datetime',
                                [
                                    'attribute' => 'status',
                                    'value' => PerformanceHelper::statusLabel($performance),
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'taxonomy_id',
                                    'value' => ArrayHelper::getValue($performance, 'taxonomy.name'),
                                ],
                                [
                                    'label' => 'Теги',
                                    'value' => implode(', ', ArrayHelper::getColumn($performance->tags, 'name')),
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--ACTORS-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Actors</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-actors" role="button"
                       aria-expanded="true" aria-controls="collapseActors"></a>
                </div>
                <div class="collapse show" id="collapse-actors">
                    <div class="card-body">
                        <?= Yii::$app->formatter->asHtml($performance->actors, [
                            'Attr.AllowedRel' => array('nofollow'),
                            'HTML.SafeObject' => true,
                            'Output.FlashCompat' => true,
                            'HTML.SafeIframe' => true,
                            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--SEO-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">SEO</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-SEO" role="button"
                       aria-expanded="true" aria-controls="collapseSEO"></a>
                </div>
                <div class="collapse show" id="collapse-SEO">
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $performance,
                            'attributes' => [
                                [
                                    'attribute' => 'meta.title',
                                    'value' => $performance->meta->title,
                                ],
                                [
                                    'attribute' => 'meta.description',
                                    'value' => $performance->meta->description,
                                ],
                                [
                                    'attribute' => 'meta.keywords',
                                    'value' => $performance->meta->keywords,
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <!--DESCRIPTION-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Description</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-description" role="button"
                       aria-expanded="true" aria-controls="collapseDescription"></a>
                </div>
                <div class="collapse show" id="collapse-description">
                    <div class="card-body">
                        <?= Yii::$app->formatter->asHtml($performance->description, [
                            'Attr.AllowedRel' => array('nofollow'),
                            'HTML.SafeObject' => true,
                            'Output.FlashCompat' => true,
                            'HTML.SafeIframe' => true,
                            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--PRODUCTION GROUP-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Production group</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-production_group" role="button"
                       aria-expanded="true" aria-controls="collapseProductionGroup"></a>
                </div>
                <div class="collapse show" id="collapse-production_group">
                    <div class="card-body">
                        <?= Yii::$app->formatter->asHtml($performance->production_group, [
                            'Attr.AllowedRel' => array('nofollow'),
                            'HTML.SafeObject' => true,
                            'Output.FlashCompat' => true,
                            'HTML.SafeIframe' => true,
                            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <!--IMAGES-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Images</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-images" role="button"
                       aria-expanded="true" aria-controls="collapseImages"></a>
                </div>
                <div class="collapse show" id="collapse-images">
                    <div class="card-body">
                        <?= Widget::widget([
                            'ownerId'   => $performance->id,
                            'endpoints' => [
                                'getImages'    => Url::to(['/Performance/backend/performance/get-images'], true),
                                'setNewSort'   => Url::to(['/Performance/backend/performance/set-new-sort'], true),
                                'upload'       => Url::to(['/Performance/backend/performance/add-image'], true),
                                'deleteImage'  => Url::to(['/Performance/backend/performance/delete-image'], true),
                                'setMainImage' => '/Performance/backend/performance/set-main-image',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
