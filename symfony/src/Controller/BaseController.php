<?php

namespace App\Controller;

use App\Controller\Traits\CustomFunctionsTrait;
use App\Controller\Traits\ExceptionTrait;
use App\Controller\Traits\QueryParamsTrait;
use App\Repository\CustomRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

abstract class BaseController extends AbstractController
{
    use CustomFunctionsTrait;
    use ExceptionTrait;
    use QueryParamsTrait;

    protected Request $request;
    protected string $entityName;

    /**
     * BaseController constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        if (null === $request = $requestStack->getCurrentRequest()) {
            throw $this->createBadRequestException('Malformed request.');
        }
        $this->request = $request;
    }

    /**
     * @Route("/{id}", name="get_item", methods={"GET"})
     */
    public function getItem(int $id): Response
    {
        $item = $this->getDoctrine()->getManager()->find($this->entityName, $id);
        if (null === $item) {
            throw $this->createNotFoundException("{$this->entityName} with identifier {$id} not found.");
        }

        return $this->makeCustomResponse(
            $item
        );
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("", name="create_item", methods={"POST"})
     */
    public function createItem(): Response
    {
        if (!$this->container->has('serializer')) {
            throw $this->createException('Cant find serializer service.');
        }

        $context = [];
        $this->makeContextModificationActions($context);

        try {
            $responseCode = Response::HTTP_CREATED;

            $content = $this->request->getContent();
            if (!empty($content)) {
                $content = $this->container->get('serializer')->decode($content, 'json');
            } else {
                $content = [];
            }

            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->getDoctrine()->getManager()->getClassMetadata($this->entityName);
            $identifiers = $classMetadata->getIdentifier();
            if (count(array_intersect_key(array_flip($identifiers), $content)) === count($identifiers)) {
                $filterById = [];
                foreach ($identifiers as $identifier) {
                    $filterById[$identifier] = $content[$identifier];
                }

                $oldItem = $this->getDoctrine()->getManager()->getRepository($this->entityName)->findOneBy($filterById);
                if (null !== $oldItem) {
                    $context['object_to_populate'] = $oldItem;
                    $responseCode = Response::HTTP_OK;
                }
            }

            $item = $this->container->get('serializer')->denormalize(
                $content,
                $this->entityName,
                'json',
                $context
            );
            $this->makePrePersistActions($item, $content);

            $this->getDoctrine()->getManager()->persist($item);
            $this->getDoctrine()->getManager()->flush();
            if (!$this->getDoctrine()->getManager()->contains($item)) {
                throw new RuntimeException('Item was not created in DB');
            }

            $this->makePostPersistActions($item, $content);
        } catch (ExceptionInterface $exception) {
            throw $this->createException('Error serializing request.', $exception);
        } catch (Exception $exception) {
            throw $this->createException($exception->getMessage(), $exception);
        }

        return $this->makeCustomResponse(
            $item,
            $responseCode
        );
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("/{id}", name="update_item", methods={"PUT", "PATCH"})
     */
    public function updateItem(int $id): Response
    {
        $item = $this->getDoctrine()->getManager()->find($this->entityName, $id);
        if (null === $item) {
            throw $this->createNotFoundException("{$this->entityName} with identifier {$id} not found.");
        }

        if (!$this->container->has('serializer')) {
            throw $this->createException('Cant find serializer service.');
        }

        $context = [
            'object_to_populate' => $item,
        ];

        $content = $this->request->getContent();
        if (!empty($content)) {
            $content = $this->container->get('serializer')->decode($content, 'json');
        } else {
            throw $this->createBadRequestException('Empty data');
        }

        try {
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->getDoctrine()->getManager()->getClassMetadata($this->entityName);
            $identifier = $classMetadata->getSingleIdentifierFieldName();
            if (!empty($content[$identifier])) {
                unset($content[$identifier]);
            }

            $item = $this->container->get('serializer')->denormalize(
                $content,
                $this->entityName,
                'json',
                $context
            );

            $this->makePreUpdateActions($item, $content);

            $this->getDoctrine()->getManager()->persist($item);
            $this->getDoctrine()->getManager()->flush();

            $this->makePostUpdateActions($item, $content);
        } catch (HttpException $exception) {
            throw $exception;
        } catch (ExceptionInterface $exception) {
            throw $this->createException('Error serializing request.', $exception);
        } catch (Exception $exception) {
            throw $this->createException('Error processing request.', $exception);
        }

        return $this->makeCustomResponse(
            $item
        );
    }

    /**
     * @IsGranted("ROLE_MANAGER")
     * @Route("/{id}", name="delete_item", methods={"DELETE"})
     */
    public function deleteItem(int $id): Response
    {
        $item = $this->getDoctrine()->getManager()->find($this->entityName, $id);
        if (null === $item) {
            throw $this->createNotFoundException("{$this->entityName} with identifier {$id} not found.");
        }

        try {
            $this->makePreRemoveActions($item);

            $this->getDoctrine()->getManager()->remove($item);
            $this->getDoctrine()->getManager()->flush();

            if ($this->getDoctrine()->getManager()->contains($item)) {
                throw new RuntimeException('Item was not removed from DB');
            }

            $this->makePostRemoveActions($item);
        } catch (HttpException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw $this->createException('Error removing from DB', $exception);
        }

        return $this->makeCustomResponse(
            ['message' => "{$this->entityName} with identifier {$id} was deleted."],
            __FUNCTION__
        );
    }

    /**
     * @Route("", name="get_list", methods={"GET"})
     */
    public function getList(): Response
    {
        $repository = $this->getDoctrine()->getManager()->getRepository($this->entityName);
        $filter = $this->getFilterParam();

        if ($repository instanceof CustomRepository) {
            $items = $repository->findByPaginated(
                $filter,
                $this->getOrderParam(),
                $this->getLimit(),
                $this->getOffset()
            );
        } else {
            $items = $repository->findBy(
                $filter,
                $this->getOrderParam(),
                $this->getLimit(),
                $this->getOffset()
            );
        }
        $count = count($items);

        $perPage = $this->getPerPageParam();

        return $this->makeCustomResponse(
            [
                'count' => $count,
                'page' => $perPage ? $this->getPageParam() : 1,
                'perPage' => $perPage ?: $count,
                'items' => $items,
            ]
        );
    }
}