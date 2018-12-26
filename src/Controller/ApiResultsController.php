<?php
/**
 * PHP version 7.2
 * demoSF_FdS - ApiPersonaController.php
 *
 * @author   Tomás Muñoz Testón <tomini18@hotmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 * Date: 15/12/2018
 * Time: 10:37
 */

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Results;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResultsController
 *
 * @package App\Controller
 *
 * @Route(path=ApiResultsController::API_RESULTS, name="api_results_")
 */
class ApiResultsController extends AbstractController
{

    public const API_RESULTS = '/api/v1/results';

    /**
     * @Route(path="", name="getc", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getcResults(): JsonResponse
    {
        /** @var Users[] $results */
        $results = $this->getDoctrine()
            ->getRepository(Results::class)
            ->findAll();
        return (empty($results))
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                [ 'results' => $results ]
            );
    }

    /**
     * @Route(path="/{id}", name="get", methods={ Request::METHOD_GET })
     * @param Results|null $result
     * @return JsonResponse
     */
    public function getOneResult(?Results $result): JsonResponse
    {
        return (null === $result)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                $result
            );
    }

    /**
     * @Route(path="", name="post", methods={ Request::METHOD_POST })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function postResult(Request $request): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        $userId = $datos['user_id'] ?? null;
        $result = $datos['result'] ?? null;
        $time = $datos['time'] ?? null;

        // Error: falta userId
        if (null === $userId) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta userId");
        }

        // Error: falta resultado
        if (null === $result) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta result");
        }

        // Error: username no existe
        $user = $em->getRepository(Users::class)->find($userId);
        if ($user === null) {
            return $this->error(Response::HTTP_NOT_FOUND, 'No existe usuario con id: ' . $userId);
        }

        // Crear resultado
        $result = new Results($result, new DateTime($time), $user);

        // Hacerla persistente
        $em->persist($result);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($result, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param Results $result
     * @Route(path="/{id}", name="put", methods={ Request::METHOD_PUT })
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function putResult(?Results $result, Request $request): JsonResponse
    {
        if (null === $result) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        $userId = $datos['user_id'] ?? null;
        $newResult = $datos['result'] ?? null;
        $newTimestamp = new \DateTime('now');

        // Error: falta userId
        if (null === $userId) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta userId');
        }

        // Error: falta result
        if (null === $newResult) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta result');
        }

        //Error: USER no existe
        $userDB = $this->getDoctrine()
            ->getRepository(Users::class)
            ->find($userId);
        if (null === $userDB) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER NOT FOUND');
        }
        // Modificar Result
        $result->setResult($newResult);
        $result->setTime($newTimestamp);
        $result->setUser($userDB);
        // Hacerla persistente

        $em->persist($result);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($result, Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", name="remove", methods={ Request::METHOD_DELETE })
     * @param Results|null $result
     * @return JsonResponse
     */
    public function removeResult(?Results $result): JsonResponse
    {
        if (null === $result) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }
        $em = $this->getDoctrine()->getManager();
        $id = $result->getId();
        $em->remove($result);
        $em->flush();

        // devolver respuesta
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="", name="removeAll", methods={ Request::METHOD_DELETE })
     * @return JsonResponse
     */
    public function removeAllResults(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Results[] $results */
        $results = $this->getDoctrine()
            ->getRepository(Results::class)
            ->findAll();

        foreach ($results as $result) {
            $em->remove($result);
            $em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="", name="options", methods={ Request::METHOD_OPTIONS })
     * @return JsonResponse
     */
    public function optionsResult(): JsonResponse
    {
        // devolver respuesta
        return new JsonResponse(null, Response::HTTP_OK, array("Allow" => "GET, POST, OPTIONS"));
    }

    /**
     * @Route(path="/{id}", name="options2", methods={ Request::METHOD_OPTIONS })
     * @return JsonResponse
     */
    public function optionsResult2(): JsonResponse
    {
        // devolver respuesta
        return new JsonResponse(null, Response::HTTP_OK, array("Allow" => "GET, POST, PUT, DELETE, OPTIONS"));
    }

    /**
     * @param int $statusCode
     * @param string $message
     *
     * @return JsonResponse
     */
    private function error(int $statusCode, string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => [
                    'code' => $statusCode,
                    'message' => $message
                ]
            ],
            $statusCode
        );
    }
}
