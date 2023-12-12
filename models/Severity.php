<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "severity".
 *
 * @property int $id
 * @property string $name
 *
 * @property Bugs[] $bugs
 */
class Severity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'severity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['id'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[Bugs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBugs()
    {
        return $this->hasMany(Bugs::class, ['severityID' => 'id']);
    }
}