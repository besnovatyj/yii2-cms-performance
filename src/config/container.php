<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Meta\Meta;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\repositories\PerformanceRepository;
use Besnovatyj\Performance\repositories\ShowcaseRepository;
use Besnovatyj\TreeManager\Manager\entities\Node;
use Besnovatyj\TreeManager\Manager\forms\TreeNodeFormInterface;
use Besnovatyj\TreeManager\Manager\TreeManager;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

/**
 * Конфигурация DI контейнера для модуля Performance
 */
return function (\yii\di\Container $container): void {

    $container->setSingleton('performance.tree.manager', function () use ($container) {
        $performances = new PerformanceRepository();
        return new TreeManager(
            modelClass: Taxonomy::class,
            entityFactory: function (TreeNodeFormInterface $form): Taxonomy {
                return Taxonomy::create(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
            },
            entityUpdater: function (Node $node, TreeNodeFormInterface $form): Node {
                /** @var Taxonomy $node */
                $node->edit(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
                return $node;
            },
            deleteGuard: function (Node $node) use ($performances, $container): void {
                /** @var Taxonomy $node */
                // TODO нет проверки на запрет удаления родительской, если к дочерней привязаны элементы
                if ($performances->existsByMainTaxonomy($node->id)) {
                    throw new DomainException('Unable to remove taxonomy with performances.');
                }
                if ($container->get(ShowcaseRepository::class)->existsByTaxonomy($node->id)) {
                    throw new DomainException('Раздел используется витриной спектаклей: смените раздел витрины.');
                }
            },
        );
    });

    $container->setSingleton('performance.tree.scope', function () use ($container) {
        return new TreeQueryScope(Taxonomy::class);
    });
};
