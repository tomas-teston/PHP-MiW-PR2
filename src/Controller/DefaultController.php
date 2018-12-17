<?php
/**
 * PHP version 7.2
 * demoSF_FdS - DefaultController.php
 *
 * @author   Javier Gil <franciscojavier.gil@upm.es>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 * Date: 14/12/2018
 * Time: 18:03
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 *
 * @package App\Controller
 */
class DefaultController extends AbstractController
{

    /**
     * @Route(path="/saluda/{nombre}", name="index")
     * index
     * @return Response
     */
    public function index(string $nombre = null): Response
    {
        $sujeto = $nombre ?? 'MiW';
        $vector = [1, 2, 3, 4, 5];
        return $this->render(
            'Default/index.html.twig',
            [
                'persona' => $sujeto,
                'datos' => $vector
            ]
        );
    }

    /**
     * @Route(path="/adios", name="dile_adios", methods={ "GET" })
     * @return Response
     */
    public function despidete(): Response
    {
        return new Response('Hasta luego!!!');
    }
}
