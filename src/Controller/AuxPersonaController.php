<?php
/**
 * PHP version 7.2
 * demoSF_FdS - PersonaController.php
 *
 * @author   Javier Gil <franciscojavier.gil@upm.es>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 * Date: 14/12/2018
 * Time: 20:53
 */

namespace App\Controller;

use App\Entity\Persona;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PersonaController
 *
 * @package App\Controller
 *
 * @Route(path="/aux/persona", name="aux_persona_")
 */
class AuxPersonaController extends AbstractController
{

    /**
     * @Route(path="", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Persona[] $personas */
        $personas = $em->getRepository(Persona::class)->findAll();

        return $this->render(
            'AuxPersona/index.html.twig',
            [ 'personas' => $personas ]
        );
    }

    /**
     * @Route(path="/json", name="listado_json", methods={ "GET" })
     * @return JsonResponse
     */
    public function listadoJSON(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Persona[] $personas */
        $personas = $em->getRepository(Persona::class)->findAll();
        return new JsonResponse(
            [ 'personas' => $personas ]
        );
    }

    /**
     * Nueva Persona
     *
     * @Route("/nueva", name="nueva")
     * @return Response
     * @throws \Exception
     */
    public function nuevaPersona(): Response
    {
        $num = random_int(0, 10E6);
        $persona = new Persona($num, 'Nombre_' . $num, $num . '@xyz.com');
        $em = $this->getDoctrine()->getManager();
        $em->persist($persona);
        $em->flush();

        return $this->render(
            'AuxPersona/nueva.html.twig',
            ['persona' => $persona ]
        );
    }
}
