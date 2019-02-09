<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\EventSubscriber\Traits\HasUser;
use App\Filter\UserFilter;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserFilterSubscriber implements EventSubscriberInterface
{
    use HasUser;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Reader */
    private $annotationReader;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        Reader $reader
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->annotationReader = $reader;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 7]], // after firewall (8)
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$this->isApiRequest($event->getRequest())) {
            return;
        }

        $user = $this->getUser();
        if (null === $user) {
            throw new \RuntimeException('No authenticated user');
        }

        /** @var UserFilter $filter */
        $filter = $this->entityManager->getFilters()
            ->enable('user_filter');
        $filter
            ->setAnnotationReader($this->annotationReader)
            ->setEntityManager($this->entityManager)
            ->setParameter('ids', (string) $user->getId());
    }

    private function isApiRequest(Request $request): bool
    {
        return 0
            === \mb_strpos((string) $request->attributes->get('_route'), 'api_');
    }
}
