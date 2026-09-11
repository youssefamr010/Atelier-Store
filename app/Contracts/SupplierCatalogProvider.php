<?php

declare(strict_types=1);

namespace App\Contracts;

interface SupplierCatalogProvider
{
    /** @return array<int, array{supplier_product_id: string, title: string, price_minor: int, currency: string}> */
    public function search(string $query, array $filters = [], int $limit = 50): array;

    /** @return array{supplier_product_id: string, title: string, variants: array} */
    public function getProduct(string $supplierProductId): array;
}
