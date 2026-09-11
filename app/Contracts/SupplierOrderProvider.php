<?php

declare(strict_types=1);

namespace App\Contracts;

interface SupplierOrderProvider
{
    /** @return array{supplier_order_id: string, estimated_ship_date: ?string} */
    public function placeOrder(int $orderId, array $lineItems, array $shippingAddress): array;

    public function cancelOrder(string $supplierOrderId): bool;
}
