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
     * Implements testGetcUser200
     * @covers ::getcUser
     */
    public function testGetcPersona200()
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
        self::assertArrayHasKey('users', $datosRecibidos);
    }

    /**
     *
     * @return int
     * @throws \Exception
     */
    public function testPostUser201(): int
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
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('User', $datosRecibidos);
        self::assertArrayHasKey('username', $datosRecibidos['User']);
        self::assertEquals($username, $datosRecibidos['User']['username']);

        return $datosRecibidos['User']['id'];
    }

    /**
     * @depends testPostUser201
     * @param int $id
     */
    public function testPostUser422(int $id)
    {
        $datos = [
            'id' => $id
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
        self::assertArrayHasKey('message', $datosRecibidos);
        self::assertArrayHasKey('code', $datosRecibidos['message']);
    }

    /**
     * Implements testGetUser200
     * @depends testPostUser201
     * @covers ::getUser
     * @param int $id
     */
    public function testGetUser200(int $id)
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('User', $datosRecibidos);
        self::assertArrayHasKey('id', $datosRecibidos['User']);
        self::assertEquals($id, $datosRecibidos['User']['id']);
    }

    /**
     * Implements testGetUser404
     * @depends testPostUser201
     * @param int $id
     * @covers ::getUser
     * @covers ::error
     */
    public function testGetUser404(int $id)
    {
        $id = $id + 100;
        self::$client->request(
            Request::METHOD_GET,
            ApiUsersController::API_USERS . '/' . $id
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
