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
    private static $user;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$user = self::createUser();
    }

    /**
     * Implements testGetcResults404
     * @covers ::getcResults
     * @return void
     */
    public function testGetcResults404(): void
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultsController::API_RESULTS
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
     * @return int
     * @throws \Exception
     */
    public function testPostResult201(): array
    {
        $userId = self::$user['user']['id'];

        $_result = (int) random_int(0, 100);
        $datetime = new DateTime("now");
        $result = [
            "user_id" => $userId,
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
     * Implements testGetcResults200
     * @covers ::getcResults
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
     * @covers ::error
     * @depends testPostResult201
     * @return void
     * @throws \Exception
     */
    public function testPostResult422(): void
    {
        $id = random_int(0, 32);
        $datos = [
            "id" => $id
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
     * @return void
     * @throws \Exception
     * @covers ::getOneResult
     * @covers ::error
     */
    public function testGetResult404(): void
    {
        $id = random_int(0, 10E6);
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
        $userId = self::$user['user']['id'];
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
     * Implements testRemoveAllResults200
     * @covers ::removeAllResults
     * @return void
     */
    public function testRemoveAllResults200(): void
    {
        self::$client->request(
            Request::METHOD_DELETE,
            apiResultsController::API_RESULTS
        );

        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self::assertEquals("", $response->getContent());
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

    /*
    * Ejecución al final del test
    */
    public static function tearDownAfterClass()
    {
        self::removeUser(self::$user['user']['id']);
    }

    /**
     * Create User
     * @return array $user
     * @throws
     */
    public static function createUser(): array
    {
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@test.com";
        $password = "pass" . $username . "word";
        $datos = [
            'username' => $username,
            'email' => $email,
            'enabled' => true,
            'admin' => false,
            'password' => $password,
        ];
        self::$client->request(
            Request::METHOD_POST,
            apiUsersController::API_USERS,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        $user = json_decode($response->getContent(), true);
        return $user;
    }

    /**
     * Remove User
     * @param int $id
     */
    public static function removeUser(int $id): void
    {
        self::$client->request(
            Request::METHOD_DELETE,
            apiUsersController::API_USERS . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
    }

}
