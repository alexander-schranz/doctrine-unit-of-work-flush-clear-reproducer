<?php

namespace App\Message;

final class ModifyOrganisationMessage
{
    public function __construct(
         public int $id,
         public array $customerIds,
    ) {}
}
