<?php
/** @var yii\web\View $this */
/** @var app\models\Bugs $model */

?>
<h3>
    <?= $model->id ?> :
    <?= $model->title ?>
</h3>

<div class="bug-view">
    <hr style="margin-top: 20px" />
    <div class="d-flex row flex-nowrap" style="margin-top: 60px">
        <div class="mr-5">
            <h4 class="mr-2"> Status: </h4>
            <?= $model->status->name ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Platform: </h4>
            <?= $model->platform->name ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Severity:</h4>
            <?= $model->severity->name ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Priority:</h4>
            <?= $model->priority->name ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Functionality: </h4>
            <?= $model->functionality->name ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Sprint: </h4>
            <?= $model->sprint ?>
        </div>
    </div>

    <div class="d-flex row flex-nowrap" style="margin-top: 30px">
        <div class="mr-5">
            <h4 class="mr-2"> Created date:</h4>
            <?= $model->createdDate ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Updated date:</h4>
            <?= $model->changedDate ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Resolve time (days):</h4>
            <?= $model->timeForSolve ?>
        </div>
        <div class="mr-5">
            <h4 class="mr-2"> Created By:</h4>
            <?= $model->userType->name ?>
        </div>
    </div>
    <hr style="margin-top: 20px" />

    <div class="d-flex row flex-nowrap" style="margin-top: 60px">
        <h4 class="mr-2" style="margin-top: -5px"> Cause:</h4>
        <?= $model->rootCause ?>
    </div>
</div>
