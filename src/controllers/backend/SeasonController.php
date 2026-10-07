<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\controllers\backend;

use Besnovatyj\Kernel\controller\ControllerTrait;
use Besnovatyj\Performance\entities\season\Season;
use Besnovatyj\Performance\forms\backend\season\SeasonForm;
use Besnovatyj\Performance\repositories\SeasonRepository;
use Besnovatyj\Performance\services\manage\SeasonManageService;
use DomainException;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

/**
 * Театральные сезоны: даты открытия и закрытия, от которых витрины считают периоды.
 */
class SeasonController extends Controller
{
    use ControllerTrait;

    public function __construct(
        $id,
        $module,
        private readonly SeasonManageService $service,
        private readonly SeasonRepository    $repository,
        $config = [],
    )
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Season::find()->orderBy(['start_date' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return Response|string
     */
    public function actionCreate(): Response|string
    {
        $form = new SeasonForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->create($form);
                return $this->redirect(['index']);
            } catch (DomainException $e) {
                $form->addError('start_date', $e->getMessage());
            } catch (Exception $e) {
                $this->handleDomainException($e);
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id): Response|string
    {
        $season = $this->repository->get($id);
        $form = new SeasonForm($season);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($season->id, $form);
                return $this->redirect(['index']);
            } catch (DomainException $e) {
                $form->addError('start_date', $e->getMessage());
            } catch (Exception $e) {
                $this->handleDomainException($e);
            }
        }

        return $this->render('update', [
            'model' => $form,
            'season' => $season,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (Throwable $e) {
            Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
        }
        return $this->redirect(['index']);
    }
}
