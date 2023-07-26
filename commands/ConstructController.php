<?php

namespace app\commands;

use app\models\Construct;
use yii\console\Controller;
use yii\console\ExitCode;

class ConstructController extends Controller
{

    public function actionIndex(string $ingredients): int
    {
        $model = new Construct(['ingredientsTypeList' => $ingredients]);

        try {
            $output = $model->calc();
            $this->stdout($output);
        } catch (\Exception $e) {
            $this->stderr($e->getMessage());
            return $e->getCode();
        }

        return ExitCode::OK;
    }
}
