<?php
/**
 * Created by PhpStorm.
 * User: Tomas
 * Date: 27/12/2018
 * Time: 0:14
 */

namespace App\Tests\Controller;

use App\Controller\ApiResultsController;
use App\Controller\ApiUsersController;
use App\Controller\ApiUsersResultsController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiUsersResultsControllerTest extends WebTestCase
{

    /** @var Client $client */
    private static $client;
    private static $user;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$user = static::createUser();
    }

    /**
     * Implements testGetAllResultsByUser404
     * @covers ::getAllResultsByUser
     * @covers ::error
     * @throws \Exception
     * @return void
     */
    public function testGetAllResultsByUser404(): void
    {
        $id = random_int(0, 10E6);
        self::$client->request(
            Request::METHOD_GET,
            apiUsersResultsController::API_USERS_RESULTS . '/' . $id . apiUsersResultsController::URL_RESULTS
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
        self::assertEquals("No existe usuario con ese id", $datosRecibidos["message"]["message"]);
    }

    /**
     * Implements testGetAllResultsByUser404
     * @covers ::getAllResultsByUser
     * @covers ::error
     * @throws \Exception
     * @return void
     */
    public function testGetAllResultsByUser404ResultsNotFound(): void
    {
        $id = self::$user['user']['id'];
        self::$client->request(
            Request::METHOD_GET,
            apiUsersResultsController::API_USERS_RESULTS . '/' . $id . apiUsersResultsController::URL_RESULTS
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
     * Implements testGetAllResultsByUser200
     * @covers ::getAllResultsByUser
     * @return void
     */
    public function testGetAllResultsOfUser200(): void
    {
        $id = self::$user['user']['id'];

        for ( $i = 0 ; $i < 10; $i++ ){
            self::createResults($id);
        }
        self::$client->request(
            Request::METHOD_GET,
            apiUsersResultsController::API_USERS_RESULTS . '/' . $id . apiUsersResultsController::URL_RESULTS
        );

        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('id', $datosRecibidos['results'][0]['result']);
        self::assertArrayHasKey('result', $datosRecibidos['results'][0]['result']);
        self::assertArrayHasKey('user', $datosRecibidos['results'][0]['result']);
    }

    /**
     * Implements testRemoveAllResultsByUser200
     * @covers ::removeAllResultsByUser
     */
    public function testRemoveAllResultsByUser200(): void
    {
        $id = self::$user['user']['id'];
        self::$client->request(
            Request::METHOD_DELETE,
            apiUsersResultsController::API_USERS_RESULTS . '/' . $id . apiUsersResultsController::URL_RESULTS
        );

        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:self::assertEquals("", $response->getContent());
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
     * Crear resultado usuario
     * @param int $userId
     * @throws
     */
    public static function createResults(int $userId)
    {
        $result = random_int(0, 32);
        $datos = [
            'user_id' => $userId,
            'result' => $result
        ];

        self::$client->request(
            Request::METHOD_POST,
            apiResultsController::API_RESULTS,
            [], [], [], json_encode($datos)
        );

        /** @var Response $response */
        $response = self::$client->getResponse();
        $result = json_decode($response->getContent(), true);
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
