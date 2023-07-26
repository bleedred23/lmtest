<?php

namespace app\commands;

use app\models\Construct;
use app\models\Ingredient;
use app\models\IngredientType;
use yii\console\Controller;
use yii\console\ExitCode;

class ConstructController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex(string $ingredients): int
    {
        $model = new Construct(['ingredientsTypeList' => $ingredients]);
        $model->calc();
        return ExitCode::OK;
        $ingredients = str_split($ingredients);
        $uniqIngredients = array_unique($ingredients);

        $ingredientTypes = IngredientType::find()
            ->select(['id', 'code'])
            ->where(['code' => $uniqIngredients])
            ->indexBy('id')
            ->asArray()
            ->all();
        var_dump($ingredientTypes);

        $typeCodes = [];
        foreach ($ingredientTypes as $type) {
            $typeCodes[$type['id']] = $type['code'];
        }

        foreach ($uniqIngredients as $ingredient) {
            if (!in_array($ingredient, $typeCodes)) {
                $this->stderr("Ingredient $ingredient is not present at list");
                return ExitCode::DATAERR;
            }
        }

        $allIngredients = Ingredient::find()
            ->where(['type_id' => array_keys($typeCodes)])
            ->asArray()
            ->all();

        $allIngredients = Ingredient::findAll(['type_id']);

        return ExitCode::OK;
    }
}
