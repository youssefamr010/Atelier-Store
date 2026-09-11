<?php

declare(strict_types=1);

namespace App\Contracts;

interface SupplierShipmentProvider
{
    /** @return array{tracking_number: ?string, carrier: ?string, status: string, events: array} */
    public function getShipmentStatus(string $supplierOrderId): array;
}
