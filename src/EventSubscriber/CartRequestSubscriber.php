<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\CartItem;
use App\Entity\Request\Cart\AddToCartRequest;
use App\Entity\Request\Cart\ViewCartRequest;
use App\EventSubscriber\Traits\HasUser;
use App\Repository\ProductRepository;
use App\ValueObject\UserCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CartRequestSubscriber implements EventSubscriberInterface
{
    use HasUser;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProductRepository */
    private $productRepo;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepo,
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer
    ) {
        $this->entityManager = $entityManager;
        $this->productRepo = $productRepo;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['addToCart', EventPriorities::POST_VALIDATE],
                ['removeFromCart', EventPriorities::POST_VALIDATE],
                ['viewCart', EventPriorities::POST_VALIDATE],
            ],
        ];
    }

    public function addToCart(GetResponseForControllerResultEvent $event): void
    {
        $request = $event->getRequest();
        if ('api_add_to_cart_requests_post_collection' !== $request->attributes->get('_route')) {
            return;
        }

        /** @var AddToCartRequest $data */
        $data = $event->getControllerResult();

        $user = $this->getUser();
        if (null === $user) {
            throw new \RuntimeException('No user');
        }

        $product = $this->productRepo->find($data->productId);
        if (null === $product) {
            throw new InvalidValueException('Invalid product');
        }

        if ($data->quantity > $product->getAvailable()) {
            throw new InvalidValueException('Not enough stock');
        }

        $cart = new UserCart($user);
        for ($i = 0; $i < $data->quantity; ++$i) {
            // well...
            $cart->addProduct($product);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $cartItem = $user->getCartItemByProduct($product);

        $data->cartItem = $cartItem;
        $event->setResponse(
            new JsonResponse(
                $this->normalizer->normalize($data), Response::HTTP_CREATED
            )
        );
    }

    public function removeFromCart(GetResponseForControllerResultEvent $event): void
    {
        $request = $event->getRequest();
        if ('api_remove_from_cart_requests_post_collection' !== $request->attributes->get('_route')) {
            return;
        }

        /** @var AddToCartRequest $data */
        $data = $event->getControllerResult();

        $user = $this->getUser();
        if (null === $user) {
            throw new \RuntimeException('No user');
        }

        $product = $this->productRepo->find($data->productId);
        if (null === $product) {
            throw new InvalidValueException('Invalid product');
        }

        $cart = new UserCart($user);
        for ($i = 0; $i < $data->quantity; ++$i) {
            // well...
            $cart->removeProduct($product);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $cartItem = $user->getCartItemByProduct($product);
        if (null === $cartItem) {
            $cartItem = (new CartItem())->setProduct($product)->setQuantity(0);
        }

        $data->cartItem = $cartItem;

        $event->setResponse(
            new JsonResponse(
                $this->normalizer->normalize($data), Response::HTTP_CREATED
            )
        );
    }

    public function viewCart(GetResponseForControllerResultEvent $event): void
    {
        $request = $event->getRequest();
        if ('api_view_cart_requests_get_collection' !== $request->attributes->get('_route')) {
            return;
        }

        $user = $this->getUser();
        if (null === $user) {
            throw new \RuntimeException('No user');
        }

        $cart = new UserCart($user);

        $data = new ViewCartRequest();
        // otherwise we see an array or IRIs
        $data->items = \array_map(function (CartItem $item) {
            return $this->normalizer->normalize($item);
        }, $cart->getProducts());

        $subtotal = $this->normalizer->normalize($cart->getSubtotal());
        \assert(\is_array($subtotal));
        $data->subTotal = $subtotal;

        $vat = $this->normalizer->normalize($cart->getVatAmount());
        \assert(\is_array($vat));
        $data->vatAmount = $vat;

        $total = $this->normalizer->normalize($cart->getTotal());
        \assert(\is_array($total));
        $data->total = $total;

        $event->setResponse(new JsonResponse($data, Response::HTTP_OK));
    }
}
