<?php

namespace App\Controller\Traits;

use DateTime;
use Exception;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

trait CustomFunctionsTrait
{
    /**
     * @param mixed $data
     */
    protected function makeCustomResponse(
        $data,
        int $responseCode = Response::HTTP_OK,
        array $context = []
    ): Response {
        if (!isset($context['groups'])) {
            $context['groups'] = 'full';
        }

        return $this->json(
            $data,
            $responseCode,
            [],
            $context
        );
    }

    protected function makeContextModificationActions(array &$context): void
    {
    }

    protected function makePrePersistActions(object $item, array $content): void
    {
    }

    protected function makePostPersistActions(object $item, array $content): void
    {
    }

    protected function makePreUpdateActions(object $item, array $content): void
    {
    }

    protected function makePostUpdateActions(object $item, array $content): void
    {
    }

    protected function makePreRemoveActions(object $item): void
    {
    }

    protected function makePostRemoveActions(object $item): void
    {
    }

    protected function createFileName(array $context, string $type = 'pdf'): string
    {
        try {
            $className = (new ReflectionClass($this->entityName))
                ->getShortName();
            $name = u($className)->snake();
            $dateString = (new DateTime())->format('Y-m-d_H-i-s');
        } catch (Exception $exception) {
            throw $this->createException('Error naming file.', $exception);
        }

        return $name->truncate(40)->append('_report_', $dateString, '.', $type)->toString();
    }
}
