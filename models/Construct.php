<?php

namespace app\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\console\ExitCode;
use yii\helpers\Json;

class Construct extends Model
{
    private array $ingredientsByCode = [];
    public string $ingredientsTypeList = '';

    /**
     * Вычисляет все возможные комбинации ингредиентов
     *
     * @return string
     */
    public function calc()
    {
        if (!$this->ingredientsTypeList) {
            throw new InvalidArgumentException("No ingredients passed", ExitCode::DATAERR);
        }
        $this->prepareData();
        $resultData = [];

        foreach (str_split($this->ingredientsTypeList) as $i => $code) {
            $resultDataTpl = $resultData;
            $resultData = [];
            foreach ($this->ingredientsByCode[$code] as $ingredient) {
                if ($i === 0) {
                    $resultData[] = [
                        'products' => [[
                            'type' => $ingredient['type'],
                            'value' => $ingredient['title'],
                        ]],
                        'price' => $ingredient['price'],
                        'used' => [$ingredient['id']],
                    ];

                    continue;
                }

                $newData = $resultDataTpl;
                foreach ($newData as &$data) {
                    // Не позволяем использовать несколько ингредиентов
                    // в одной сборке
                    if (in_array($ingredient['id'], $data['used'])) {
                        continue;
                    }
                    $data['products'][] = [
                        'type' => $ingredient['type'],
                        'value' => $ingredient['title'],
                    ];
                    $data['price'] += $ingredient['price'];
                    $data['used'][] = $ingredient['id'];
                }
                unset($data);

                $resultData = array_merge($resultData, $newData);
            }
        }

        $used = [];
        foreach ($resultData as $k => $data) {
            if (count($data['products']) != strlen($this->ingredientsTypeList)) {
                unset($resultData[$k]);
            }

            // Исключаем из списка дублирующие подборки,
            // в которых перетасованы ингредиенты
            sort($data['used']);
            $items = implode(',', $data['used']);
            if (isset($used[$items])) {
                unset($resultData[$k]);
            }
            unset($resultData[$k]['used']);

            $used[$items] = true;
        }

        sort($resultData);
        return Json::encode($resultData);
    }

    /**
     * Заполняет $this->ingredientsByCode необходимыми ингредиентами
     *
     * @return void
     */
    private function prepareData(): void
    {
        $ingredients = str_split($this->ingredientsTypeList);
        $uniqIngredients = array_unique($ingredients);

        $ingredientTypes = IngredientType::find()
            ->select(['id', 'code'])
            ->where(['code' => $uniqIngredients])
            ->indexBy('id')
            ->cache(300)
            ->asArray()
            ->all();

        $typeCodes = [];
        foreach ($ingredientTypes as $type) {
            $typeCodes[$type['id']] = $type['code'];
            $this->ingredientsByCode[$type['code']] = [];
        }

        foreach ($uniqIngredients as $ingredient) {
            if (!in_array($ingredient, $typeCodes)) {
                throw new InvalidArgumentException("Ingredient type $ingredient is not present at list", ExitCode::DATAERR);
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
            ->cache(300)
            ->asArray()
            ->all();

        foreach ($allIngredients as $ingredient) {
            $this->ingredientsByCode[$ingredient['code']][] = $ingredient;
        }

        // Т.к. блюдо не может содержать в себе несколько одинаковых ингредиентов - проверяем,
        // что у нас хватит уникальных ингредиентов для каждого типа
        foreach ($typeCodes as $code) {
            $count = substr_count($this->ingredientsTypeList, $code);
            $maxCount = count($this->ingredientsByCode[$code]);
            if ($count > $maxCount) {
                throw new InvalidArgumentException("Dish can't contain more than $maxCount ingredients with type $code", ExitCode::DATAERR);
            }
        }
    }
}