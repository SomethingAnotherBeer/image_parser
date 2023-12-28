<?php
namespace App\Helper;

use App\Exception\ValidationParamException;

trait ValidatorHelper
{

    protected function validateParamByRules(mixed $param, array $rules): void
    {
        $validators = [
            'nonempty' => [
                'func' => fn(mixed $param) => ($param) ? true : false,
                'message' => "Переданный параметр не может быть пустым",
            ],
            'url' => [
                'func' => fn(mixed $param) => filter_var($param, FILTER_VALIDATE_URL),
                'message' => "Переданный параметр не является валидным url",
            ],
        ];

        
        foreach ($validators as $validator_key => $validator_params) {

            $is_err = false;

            if (array_key_exists($validator_key, $rules)) {
                $is_err = $validators[$validator_key]['func']($param);

                if ($is_err) {
                    throw new ValidationParamException($validators[$validator_key]['message']);
                }
            }
        }


    }


}