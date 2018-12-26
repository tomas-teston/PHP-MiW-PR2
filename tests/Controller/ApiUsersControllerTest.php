<?php
/**
 * Created by PhpStorm.
 * User: Tomas
 * Date: 20/12/2018
 * Time: 0:14
 */

namespace App\Tests\Controller;

use App\Controller\ApiUsersController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiUsersControllerTest extends WebTestCase
{

    /** @var Client $client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * Implements testGetcUser404
     * @covers ::getcUser
     * @covers ::error
     * @return void
     */
    public function testGetcUser404(): void
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS
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
     * @covers ::error
     * @return int
     * @throws \Exception
     */
    public function testPostUser201(): int
    {
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@myemail.com";
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
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey("user", $datosRecibidos);
        self::assertArrayHasKey("username", $datosRecibidos["user"]);
        self::assertEquals($username, $datosRecibidos["user"]["username"]);

        return $datosRecibidos["user"]["id"];
    }

    /**
     * @depends testPostUser201
     * @param int $id
     * @covers ::error
     * @return void
     */
    public function testPostUser422(int $id): void
    {
        $datos = [
            "id" => $id
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiUsersController::API_USERS,
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
     * Implements testGetcUser200
     * @covers ::getcUser
     * @covers ::error
     * @return void
     */
    public function testGetcUser200(): void
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey("users", $datosRecibidos);
    }


    /**
     * Implements testGetUser200
     * @depends testPostUser201
     * @covers ::getUser
     * @param int $id
     * @return array $response
     */
    public function testGetUser200(int $id): array
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS . "/" . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey("user", $datosRecibidos);
        self::assertArrayHasKey("id", $datosRecibidos["user"]);
        self::assertEquals($id, $datosRecibidos["user"]["id"]);

        return $datosRecibidos;
    }

    /**
     * Implements testGetUser404
     * @depends testPostUser201
     * @param int $id
     * @covers ::getUser
     * @covers ::error
     * @return void
     */
    public function testGetUser404(int $id): void
    {
        $id = $id + 100;
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS . "/" . $id
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
     * Implements testGetUser200
     * @covers ::postUser
     * @covers ::error
     * @param array $user
     * @depends testGetUser200
     * @return void
     * @throws
     */
    public function testPostUser400(array $user): void
    {
        $username = $user["user"]["username"];
        $email = $username . "@test.com";
        $password = "password-" . $username;
        $datos = [
            "username" => $username,
            "email" => $email,
            "enabled" => true,
            "admin" => false,
            "password" => $password,
        ];
        self::$client->request(
            Request::METHOD_POST,
            apiUsersController::API_USERS,
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
        self::assertEquals(400, $datosRecibidos["message"]["code"]);
        self::assertEquals("Nombre de usuario ya existe", $datosRecibidos["message"]["message"]);
    }
    /**
     * Implements testPutUser400
     * @depends testGetUser200
     * @covers ::putUser
     * @covers ::error
     * @param array $user
     * @return void
     * @throws
     */
    public function testPutUser400(array $user): void
    {
        $id = $user["user"]["id"];
        $username = $user["user"]["username"];
        $email = $username . "@test.com";
        $password = "password-" . $username;
        $datos = [
            "username" => $username,
            "email" => $email,
            "enabled" => true,
            "admin" => false,
            "password" => $password,
        ];
        self::$client->request(
            Request::METHOD_PUT,
            apiUsersController::API_USERS . "/" . $id,
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
        self::assertEquals(400, $datosRecibidos["message"]["code"]);
        self::assertEquals("Nombre de usuario ya existe", $datosRecibidos["message"]["message"]);
    }

    /**
     * Implements testPutUser200
     * @depends testGetUser200
     * @covers ::postUser
     * @param array $user
     * @return void
     * @throws
     */
    public function testPutUser200(array $user): void
    {
        $id = $user["user"]["id"];
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@test.com";
        $password = "password-" . $username;
        $datos = [
            "username" => $username,
            "email" => $email,
            "enabled" => true,
            "admin" => false,
            "password" => $password,
        ];
        self::$client->request(
            Request::METHOD_PUT,
            apiUsersController::API_USERS . "/" . $id,
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
        self::assertEquals($username, $datosRecibidos["user"]["username"]);
        self::assertEquals($email, $datosRecibidos["user"]["email"]);
        self::assertEquals(true, $datosRecibidos["user"]["enabled"]);
        self::assertEquals(false, $datosRecibidos["user"]["admin"]);
    }

    /**
     * Implements testPutUser422
     * @depends testGetUser200
     * @covers ::postUser
     * @covers ::error
     * @param array $user
     * @return void
     * @throws
     */
    public function testPutUser422(array $user): void
    {
        $id = $user["user"]["id"];
        $username = "user_" . (string) random_int(0, 10E6);
        $email = $username . "@test.com";
        $password = "password-" . $username;
        $datos = [
            "email" => $email,
            "enabled" => true,
            "admin" => false,
            "password" => $password,
        ];
        self::$client->request(
            Request::METHOD_PUT,
            apiUsersController::API_USERS . "/" . $id,
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
        self::assertEquals("Falta username", $datosRecibidos["message"]["message"]);
    }

    /**
     * Implements testPutUser404
     * @covers ::putUser
     * @covers ::error
     * @return void
     * @throws \Exception
     */
    public function testPutUser404(): void
    {
        $id = random_int(0, 10E6);
        self::$client->request(
            Request::METHOD_PUT,
            apiUsersController::API_USERS . "/" . $id
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
     * Implements testRemoveUser200
     * @depends testPostUser201
     * @covers ::removeUser
     * @param int $id
     */
    public function testRemoveUser200(int $id): void
    {
        self::$client->request(
            Request::METHOD_DELETE,
            apiUsersController::API_USERS . "/" . $id
        );

        /** @var Response $response */
        $response = self::$client->getResponse();

        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:self::assertEquals("", $response->getContent());
    }

    /**
     * Implements testRemoveUser404
     * @covers ::removeUser
     * @covers ::error
     * @return void
     * @throws \Exception
     */
    public function testRemoveUser404(): void
    {
        $id = random_int(0, 10E6);
        self::$client->request(
            Request::METHOD_DELETE,
            apiUsersController::API_USERS . "/" . $id
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
     * Implements testOptions
     * @covers ::options
     * @return void
     * @throws \Exception
     */
    public function testOptions(): void
    {
        self::$client->request(
            Request::METHOD_OPTIONS,
            apiUsersController::API_USERS
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
            apiUsersController::API_USERS . "/" . $id
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
