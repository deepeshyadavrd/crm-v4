<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Product extends WSController
{
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function search()
    {
        $term = trim(
            (string) $this->request->getGet('term')
        );

        $products = $this->productModel->searchProducts($term);

        return $this->response->setJSON($products);
    }
}