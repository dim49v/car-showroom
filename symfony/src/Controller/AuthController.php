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
     */
    public function authLogin()
    {
    }

    /**
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function authLogout()
    {
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
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
     * @Route("/register/check-email", name="register_check_email", methods={"POST"})
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
