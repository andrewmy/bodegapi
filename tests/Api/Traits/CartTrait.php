<?php

declare(strict_types=1);

namespace App\Tests\Api\Traits;

use App\Repository\ProductRepository;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property ProductRepository $productRepo
 *
 * @method Client|null request(string $method, string $endpoint, array $data = [], ?Client $client = null, $server = [])
 */
trait CartTrait
{
    private function attemptViewCart(Client $client): void
    {
        $this->request('GET', 'cart', [], $client);
    }

    private function assertViewCartSuccess(Client $client): void
    {
        $this->attemptViewCart($client);

        $this->assertEquals(
            Response::HTTP_OK, $client->getResponse()->getStatusCode()
        );
    }

    private function attemptAddToCart(Client $client): void
    {
        $id = $this->productRepo->findOneBy([])->getId();

        $this->request(
            'POST',
            'cart/add',
            [
                'productId' => $id,
                'quantity' => 1,
            ],
            $client
        );
    }

    private function assertAddToCartSuccess(Client $client): void
    {
        $this->attemptAddToCart($client);

        $this->assertEquals(
            Response::HTTP_CREATED, $client->getResponse()->getStatusCode()
        );
    }
}
