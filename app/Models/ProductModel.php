<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'oc_product';
    protected $primaryKey = 'product_id';
    protected $returnType = 'array';

    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
    }

    public function searchProducts(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $words = preg_split('/\s+/', $term);

        $builder = $this->db->table('oc_product p');

        $builder->select([
            'p.product_id',
            'pd.name',
            'p.model',
            'p.image',
            'p.price AS original_price',
            'ps.price AS special_price'
        ]);

        $builder->join(
            'oc_product_description pd',
            'pd.product_id = p.product_id',
            'left'
        );

        $builder->join(
            'oc_product_special ps',
            'ps.product_id = p.product_id',
            'left'
        );

        $builder->where('p.status', 1);

        $builder->groupStart();

        foreach ($words as $word) {

            if ($word !== '') {

                $builder->groupStart();

                $builder->like('pd.name', $word);
                $builder->orLike('p.model', $word);

                $builder->groupEnd();
            }
        }

        $builder->groupEnd();

        $builder->notLike('pd.name', 'test');
        $builder->notLike('pd.name', 'custom');

        $escapedTerm = $this->db->escape($term);

        $builder->orderBy(
            "CASE
                WHEN pd.name LIKE CONCAT('%', {$escapedTerm}, '%') THEN 1
                ELSE 2
            END",
            'ASC',
            false
        );

        $builder->limit(10);

        $products = $builder
            ->get()
            ->getResultArray();

        $imageBase = 'https://www.urbanwood.in/';

        $result = [];

        foreach ($products as $row) {

            $result[] = [
                'id' => (int) $row['product_id'],
                'name' => $row['name'],
                'model' => $row['model'],

                'original_price' => number_format(
                    (float) $row['original_price'],
                    2,
                    '.',
                    ''
                ),

                'special_price' => !empty($row['special_price'])
                    ? number_format(
                        (float) $row['special_price'],
                        2,
                        '.',
                        ''
                    )
                    : null,

                'image' => !empty($row['image'])
                    ? $imageBase . 'image/' . $row['image']
                    : $imageBase . 'image/no_image.png'
            ];
        }

        return $result;
    }
}