<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method static Kernel bootKernel()
 * @method static Client createClient()
 */
trait ApiAwareTestTrait
{
    protected static function logInApiClient(
        string $username, string $password
    ): Client {
        /** @var Client $client */
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        return $client;
    }

    protected static function createApiClient(
        string $username, string $password
    ): Client {
        if (null === self::$kernel) {
            self::bootKernel();
        }

        $response = null;
        if ('' !== $username && '' !== $password) {
            /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
            $client = self::logInApiClient($username, $password);
            $response = \json_decode($client->getResponse()->getContent(), true);
        }

        $client = static::createClient();
        if (isset($response['token'])) {
            $client->setServerParameter(
                'HTTP_Authorization',
                \sprintf('Bearer %s', $response['token'])
            );
        }
        $client->setServerParameter('HTTP_ACCEPT', 'application/json');
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    protected function request(
         string $method,
         string $endpoint,
         array $data = [],
         ?Client $client = null,
         $server = []
    ) {
        if (null === $client) {
            $client = static::createApiClient('', '');
        }

        $server = \array_merge([
            'HTTP_ACCEPT_LANGUAGE' => 'en-US',
        ], $server);

        $client->request(
            $method,
            '/api/'.$endpoint,
            Request::METHOD_POST === $method ? [] : $data,
            [],
            $server,
            \in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])
                ? \json_encode($data)
                : null
        );

        return $client;
    }
}
