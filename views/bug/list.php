<?php

use app\models\Bugs;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */

/* @var $model app\models\UploadForm */
/* @var $form ActiveForm */

$this->title = 'All bugs';
?>
<div class="site-index">

    <div class="body-content">
        <div class="row justify-content-start align-items-center">
            <div class="col-8">
                <?= Html::beginForm(Url::to(['bug/new']), 'get') ?>
                <?= Html::submitButton('Add new bug', ['class' => 'btn btn-primary']) ?>
                <?= Html::endForm() ?>
            </div>
            <div class="col-4 row justify-content-center align-items-center">
                <div class="csv-upload-form col-md-auto">

                    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
                    <?= $form->field($model, 'csvFile')->fileInput(['class' => 'hidden-file-input', 'id' => 'real-file-input'])->label(false) ?>
                    <label for="real-file-input" class="btn btn-primary">Select .csv file</label>

                </div>
                <div class="form-group col-md-auto mt-4">
                    <?= Html::submitButton('Import Data', ['class' => 'btn btn-primary']) ?>
                </div>
                <?= Html::a('<i class="fa fa-trash"></i>', Url::to(['bug/delete']), ['class' => 'btn btn-danger col-md-auto mt-4', 'style' => 'margin-bottom: 1rem']) ?>

                <?php ActiveForm::end(); ?>
            </div>

        </div>


        <div class="row">

        <?php $records = Bugs::find()->all(); ?>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th> ID </th>
                    <th> Title </th>
                    <th> Created by </th>
                    <th> Platform </th>
                    <th> Create date </th>
                    <th> Severity </th>
                    <th> Priority </th>
                    <th> Actions </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($records as $record) {
                        echo "<tr>";
                        echo "<td>" . $record->id . "</td>";
                        echo "<td>" . $record->title . "</td>";
                        echo "<td>" . $record->userType->name. "</td>";
                        echo "<td>" . $record->platform->name . "</td>";
                        echo "<td>" . $record->createdDate . "</td>";
                        echo "<td>" . $record->severity->name . "</td>";
                        echo "<td>" . $record->priority->name . "</td>";
                        echo "<td class=\"d-flex flex-row flex-nowrap\">" .
                            Html::a('<i class="fa fa-eye"></i>', Url::to(['bug/index', 'id' => $record->id]), ['class' => 'btn btn-primary mr-1']) .
                            Html::a('<i class="fa fa-edit"></i>', Url::to(['bug/edit', 'id' => $record->id]), ['class' => 'btn btn-success mr-1']) .
                            Html::a('<i class="fa fa-trash"></i>', Url::to(['bug/delete', 'id' => $record->id]), ['class' => 'btn btn-danger    ']) .
                            "</td></tr>";
                    }
                ?>
            </tbody>
        </div>

    </div>
</div>
