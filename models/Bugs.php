<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bugs".
 *
 * @property int $id
 * @property int|null $sprint
 * @property string $title
 * @property string $createdDate
 * @property string $changedDate
 * @property int $statusID
 * @property int $priorityID
 * @property int $severityID
 * @property int $timeForSolve
 * @property int $createdBy
 * @property int $functionalityID
 * @property string|null $rootCause
 * @property int $platformID
 *
 * @property Functionality $functionality
 * @property Platform $platform
 * @property Priority $priority
 * @property Severity $severity
 * @property Status $status
 */
class Bugs extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'bugs';
    }
    public function rules()
    {
        return [
            [['id', 'title', 'createdDate', 'changedDate', 'statusID', 'priorityID', 'severityID', 'timeForSolve', 'createdBy', 'functionalityID', 'platformID'], 'required'],
            [['id', 'sprint', 'statusID', 'priorityID', 'severityID', 'timeForSolve', 'createdBy', 'functionalityID', 'platformID'], 'integer'],
            [['title', 'rootCause'], 'string'],
            [['createdDate', 'changedDate'], 'safe'],
            [['id'], 'unique'],
            [['functionalityID'], 'exist', 'skipOnError' => true, 'targetClass' => Functionality::class, 'targetAttribute' => ['functionalityID' => 'id']],
            [['platformID'], 'exist', 'skipOnError' => true, 'targetClass' => Platform::class, 'targetAttribute' => ['platformID' => 'id']],
            [['priorityID'], 'exist', 'skipOnError' => true, 'targetClass' => Priority::class, 'targetAttribute' => ['priorityID' => 'id']],
            [['severityID'], 'exist', 'skipOnError' => true, 'targetClass' => Severity::class, 'targetAttribute' => ['severityID' => 'id']],
            [['statusID'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['statusID' => 'id']],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sprint' => 'Sprint',
            'title' => 'Title',
            'createdDate' => 'Created Date',
            'changedDate' => 'Changed Date',
            'statusID' => 'Status',
            'priorityID' => 'Priority',
            'severityID' => 'Severity',
            'timeForSolve' => 'Time For Solve',
            'createdBy' => 'Created By',
            'functionalityID' => 'Functionality',
            'rootCause' => 'Root Cause',
            'platformID' => 'Platform',
        ];
    }

    /**
     * Gets query for [[Functionality]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFunctionality()
    {
        return $this->hasOne(Functionality::class, ['id' => 'functionalityID']);
    }
    /**
     * Gets query for [[Platform]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platform::class, ['id' => 'platformID']);
    }
    /**
     * Gets query for [[Priority]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPriority()
    {
        return $this->hasOne(Priority::class, ['id' => 'priorityID']);
    }
    /**
     * Gets query for [[Severity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeverity()
    {
        return $this->hasOne(Severity::class, ['id' => 'severityID']);
    }
    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'statusID']);
    }
    /**
     * Gets query for [[UserType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserType()
    {
        return $this->hasOne(UserType::class, ['id' => 'createdBy']);
    }
}
