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
     * @covers ::getcResultsç
     * @return void
     */
    public function testGetcResults(): void
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
        self::assertArrayHasKey("results", $datosRecibidos);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function testPostResult201(): array
    {
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@test.com";
        $datos = [
            "username" => $username,
            "email" => $email,
            "enabled" => false,
            "admin" => false,
            "password" => "1234"
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
            "user_id" => $datosRecibidos["user"]["id"],
            "result" => $_result,
            "time" => $datetime->format("d-m-Y H:i:s")
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
        self::assertArrayHasKey("result", $datosRecibidos);
        self::assertArrayHasKey("result", $datosRecibidos["result"]);
        self::assertEquals($_result, $datosRecibidos["result"]["result"]);

        return $datosRecibidos;
    }

    /**
     * @covers ::error
     * @depends testPostResult201
     * @param array $id
     * @return void
     */
    public function testPostResult422(array $createdResult): void
    {
        $datos = [
            "id" => $createdResult["result"]["id"]
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
        self::assertArrayHasKey("message", $datosRecibidos);
        self::assertArrayHasKey("code", $datosRecibidos["message"]);
    }

    /**
     * Implements testGetResult200
     * @depends testPostResult201
     * @covers ::getOneResult
     * @param array $createdResult
     * @return void
     */
    public function testGetResult200(array $createdResult): void
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS . "/" . $createdResult["result"]["id"]
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey("result", $datosRecibidos);
        self::assertArrayHasKey("id", $datosRecibidos["result"]);
    }

    /**
     * Implements testGetResult404
     * @depends testPostResult201
     * @param array $createdResult
     * @covers ::getOneResult
     * @covers ::error
     * @return void
     */
    public function testGetResult404(array $createdResult): void
    {
        $id = ((int) $createdResult["result"]["id"]) + 100;
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS . "/" . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey("message", $datosRecibidos);
        self::assertArrayHasKey("code", $datosRecibidos["message"]);
    }

    /**
     * Implements testPutResult404
     * @depends testPostResult201
     * @covers ::putResult
     * @covers ::error
     * @param array $createdResult
     * @return void
     * @throws
     */
    public function testPutResult404(array $createdResult): void
    {
        $id = random_int(0, 10E6);
        $userId = ((int) $createdResult["result"]["id"]) + 100;
        $result = random_int(0, 32);
        $datos = [
            "user" => $userId,
            "result" => $result
        ];

        self::$client->request(
            Request::METHOD_PUT,
            apiResultsController::API_RESULTS . "/" . $id,
            [], [], [], json_encode($datos)
        );

        /** @var Response $response */
        $response = self::$client->getResponse();

        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(404, $datosRecibidos["message"]["code"]);
        self::assertEquals("NOT FOUND", $datosRecibidos["message"]["message"]);
    }

    /**
     * Implements testPutResult422
     * @depends testPostResult201
     * @covers ::putResult
     * @covers ::error
     * @param array $createdResult
     * @return void
     * @throws
     */
    public function testPutResult422(array $createdResult): void
    {
        $id = ((int) $createdResult["result"]["id"]);
        $result = random_int(0, 32);
        $datos = [
            "result" => $result
        ];

        self::$client->request(
            Request::METHOD_PUT,
            apiResultsController::API_RESULTS . "/" . $id,
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
        self::assertEquals(422, $datosRecibidos["message"]["code"]);
        self::assertEquals("Falta userId", $datosRecibidos["message"]["message"]);
    }
    /**
     * Implements testPutResult200
     * @depends testPostResult201
     * @covers ::putResult
     * @param array $createdResult
     * @return void
     * @throws
     */
    public function testPutResult200(array $createdResult): void
    {
        $id = ((int) $createdResult["result"]["id"]);
        $userId = ((int) $createdResult["result"]["user"]["user"]["id"]);
        $result = random_int(0, 32);
        $newTimestamp = new \DateTime("now");
        $datos = [
            "user_id" => $userId,
            "result" => $result,
            "time" => $newTimestamp
        ];
        self::$client->request(
            Request::METHOD_PUT,
            apiResultsController::API_RESULTS . "/" . $id,
            [], [], [], json_encode($datos)
        );

        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals($result, $datosRecibidos["result"]["result"]);
        self::assertEquals($userId, $datosRecibidos["result"]["user"]["user"]["id"]);
    }

    /**
     * Implements testOptions
     * @covers ::options
     * @return void
     * @throws \Exception
     */
    public function testOptions(): void
    {
        self::$client->request(
            Request::METHOD_OPTIONS,
            apiResultsController::API_RESULTS
        );

        /** @var Response $response */
        $response = self::$client->getResponse();

        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertEquals("GET, POST, OPTIONS", $response->headers->get("Allow"));
    }

    /**
     * Implements testOptions2
     * @covers ::options2
     * @return void
     * @throws \Exception
     */
    public function testOptions2(): void
    {
        $id = random_int(0, 10E6);
        self::$client->request(
            Request::METHOD_OPTIONS,
            apiResultsController::API_RESULTS . "/" . $id
        );

        /** @var Response $response */
        $response = self::$client->getResponse();

        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertEquals("GET, POST, PUT, DELETE, OPTIONS", $response->headers->get("Allow"));
    }

}
