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

use App\Entity\Results;
use App\Entity\Users;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiUsersResultsController
 *
 * @package App\Controller
 *
 * @Route(path=ApiUsersResultsController::API_USERS_RESULTS, name="api_users_results")
 */
class ApiUsersResultsController extends AbstractController
{

    public const API_USERS_RESULTS = '/api/v1/users';
    public const URL_RESULTS = '/results';


    /**
     * @param Users $user
     * @Route(path="/{id}/results", name="getAllResultsByUser", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getAllResultsByUser(?Users $user): JsonResponse
    {
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'No existe usuario con ese id');
        }
        /** @var Results[] results */
        $results = $this->getDoctrine()
            ->getRepository(Results::class)
            ->findBy(['user' => $user->getId()]);

        return (empty($results))
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(array("results" => $results));
    }

    /**
     * @param Users $user
     * @Route(path="/{id}/results", name="removeAllResultsByUser", methods={ Request::METHOD_DELETE })
     * @return JsonResponse
     */
    public function removeAllResultsByUser(?Users $user): JsonResponse
    {
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'No existe usuario con ese id');
        }
        /** @var Results[] results */
        $results = $this->getDoctrine()
            ->getRepository(Results::class)
            ->findBy(['user' => $user->getId()]);

        $em = $this->getDoctrine()->getManager();
        foreach ($results as $result) {
            $em->remove($result);
            $em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
