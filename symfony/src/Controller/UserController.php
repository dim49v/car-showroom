<?php

namespace App\Controller;

use App\Entity\Enum\RoleEnum;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @Route("/users", name="users_")
 */
class UserController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = User::class;
    }

    protected function makeFilterModifications(array &$filters): void
    {
        if (!$this->isGranted(RoleEnum::ROLE_DIRECTOR)) {
            $filters['manager'] = [false];
            $filters['director'] = [false];
        }
    }

    protected function makePrePersistActions(object $item, array $content): void
    {
        /** @var $item User */
        if (!$this->isGranted(RoleEnum::ROLE_DIRECTOR)
            && ($item->isManager() || $item->isDirector())) {
            throw $this->createAccessDeniedException();
        }
    }

    protected function makePreUpdateActions(object $item, array $content): void
    {
        $this->makePrePersistActions($item, $content);
    }

    /**
     * @Route("/{id}", name="get_item", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns item from Schema. Need role MANAGER or DIRECTOR.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=User::class, groups={"full"})
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
     *         ref=@Model(type=User::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Item created.",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=User::class, groups={"full"})
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Access denied."
     * )
     * @OA\RequestBody(
     *     request="createItem",
     *     description="Need role MANAGER or DIRECTOR. Need role DIRECTOR to set 'manager', 'director'. 'manager' must have 'showroom'.",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"lastName", "firstName", "patronymic", "phone", "password"},
     *             @OA\Property(type="string", property="lastName"),
     *             @OA\Property(type="string", property="firstName"),
     *             @OA\Property(type="string", property="patronymic"),
     *             @OA\Property(type="string", property="phone"),
     *             @OA\Property(type="string", property="newPassword"),
     *             @OA\Property(type="integer", property="showroom"),
     *             @OA\Property(type="boolean", property="manager"),
     *             @OA\Property(type="boolean", property="director"),
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
     *         ref=@Model(type=User::class, groups={"full"})
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
     *     description="Need role MANAGER or DIRECTOR. Need role DIRECTOR to set 'manager', 'director'. 'manager' must have 'showroom'.",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"lastName", "firstName", "patronymic", "phone", "password"},
     *             @OA\Property(type="string", property="lastName"),
     *             @OA\Property(type="string", property="firstName"),
     *             @OA\Property(type="string", property="patronymic"),
     *             @OA\Property(type="string", property="phone"),
     *             @OA\Property(type="string", property="newPassword"),
     *             @OA\Property(type="integer", property="showroom"),
     *             @OA\Property(type="boolean", property="manager"),
     *             @OA\Property(type="boolean", property="director"),
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
     *     example="manager:true,showrooom.id:1"
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
     *     description="Returns items from Schema. Need role MANAGER or DIRECTOR.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="count", type="integer"),
     *         @OA\Property(property="page", type="integer"),
     *         @OA\Property(property="perPage", type="integer"),
     *         @OA\Property(property="items", type="array", @OA\Items(ref=@Model(type=User::class, groups={"full"})))
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