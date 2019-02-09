<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\Users;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use App\Tests\Api\Traits\CartTrait;
use App\Tests\ApiAwareTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Response;

class CartTest extends WebTestCase
{
    use ApiAwareTestTrait, CartTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Client */
    private $client;

    /** @var CartItemRepository */
    private $cartItemRepo;

    /** @var ProductRepository */
    private $productRepo;

    protected function setUp(): void
    {
        $this->client = self::createApiClient(
            Users::API_LOGIN, Users::API_PASSWORD
        );

        $this->entityManager = self::$container->get(EntityManagerInterface::class);
        $this->userPasswordEncoder = self::$container->get(
            'security.user_password_encoder.generic'
        );
        $this->cartItemRepo = self::$container->get(CartItemRepository::class);
        $this->productRepo = self::$container->get(ProductRepository::class);
    }

    public function testCartIsUserAware(): void
    {
        $this->assertViewCartSuccess($this->client);
        $this->assertCount(
            0,
            \json_decode($this->client->getResponse()->getContent(), true)['items']
        );

        $this->assertAddToCartSuccess($this->client);
        $this->assertViewCartSuccess($this->client);
        $this->assertCount(
            1,
            \json_decode($this->client->getResponse()->getContent(), true)['items']
        );

        $client = self::createApiClient(
            Users::ADMIN_LOGIN, Users::ADMIN_PASSWORD
        );
        $this->assertViewCartSuccess($client);
        $this->assertCount(
            0, \json_decode($client->getResponse()->getContent(), true)['items']
        );

        $this->assertAddToCartSuccess($client);
        $this->assertViewCartSuccess($client);
        $this->assertCount(
            1, \json_decode($client->getResponse()->getContent(), true)['items']
        );
    }

    public function testInvalidCartRequests(): void
    {
        $this->request(
            'POST',
            'cart/add',
            [
                'productId' => 'none',
                'quantity' => 1,
            ],
            $this->client
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );

        $product = $this->productRepo->findOneBy([]);
        $this->request(
            'POST',
            'cart/add',
            [
                'productId' => $product->getId(),
                'quantity' => $product->getAvailable() + 1,
            ],
            $this->client
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );

        $this->request(
            'POST',
            'cart/remove',
            [
                'productId' => 'none',
                'quantity' => 1,
            ],
            $this->client
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
