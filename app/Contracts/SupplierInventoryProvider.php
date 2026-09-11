<?php

declare(strict_types=1);

namespace App\Contracts;

interface SupplierInventoryProvider
{
    /** @param string[] $supplierSkus
     *  @return array<string, int> */
    public function getStock(array $supplierSkus): array;
}
