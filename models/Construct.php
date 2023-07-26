<?php

namespace app\models;

use yii\base\Model;
use yii\console\ExitCode;

class Construct extends Model
{
    private array $ingredientsByCode = [];
    public string $ingredientsTypeList = '';

    public function calc()
    {
        $this->prepareData();
        $usedIngredients = [];
        $resultData = [
            [
                'products' => [],
                'price' => 0
            ]
        ];

        $used = [];
//        var_dump(str_split($this->ingredientsTypeList)); die();
        foreach (str_split($this->ingredientsTypeList) as $i => $code) {
            $newResultData = [];
            $k = 0;
            foreach ($this->ingredientsByCode[$code] as $ingredient) {
                if (in_array($ingredient['id'], $used)) {
                    continue;
                }

                $resultDataBefore = $resultData;
                $used[] = $ingredient['id'];
                foreach ($resultDataBefore as &$data) {
                    $data['products'][$i] = [
                        'type' => $ingredient['type'],
                        'value' => $ingredient['title'],
                    ];
                    $data['price'] += $ingredient['price'];
                }
                unset($data);
                if ($k === 0) {
                    $newResultData = $resultDataBefore;
                    $k++;
                } {

                $newResultData[] = $resultDataBefore;
                }
            }

            var_dump([
                $resultData,
                $newResultData,
            ]);
            if ($newResultData) {
                $resultData = $newResultData;
            }
        }

//        var_dump($resultData);
    }

    private function prepareData(): void
    {
        $ingredients = str_split($this->ingredientsTypeList);
        $uniqIngredients = array_unique($ingredients);

        $ingredientTypes = IngredientType::find()
            ->select(['id', 'code'])
            ->where(['code' => $uniqIngredients])
            ->indexBy('id')
            ->asArray()
            ->all();

        $typeCodes = [];
        foreach ($ingredientTypes as $type) {
            $typeCodes[$type['id']] = $type['code'];
            $this->ingredientsByCode[$type['code']] = [];
        }

        foreach ($uniqIngredients as $ingredient) {
            if (!in_array($ingredient, $typeCodes)) {
                $this->addError('ingredients', "Ingredient $ingredient is not present at list");
            }
        }


        $allIngredients = Ingredient::find()
            ->alias('i')
            ->select([
                'type_id', 'price', 'i.title',
                'it.title as type', 'code', 'i.id as id'
                ])
            ->where(['type_id' => array_keys($typeCodes)])
            ->innerJoin(
                'ingredient_type it',
                'i.type_id = it.id'
            )
            ->asArray()
            ->all();

        foreach ($allIngredients as $ingredient) {
            $this->ingredientsByCode[$ingredient['code']][] = $ingredient;
        }
    }
}