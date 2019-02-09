<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\Users;
use App\Tests\ApiAwareTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentationDecoratorTest extends WebTestCase
{
    use ApiAwareTestTrait;

    public function testAddsInfo(): void
    {
        $client = self::createApiClient(Users::API_LOGIN, Users::API_PASSWORD);

        $this->request('GET', 'docs.json', [], $client);
        $this->assertJson($client->getResponse()->getContent());
        $data = \json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('/api/login', $data['paths']);
        $this->assertSame('Bodegapi', $data['info']['title']);
        $this->assertSame('0.0.1', $data['info']['version']);
    }
}
