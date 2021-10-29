<?php

namespace App\Controller;

use App\Controller\Traits\ExceptionTrait;
use App\Entity\User;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @Route("/auth", name="auth_")
 */
class AuthController extends AbstractController
{
    use ExceptionTrait;

    protected Request $request;
    protected UserPasswordHasherInterface $passwordHasher;

    /**
     * AuthController constructor.
     */
    public function __construct(
        RequestStack $requestStack,
        UserPasswordHasherInterface $hasher
    ) {
        if (null === $request = $requestStack->getCurrentRequest()) {
            throw $this->createBadRequestException('Malformed request.');
        }
        $this->request = $request;
        $this->passwordHasher = $hasher;
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Authorization data.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="success", type="boolean"),
     *         @OA\Property(property="session", type="object",
     *             @OA\Property(property="ttl", type="integer"),
     *             @OA\Property(property="token", type="string")
     *         ),
     *         @OA\Property(property="user", type="object", ref=@Model(type=User::class, groups={"login"}))
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Access denied."
     * )
     *
     * @OA\RequestBody(
     *     request="authLogin",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"phone", "password"},
     *             @OA\Property(type="string", property="phone"),
     *             @OA\Property(type="string", property="password")
     *         )
     *     )
     * )
     * @Security(name="")
     */
    public function authLogin()
    {
    }

    /**
     * @Route("/logout", name="logout", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Logout.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="success", type="boolean"),
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function authLogout()
    {
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Ures data.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="success", type="boolean"),
     *         @OA\Property(property="user", type="object", ref=@Model(type=User::class, groups={"register"}))
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="BadRequest."
     * )
     *
     * @OA\RequestBody(
     *     request="authRegister",
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
     *             @OA\Property(type="string", property="newPassword")
     *         )
     *     )
     * )
     * @Security(name="")
     */
    public function authRegister(): JsonResponse
    {
        $content = $this->get('serializer')->decode($this->request->getContent(), 'json');
        if (empty($content['firstName'])
            || empty($content['lastName'])
            || empty($content['patronymic'])
            || empty($content['phone'])
            || empty($content['newPassword'])) {
            throw $this->createBadRequestException('Fields "firstName", "lastName", "patronymic", "phone", "newPassword" are required.');
        }

        unset($content['id']);
        try {
            /** @var User $user */
            $user = $this->container->get('serializer')->denormalize($content, User::class, 'json');

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            if (!$this->getDoctrine()->getManager()->contains($user)) {
                throw new RuntimeException('User was not created in DB');
            }
        } catch (Exception $exception) {
            throw $this->createException('Error saving new user.', $exception);
        }

        return $this->json(
            [
                'success' => true,
                'user' => $user,
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['register']]
        );
    }

    /**
     * @Route("/register/check-phone", name="register_check_phone", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Check exists phone.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="exists", type="boolean")
     *     )
     * )
     *
     * @OA\RequestBody(
     *     request="authRegisterCheckPhone",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"phone"},
     *             @OA\Property(type="string", property="phone")
     *         )
     *     )
     * )
     * @Security(name="")
     */
    public function authRegisterCheckPhone(): JsonResponse
    {
        $content = $this->get('serializer')->decode($this->request->getContent(), 'json');
        if (empty($content['phone'])) {
            throw $this->createBadRequestException('Field "phone" is required.');
        }

        $user = $this->getDoctrine()->getManager()
            ->getRepository(User::class)
            ->findOneByIdentifier($content['phone']);
        if (!($user instanceof User)) {
            return $this->json(
                [
                    'exists' => false,
                ]
            );
        }

        return $this->json(
            [
                'exists' => true,
            ]
        );
    }
}
