<?php


use app\models\Functionality;
use app\models\Platform;
use app\models\Priority;
use app\models\Severity;
use app\models\Status;
use app\models\UserType;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use yii\web\View;
use yii\widgets\ActiveForm;

use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\Bugs $model */
/** @var ActiveForm $form */

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="body-content">

        <div class="column">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'title', ['options' => ['class' => 'w-100']])->textarea() ?>
            <?= $form->field($model, 'rootCause', ['options' => ['class' => 'w-100']])->textarea() ?>
            <div class="d-flex justify-content-between ">
                <?= $form->field($model, 'createdDate', ['options' => ['class' => 'w-100 mr-3 mt-3 mb-3']])->widget(DatePicker::classname(), [
                    'language' => 'en',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control'],
                ]) ?>
                <?= $form->field($model, 'changedDate', ['options' => ['class' => 'w-100 m-3']])->widget(DatePicker::classname(), [
                    'language' => 'en',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control'],
                ]) ?>
                <?= $form->field($model, 'timeForSolve', ['options' => ['class' => 'w-100 ml-3 mt-3 mb-3']]) ?>
            </div>
            <div class="d-flex justify-content-between ">
                <?php $data = ArrayHelper::map(Status::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'statusID', ['options' => ['class' => 'w-100 mr-3 mt-3 mb-3']])->
                    dropDownList($data, ['prompt' => 'Select a Status'])?>

                <?php $data = ArrayHelper::map(Priority::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'priorityID', ['options' => ['class' => 'w-100 m-3']])->
                    dropDownList($data, ['prompt' => 'Select a Priority'])?>

                <?php $data = ArrayHelper::map(Severity::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'severityID', ['options' => ['class' => 'w-100 ml-3 mt-3 mb-3']])->
                    dropDownList($data, ['prompt' => 'Select a Severity'])?>
            </div>

            <div class="d-flex justify-content-between gap-10">
                <?php $data = ArrayHelper::map(UserType::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'createdBy', ['options' => ['class' => 'w-100']])->
                dropDownList($data, ['prompt' => 'Select a user type has found a bug'])?>

                <?= $form->field($model, 'sprint') ?>
            </div>

            <div class="d-flex justify-content-between ">
                <?php $data = ArrayHelper::map(Functionality::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'functionalityID', ['options' => ['class' => 'w-100']])->
                    dropDownList($data, ['prompt' => 'Select a Functionality area'])?>

                <?php $data = ArrayHelper::map(Platform::find()->all(), 'id', 'name') ?>
                <?= $form->field($model, 'platformID', ['options' => ['class' => 'w-100']])->
                    dropDownList($data, ['prompt' => 'Select a Platform'])?>

            </div>

            <div class="form-group mt-5">
                <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>


<?php

$script = <<< JS
$(document).ready(function() {
    $('#bugs-createddate, #bugs-changeddate').change(function() {
        var createdVal = $('#bugs-createddate').val();
        var changedVal = $('#bugs-changeddate').val();

        if (!createdVal || !changedVal) {
            return
        }
        
        const createdDate = new Date(createdVal)
        const changedDate = new Date(changedVal)
        var amountOfDays = calculateDays(createdDate, changedDate);

        $('#bugs-timeforsolve').val(amountOfDays);
    });
});

function calculateDays(d1, d2) {
    const oneDay = 24 * 60 * 60 * 1000;

    const differenceInTime = Math.abs(d1 - d2);
    return Math.ceil(differenceInTime / oneDay);
} 

JS;
$this->registerJs($script, View::POS_END);
?>
