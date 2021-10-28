<?php

namespace App\Controller;

use App\Entity\Enum\RoleEnum;
use App\Entity\Purchase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/purchases", name="purchases_")
 */
class PurchaseController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = Purchase::class;
    }

    protected function makePrePersistActions(object $item, array $content): void
    {
        /** @var $item Purchase */
        if (!$this->isGranted(RoleEnum::ROLE_DIRECTOR)
            && $item->getManager() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!in_array(RoleEnum::ROLE_MANAGER, $item->getManager()->getRoles())) {
            throw $this->createBadRequestException("Current user isn't manager.");
        }

        if ($item->getCar()->getShowroom() !== $item->getManager()->getShowroom()) {
            throw $this->createBadRequestException("Manager can't sell a car from another showroom.");
        }
    }

    protected function makePreUpdateActions(object $item, array $content): void
    {
        $this->makePrePersistActions($item, $content);
    }

    protected function makeFilterModifications(array &$filters): void
    {
        if (!$this->isGranted(RoleEnum::ROLE_DIRECTOR)) {
            $filters['manager'] = $this->getUser()->getId();
        }
    }
}