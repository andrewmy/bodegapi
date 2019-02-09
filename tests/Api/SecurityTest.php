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

class SecurityTest extends WebTestCase
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
        $this->cartItemRepo = self::$container->get(CartItemRepository::class);
        $this->productRepo = self::$container->get(ProductRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->client = null;
        $this->cartItemRepo = null;
        $this->productRepo = null;
    }

    public function testUserCanLogIn(): void
    {
        $client = self::logInApiClient(
            Users::API_LOGIN, Users::API_PASSWORD
        );
        $response = $client->getResponse()->getContent();

        $this->assertJson($response);
        $data = \json_decode($response, true);

        $this->assertNotEmpty($data['token']);
    }

    private function attemptCartItem(Client $client): void
    {
        $id = $this->cartItemRepo->findOneBy([])->getId();
        $this->request('GET', 'cart_items/'.$id, [], $client);
    }

    private function assertCartItemSuccess(Client $client): void
    {
        $this->attemptCartItem($client);

        $this->assertEquals(
            Response::HTTP_OK, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptRemoveFromCart(Client $client): void
    {
        $cartItem = $this->cartItemRepo->findOneBy([]);

        $this->request(
            'POST',
            'cart/remove',
            [
                'productId' => $cartItem->getProduct()->getId(),
                'quantity' => 1,
            ],
            $client
        );
    }

    private function assertRemoveFromCartSuccess(Client $client): void
    {
        $this->attemptRemoveFromCart($client);

        $this->assertEquals(
            Response::HTTP_CREATED, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptProductList(Client $client): void
    {
        $this->request('GET', 'products', [], $client);
    }

    private function assertProductListSuccess(Client $client): void
    {
        $this->attemptProductList($client);

        $this->assertEquals(
            Response::HTTP_OK, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptProductItem(Client $client): void
    {
        $id = $this->productRepo->findOneBy([])->getId();

        $this->request('GET', 'products/'.$id, [], $client);
    }

    private function assertProductItemSuccess(Client $client): void
    {
        $this->attemptProductItem($client);

        $this->assertEquals(
            Response::HTTP_OK, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptCreateProduct(Client $client): void
    {
        $this->request(
            'POST',
            'products',
            [
                'name' => 'Lego',
                'available' => 10,
                'price' => ['euros' => 123, 'cents' => 45],
                'vatRate' => 0.14,
            ],
            $client
        );
    }

    private function assertCreateProductSuccess(Client $client): void
    {
        $this->attemptCreateProduct($client);

        $this->assertEquals(
            Response::HTTP_CREATED, $client->getResponse()->getStatusCode()
        );
    }

    private function assertCreateProductFail(Client $client): void
    {
        $this->attemptCreateProduct($client);

        $this->assertEquals(
            Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptEditProduct(Client $client): void
    {
        $product = $this->productRepo->findOneBy([]);

        $this->request(
            'PUT',
            'products/'.$product->getId(),
            [
                'name' => 'Lego brick bucket',
                'available' => 10,
                'price' => ['euros' => 123, 'cents' => 45],
                'vatRate' => 0.14,
            ],
            $client
        );
    }

    private function assertEditProductSuccess(Client $client): void
    {
        $this->attemptEditProduct($client);

        $this->assertEquals(
            Response::HTTP_OK, $client->getResponse()->getStatusCode()
        );
    }

    private function assertEditProductFail(Client $client): void
    {
        $this->attemptEditProduct($client);

        $this->assertEquals(
            Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptDeleteProduct(Client $client): void
    {
        $product = $this->productRepo->findOneBy([]);

        $this->request('DELETE', 'products/'.$product->getId(), [], $client);
    }

    private function assertDeleteProductSuccess(Client $client): void
    {
        $this->attemptDeleteProduct($client);

        $this->assertEquals(
            Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode()
        );
    }

    private function assertDeleteProductFail(Client $client): void
    {
        $this->attemptDeleteProduct($client);

        $this->assertEquals(
            Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode()
        );
    }

    public function testApiUserIsLimited(): void
    {
        $this->assertProductListSuccess($this->client);
        $this->assertProductItemSuccess($this->client);

        $this->assertAddToCartSuccess($this->client);
        $this->assertViewCartSuccess($this->client);
        $this->assertCartItemSuccess($this->client);
        $this->assertRemoveFromCartSuccess($this->client);

        $this->assertCreateProductFail($this->client);
        $this->assertEditProductFail($this->client);
        $this->assertDeleteProductFail($this->client);
    }

    public function testAdminIsNotLimited(): void
    {
        $client = self::createApiClient(
            Users::ADMIN_LOGIN, Users::ADMIN_PASSWORD
        );

        $this->assertProductListSuccess($client);
        $this->assertProductItemSuccess($client);

        $this->assertAddToCartSuccess($client);
        $this->assertViewCartSuccess($client);
        $this->assertCartItemSuccess($client);
        $this->assertRemoveFromCartSuccess($client);

        $this->assertCreateProductSuccess($client);
        $this->assertEditProductSuccess($client);
        $this->assertDeleteProductSuccess($client);
    }
}
