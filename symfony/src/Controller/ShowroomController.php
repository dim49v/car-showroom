<?php

namespace App\Controller;

use App\Entity\Showroom;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/showrooms", name="showrooms_")
 */
class ShowroomController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = Showroom::class;
    }
}