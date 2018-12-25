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
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiUsersController
 *
 * @package App\Controller
 *
 * @Route(path=ApiUsersController::API_USERS, name="api_users_")
 */
class ApiUsersController extends AbstractController
{

    public const API_USERS = '/api/v1/users';

    /**
     * @Route(path="", name="getc", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getcUser(): JsonResponse
    {
        /** @var Users[] $users */
        $users = $this->getDoctrine()
            ->getRepository(Users::class)
            ->findAll();
        return (null === $users)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                [ 'users' => $users ]
            );
    }

    /**
     * @Route(path="/{id}", name="get", methods={ Request::METHOD_GET })
     * @param Users|null $user
     * @return JsonResponse
     */
    public function getOneUser(?Users $user): JsonResponse
    {
        return (null === $user)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                $user
            );
    }

    /**
     * @Route(path="", name="post", methods={ Request::METHOD_POST })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUser(Request $request): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        $username = $datos['username'] ?? null;
        $email = $datos['email'] ?? null;
        // Error: falta username
        if (null === $username) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta username");
        }

        // Error: falta email
        if (null === $email) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta email");
        }

        // Error: username ya existe
        $userLength = $em->getRepository(Users::class)->count(['username' => $username]);
        if ($userLength !== 0) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'Nombre de usuario ya existe');
        }

        // Error: email ya existe
        $userLength = $em->getRepository(Users::class)->count(['email' => $email]);
        if ($userLength !== 0) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'Email ya existe');
        }

        // Crear User

        $enabled = $datos['enabled'] ?? null;
        $admin = $datos['admin'] ?? null;
        $password = $datos['password'] ?? null;

        $user = new Users($username, $email, $enabled, $admin, $password);

        // Hacerla persistente
        $em->persist($user);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($user, Response::HTTP_CREATED);
    }

    /**
     * @Route(path="/{id}", name="put", methods={ Request::METHOD_PUT })
     * @param Users|null $user
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function putUser(?Users $user, Request $request): JsonResponse
    {
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        $username = $datos['username'] ?? null;
        $email = $datos['email'] ?? null;

        // Error: falta username
        if (null === $username) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta username");
        }

        // Error: falta email
        if (null === $email) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, "Falta email");
        }

        // Error: username ya existe
        $userLength = $em->getRepository(Users::class)->count(['username' => $username]);
        if ($userLength !== 0) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'Nombre de usuario ya existe');
        }

        // Error: email ya existe
        $userLength = $em->getRepository(Users::class)->count(['email' => $email]);
        if ($userLength !== 0) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'Email ya existe');
        }

        // Modificar User

        $enabled = $datos['enabled'] ?? null;
        $admin = $datos['admin'] ?? null;
        $password = $datos['password'] ?? null;

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEnabled($enabled);
        $user->setAdmin($admin);
        $user->setPassword($password);

        // Hacerla persistente
        $em->persist($user);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", name="remove", methods={ Request::METHOD_DELETE })
     * @param Users|null $user
     * @return JsonResponse
     */
    public function removeUser(?Users $user): JsonResponse
    {
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }
        $em = $this->getDoctrine()->getManager();
        $id = $user->getId();
        $username = $user->getUsername();
        $em->remove($user);
        $em->flush();

        // devolver respuesta
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
