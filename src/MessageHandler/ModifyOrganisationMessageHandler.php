<?php

namespace App\MessageHandler;

use App\Entity\Organisation;
use App\Message\ModifyOrganisationMessage;
use App\Repository\CustomerRepository;
use App\Repository\OrganisationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ModifyOrganisationMessageHandler
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private CustomerRepository $customerRepository,
    ){
    }

    public function __invoke(ModifyOrganisationMessage $message): Organisation
    {
        $organisation = $this->organisationRepository->findOneBy(['id' => $message->id]);

        $organisation->getCustomers()->clear();

        foreach ($message->customerIds as $customerId) {
            $customer = $this->customerRepository->findOneBy(['id' => $customerId]);
            $organisation->addCustomer($customer);
        }

        return $organisation;
    }
}
