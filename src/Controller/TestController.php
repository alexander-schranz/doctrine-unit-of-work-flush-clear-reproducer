<?php

declare(strict_types=1);

namespace App\Controller;

use App\Message\ModifyOrganisationMessage;
use Sulu\Messenger\Infrastructure\Symfony\Messenger\FlushMiddleware\EnableFlushStamp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class TestController
{
    use HandleTrait;

    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[Route('/test/{id}')]
    public function index(Request $request, int $id): Response
    {
        $customerIds = $request->query->all('customerIds');

        $this->handle(new Envelope(
            new ModifyOrganisationMessage($id, $customerIds),
            [new EnableFlushStamp()],
        ));

        return new Response('OK');
    }
}
