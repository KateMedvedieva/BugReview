<?php
namespace app\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\debug\models\search\Debug;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    public $csvFile;
    public function rules()
    {
        return [
            [['csvFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv', 'mimeTypes' => 'text/csv'],
        ];
    }
    public function upload()
    {
        $filePath = 'uploads/' . $this->csvFile->baseName . '.' . $this->csvFile->extension;
        $this->csvFile->saveAs($filePath);
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 100000, ",")) !== false) {
                if (is_numeric($data[1])) {
                    $id = (int)$data[1];
                    $entity = Bugs::findOne(['id' => $id]);
                    if ($entity || $entity['id']) {
                        continue;
                    }
                    $sprint = $data[0] === '-' || $data[0] === '' ? NULL : (int)$data[0];
                    $title = $data[2];
                    $entity = UserType::findOne(['name' => $data[3]]);
                    $createdBy = $entity['id'];
                    $entity = Platform::findOne(['name' => $data[4]]);
                    $platform = $entity['id'];
                    $entity = Functionality::findOne(['name' => $data[5]]);
                    $functionality = $entity['id'];
                    $rootCause = $data[7] === '' ? NULL : $data [7];
                    $entity = Status::findOne(['name' => $data[8]]);
                    $status = $entity['id'];
                    $entity = Priority::findOne(['name' => $data[9]]);
                    $priority = $entity['id'];
                    $entity = Severity::findOne(['name' => $data[10]]);
                    $severity = $entity['id'];
                    $crDate = DateTime::createFromFormat('d-m-Y', implode('-', explode(".", $data[11])));
                    $createDate = $crDate->format('Y-m-d');
                    echo($createDate);
                    $upDate = DateTime::createFromFormat('d-m-Y', implode('-', explode(".", $data[12])));
                    $changeDate = $upDate->format('Y-m-d');
                    $resolveTime = (int)$data[13];
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $entity = new Bugs();
                        $entity->sprint = $sprint;
                        $sql = "INSERT INTO  bugs (id, sprint, title, createdDate, changedDate, statusID, priorityID, severityID, timeForSolve, createdBy, platformID, functionalityID, rootCause) VALUES (:id, :sprint, :title, :createdDate, :changedDate, :statusID, :priorityID, :severityID, :timeForSolve, :createdBy, :platformID, :functionalityID, :rootCause)";
                        $command = Yii::$app->db->createCommand($sql);
                        $command->bindValue(':id', $id);
                        $command->bindValue(':sprint', $sprint);
                        $command->bindValue(':title', $title);
                        $command->bindValue(':createdDate', $createDate);
                        $command->bindValue(':changedDate', $changeDate);
                        $command->bindValue(':statusID', $status);
                        $command->bindValue(':priorityID', $priority);
                        $command->bindValue(':severityID', $severity);
                        $command->bindValue(':timeForSolve', $resolveTime);
                        $command->bindValue(':createdBy', $createdBy);
                        $command->bindValue(':platformID', $platform);
                        $command->bindValue(':functionalityID', $functionality);
                        $command->bindValue(':rootCause', $rootCause);

                        $command->execute();
                        $transaction->commit();
                    } catch (\Exception $e) {
                        echo($e);
                        $transaction->rollBack();
                        throw $e;
                    }
                }
            }
            fclose($handle);

            return true;
        } else {
            return false;
        }
    }
}

