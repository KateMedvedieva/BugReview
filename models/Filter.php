<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Filter is the model behind the Filter form.
 */
class Filter extends Model
{
    public $startDate;
    public $endDate;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['startDate', 'endDate'], 'safe'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'startDate' => 'From',
            'endDate' => 'To',
        ];
    }
}
