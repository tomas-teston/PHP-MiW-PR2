<?php
/**
 * PHP version 7.2
 * demoSF_FdS - ApiPersonaControllerTest.php
 *
 * @author   Javier Gil <franciscojavier.gil@upm.es>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es ETS de Ingeniería de Sistemas Informáticos
 * Date: 15/12/2018
 * Time: 11:26
 */

namespace App\Tests\Controller;

use App\Controller\ApiPersonaController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiPersonaControllerTest
 *
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\ApiPersonaController
 */
class ApiPersonaControllerTest extends WebTestCase
{
    /** @var Client $client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * Implements testGetcPersona200
     * @covers ::getcPersona
     */
    public function testGetcPersona200()
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiPersonaController::API_PERSONA
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('personas', $datosRecibidos);
    }

    /**
     *
     * @return int
     */
    public function testPostPersona201(): int
    {
        $dni = random_int(0, 10E6);
        $datos = [
            'dni' => $dni,
            'nombre' => 'nombre' . $dni,
            'e-mail' => $dni . '@xyz.com'
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiPersonaController::API_PERSONA,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('persona', $datosRecibidos);
        self::assertArrayHasKey('dni', $datosRecibidos['persona']);

        return $dni;
    }

    /**
     * @depends testPostPersona201
     * @param int $dni
     */
    public function testPostPersona400(int $dni)
    {
        $datos = [
            'dni' => $dni
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiPersonaController::API_PERSONA,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('message', $datosRecibidos);
        self::assertArrayHasKey('code', $datosRecibidos['message']);
    }

    /**
     * Implements testGetPersona200
     * @depends testPostPersona201
     * @covers ::getPersona
     * @param int $dni
     */
    public function testGetPersona200(int $dni)
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiPersonaController::API_PERSONA . '/' . $dni
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('persona', $datosRecibidos);
        self::assertArrayHasKey('dni', $datosRecibidos['persona']);
        self::assertEquals($dni, $datosRecibidos['persona']['dni']);
    }

    /**
     * Implements testGetPersona404
     * @covers ::getPersona
     * @covers ::error
     */
    public function testGetPersona404()
    {
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Proveedor de datos de persona
     * @return array
     */
    public function proveedorPersonas(): array
    {
        return [
           'user1' => [ '876132504', 'nombre,hbchdc', 'hgdsakf@xyz.com' ],
        ];
    }
}
