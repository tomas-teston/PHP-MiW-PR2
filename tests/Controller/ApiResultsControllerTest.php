<?php
/**
 * Created by PhpStorm.
 * User: Tomas
 * Date: 21/12/2018
 * Time: 13:56
 */

namespace App\Tests\Controller;

use App\Controller\ApiResultsController;
use App\Controller\ApiUsersController;
use App\Entity\Results;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class ApiResultsControllerTest extends WebTestCase
{

    /** @var Client $client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * Implements testGetcResults200
     * @covers ::getcResults
     */
    public function testGetcResults()
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('results', $datosRecibidos);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function testPostResult201(): int
    {
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@myemail.com";
        $datos = [
            'username' => $username,
            'email' => $email,
            'enabled' => false,
            'admin' => false,
            'password' => "1234"
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiUsersController::API_USERS,
            [], [], [], json_encode($datos)
        );

        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);

        $_result = (int) random_int(0, 100);
        $datetime = new DateTime("now");
        $result = [
            'user_id' => $datosRecibidos['User']['id'],
            'result' => $_result,
            'time' => $datetime->format('d-m-Y H:i:s')
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiResultsController::API_RESULTS,
            [], [], [], json_encode($result)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('result', $datosRecibidos);
        self::assertArrayHasKey('result', $datosRecibidos['result']);
        self::assertEquals($_result, $datosRecibidos['result']['result']);

        return $datosRecibidos['result']['id'];
    }

    /**
     * @depends testPostResult201
     * @param int $id
     */
    public function testPostResult422(int $id)
    {
        $datos = [
            'id' => $id
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiResultsController::API_RESULTS,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('message', $datosRecibidos);
        self::assertArrayHasKey('code', $datosRecibidos['message']);
    }

    /**
     * Implements testGetResult200
     * @depends testPostResult201
     * @covers ::getOneResult
     * @param int $id
     */
    public function testGetResult200(int $id)
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('result', $datosRecibidos);
        self::assertArrayHasKey('id', $datosRecibidos['result']);
        self::assertEquals($id, $datosRecibidos['result']['id']);
    }

    /**
     * Implements testGetResult404
     * @depends testPostResult201
     * @param int $id
     * @covers ::getOneResult
     * @covers ::error
     */
    public function testGetResult404(int $id)
    {
        $id = $id + 100;
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('message', $datosRecibidos);
        self::assertArrayHasKey('code', $datosRecibidos['message']);
    }

}
