<?php
namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequiredParamMissedException extends BadRequestHttpException
{
    
}