<?php

declare(strict_types=1);

namespace OpenDialogAi\Core\Contracts;

interface DataTransferObjectInterface
{
    public function toArray(): array;
}
