<?php

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

trait ExceptionTrait
{
    /**
     * @final
     */
    protected function createBadRequestException(
        string $message = 'Bad data',
        Throwable $previous = null
    ): BadRequestHttpException {
        return new BadRequestHttpException($message, $previous);
    }

    /**
     * @final
     */
    protected function createException(
        string $message = 'Server error',
        Throwable $previous = null
    ): HttpException {
        return new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $previous);
    }
}
