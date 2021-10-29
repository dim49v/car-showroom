<?php

namespace App\Controller;

use App\Entity\Showroom;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

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

    /**
     * @Route("/{id}", name="get_item", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns item from Schema.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Showroom::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Item not found."
     * )
     * @OA\Tag(name="Get item")
     * @Security(name="Bearer")
     */
    public function getItem(int $id): Response
    {
        return parent::getItem($id);
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("", name="create_item", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Item updated.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Showroom::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Item created.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Showroom::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Access denied."
     * )
     * @OA\RequestBody(
     *     request="createItem",
     *     required=true,
     *     description="Need role DIRECTOR.",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"title", "address"},
     *             @OA\Property(type="string", property="title"),
     *             @OA\Property(type="string", property="address"),
     *         )
     *     )
     * )
     * @OA\Tag(name="Create item")
     * @Security(name="Bearer")
     */
    public function createItem(): Response
    {
        return parent::createItem();
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("/{id}", name="update_item", methods={"PUT", "PATCH"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Item updated.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Showroom::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Item nof found."
     * )
     * @OA\Response(
     *     response=401,
     *     description="Access denied."
     * )
     * @OA\RequestBody(
     *     request="createItem",
     *     required=true,
     *     description="Need role DIRECTOR.",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"title", "address"},
     *             @OA\Property(type="string", property="title"),
     *             @OA\Property(type="string", property="address"),
     *         )
     *     )
     * )
     * @OA\Tag(name="Update item")
     * @Security(name="Bearer")
     */
    public function updateItem(int $id): Response
    {
        return parent::updateItem($id);
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("/{id}", name="delete_item", methods={"DELETE"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Item deleted. Need role DIRECTOR."
     * )
     * @OA\Response(
     *     response=401,
     *     description="Access denied."
     * )
     * @OA\Response(
     *     response=404,
     *     description="Item nof found."
     * )
     * @OA\Tag(name="Delete item")
     * @Security(name="Bearer")
     */
    public function deleteItem(int $id): Response
    {
        return parent::deleteItem($id);
    }

    /**
     * @Route("", name="get_list", methods={"GET"})
     *
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     required=false,
     *     description="Filter. Use format '{field}:{value}' or '{association}.{field}:{value}'. Use comma to separate filters.",
     *     example="id:1"
     * )
     *
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     required=false,
     *     description="Order by. Use format '+|-{field}'",
     *     example="-id"
     * )
     *
     * @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *     description="Count items on page. May be integer or 'all'",
     *     example="10"
     * )
     *
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=false,
     *     description="Page number",
     *     example="1"
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns items from Schema.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="count", type="integer"),
     *         @OA\Property(property="page", type="integer"),
     *         @OA\Property(property="perPage", type="integer"),
     *         @OA\Property(property="items", type="array", @OA\Items(ref=@Model(type=Showroom::class, groups={"full"})))
     *     )
     * )
     *
     * @OA\Tag(name="Get list")
     * @Security(name="Bearer")
     */
    public function getList(): Response
    {
        return parent::getList();
    }
}