<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Organisation;
use App\Event\OrganisationChangedEvent;
use App\Message\ModifyOrganisationMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sulu\Messenger\Infrastructure\Symfony\Messenger\FlushMiddleware\EnableFlushStamp;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[AsDoctrineListener(event: Events::postFlush)]
#[AsEventListener(event: OrganisationChangedEvent::class, method: 'onOrganisationChanged')]
class TestController
{
    /**
     * @var array<object>
     */
    private array $events;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route('/')]
    public function index(Request $request): Response
    {
        $organisation = $this->entityManager->getRepository(Organisation::class)->find(1);
        if (!$organisation) {
            throw new NotFoundHttpException('Make sure the load the fixtures first');
        }

        $organisation->getCustomers()->clear(); // UnitOfWork tracks a delete query on clear

        $customerA = $this->entityManager->getRepository(Customer::class)->find(1);
        $customerB = $this->entityManager->getRepository(Customer::class)->find(2);

        $organisation->getCustomers()->add($customerA);
        $organisation->getCustomers()->add($customerB);

        // for simplicity doing this here normally done by an own services
        $this->events[] = new OrganisationChangedEvent();

        $this->entityManager->flush();

        // refetch organisation
        $this->entityManager->clear();
        $organisation = $this->entityManager->getRepository(Organisation::class)->find(1);

        return new Response(
            \count($organisation->getCustomers()) === 2
                ? 'OK: two customers added'
                : 'FAIL: second flush triggered clear query again'
        );
    }

    public function postFlush(): void
    {
        $events = $this->events;
        $this->events = [];

        // in our case events should only be triggered after the flush where we are sure that the data is persisted
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    public function onOrganisationChanged(OrganisationChangedEvent $event): void
    {
        // do something else, updating external services, read models and so on

        $this->entityManager->flush(); // FIXME this triggers the clear query again
    }
}
