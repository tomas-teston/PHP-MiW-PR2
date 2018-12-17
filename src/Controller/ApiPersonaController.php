<?php
/**
 * PHP version 7.2
 * demoSF_FdS - ApiPersonaController.php
 *
 * @author   Javier Gil <franciscojavier.gil@upm.es>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 * Date: 15/12/2018
 * Time: 10:37
 */

namespace App\Controller;

use App\Entity\Persona;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiPersonaController
 *
 * @package App\Controller
 *
 * @Route(path=ApiPersonaController::API_PERSONA, name="api_persona_")
 */
class ApiPersonaController extends AbstractController
{

    public const API_PERSONA = '/api/v1/persona';

    /**
     * @Route(path="", name="getc", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getcPersona(): JsonResponse
    {
        /** @var Persona[] $personas */
        $personas = $this->getDoctrine()
            ->getRepository(Persona::class)
            ->findAll();
        return (null === $personas)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                [ 'personas' => $personas ]
            );
    }

    /**
     * @Route(path="/{dni}", name="get", methods={ Request::METHOD_GET })
     * @param Persona|null $persona
     * @return JsonResponse
     */
    public function getPersona(?Persona $persona): JsonResponse
    {
//        /** @var Persona $persona */
//        $persona = $this->getDoctrine()
//            ->getRepository(Persona::class)
//            ->find($dni);
        return (null === $persona)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                $persona
            );
    }

    /**
     * @Route(path="", name="post", methods={ Request::METHOD_POST })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postPersona(Request $request): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        $dni = $datos['dni'] ?? null;
        // Error: falta DNI
        if (null === $dni) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'DNI no existe');
        }

        // Error: DNI ya existe
        /** @var Persona $persona */
        $persona = $em->getRepository(Persona::class)->find($dni);
        if (null !== $persona) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'DNI ya existe');
        }

        // Crear Persona
        $nombre = $datos['nombre'] ?? null;
        $email = $datos['e-mail'] ?? null;
        $persona = new Persona($dni, $nombre, $email);

        // Hacerla persistente
        $em->persist($persona);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($persona, Response::HTTP_CREATED);
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
