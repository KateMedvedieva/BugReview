<?php

namespace app\controllers;

use app\models\Bugs;
use app\models\UploadForm;
use Yii;
use yii\db\StaleObjectException;
use yii\web\UploadedFile;

class BugController extends \yii\web\Controller
{
    public function actionIndex($id)
    {
        $model = Bugs::findOne($id);

        return $this->render('index', [
            'model' => $model
        ]);
    }

    public function actionImport($model)
    {
    }

    public function actionList()
    {

        $model = new UploadForm();


        if (Yii::$app->request->isPost) {
            $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
            if ($model->upload()) {
                return $this->refresh();
            }
        }

        return $this->render('list', ['model' => $model]);
    }

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionDelete($id = null)
    {
        if ($id) {
            $bug = Bugs::findOne(['id' => $id]);

            if ($bug) {
                $bug->delete();
            }
        } else {
            Bugs::deleteAll();
        }

        return $this->actionList();
    }

    /**
     * Displays bugs page.
     *
     * @return string
     */
    public function actionNew()
    {

        $model = new Bugs();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {

                Yii::$app->session->setFlash('success', 'Data saved successfully.');
            } else {

                Yii::$app->session->setFlash('error', 'Error saving data.');
            }
        }

        return $this->render('new', [
            'model' => $model,
        ]);
    }


    /**
     * Displays bugs page.
     *
     * @return string
     */
    public function actionEdit($id)
    {

        $model = Bugs::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                // data is saved successfully
                Yii::$app->session->setFlash('success', 'Data updated successfully.');
            } else {
                // error in saving data
                Yii::$app->session->setFlash('error', 'Error saving data.');
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

}
