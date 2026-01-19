<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs de categorías
        $nichoId = Category::where('slug', 'perfumes-nicho')->first()->id;
        $disenadorId = Category::where('slug', 'perfumes-disenador')->first()->id;
        $arabeId = Category::where('slug', 'perfumes-arabes')->first()->id;

        $products = [
            // PERFUMES NICHO (más caros)
            [
                'category_id' => $nichoId,
                'name' => 'Creed Aventus',
                'slug' => 'creed-aventus',
                'description' => 'Una fragancia masculina icónica con notas de piña, bergamota, pachulí y vainilla. Perfecta para el hombre seguro y exitoso.',
                'price' => 450.00,
                'stock' => 15,
                'brand' => 'Creed',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $nichoId,
                'name' => 'Tom Ford Oud Wood',
                'slug' => 'tom-ford-oud-wood',
                'description' => 'Fragancia unisex exótica con oud, sándalo y especias orientales. Elegancia y sofisticación en un frasco.',
                'price' => 380.00,
                'stock' => 12,
                'brand' => 'Tom Ford',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $nichoId,
                'name' => 'Byredo Gypsy Water',
                'slug' => 'byredo-gypsy-water',
                'description' => 'Fragancia unisex fresca y amaderada. Notas de bergamota, enebro, pino y vainilla.',
                'price' => 420.00,
                'stock' => 8,
                'brand' => 'Byredo',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $nichoId,
                'name' => 'Maison Francis Kurkdjian Baccarat Rouge 540',
                'slug' => 'mfk-baccarat-rouge-540',
                'description' => 'Fragancia luminosa y sensual con ámbar, azafrán y cedro. Una obra maestra de la perfumería moderna.',
                'price' => 495.00,
                'stock' => 10,
                'brand' => 'Maison Francis Kurkdjian',
                'size' => 70,
                'featured' => true,
            ],
            [
                'category_id' => $nichoId,
                'name' => 'Le Labo Santal 33',
                'slug' => 'le-labo-santal-33',
                'description' => 'Fragancia unisex con sándalo australiano, cedro y cardamomo. Culto urbano moderno.',
                'price' => 405.00,
                'stock' => 14,
                'brand' => 'Le Labo',
                'size' => 100,
                'featured' => false,
            ],

            // PERFUMES DE DISEÑADOR (precio medio)
            [
                'category_id' => $disenadorId,
                'name' => 'Dior Sauvage',
                'slug' => 'dior-sauvage',
                'description' => 'Fragancia masculina fresca y picante con bergamota, pimienta y ámbar gris. Un clásico moderno.',
                'price' => 180.00,
                'stock' => 35,
                'brand' => 'Dior',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $disenadorId,
                'name' => 'Chanel Coco Mademoiselle',
                'slug' => 'chanel-coco-mademoiselle',
                'description' => 'Fragancia femenina elegante con naranja, rosa, pachulí y vainilla. Sofisticación parisina.',
                'price' => 195.00,
                'stock' => 28,
                'brand' => 'Chanel',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $disenadorId,
                'name' => 'Yves Saint Laurent La Nuit de Homme',
                'slug' => 'ysl-la-nuit-de-lhomme',
                'description' => 'Fragancia masculina seductora con cardamomo, cedro y vetiver. Para el hombre nocturno.',
                'price' => 165.00,
                'stock' => 22,
                'brand' => 'Yves Saint Laurent',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $disenadorId,
                'name' => 'Paco Rabanne 1 Million',
                'slug' => 'paco-rabanne-1-million',
                'description' => 'Fragancia masculina dulce y especiada con canela, menta y cuero. Audaz y llamativa.',
                'price' => 155.00,
                'stock' => 40,
                'brand' => 'Paco Rabanne',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $disenadorId,
                'name' => 'Giorgio Armani Acqua di Gio',
                'slug' => 'armani-acqua-di-gio',
                'description' => 'Fragancia masculina acuática fresca con notas marinas, bergamota y pachulí.',
                'price' => 145.00,
                'stock' => 45,
                'brand' => 'Giorgio Armani',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $disenadorId,
                'name' => 'Viktor & Rolf Flowerbomb',
                'slug' => 'viktor-rolf-flowerbomb',
                'description' => 'Fragancia femenina floral explosiva con jazmín, rosa, orquídea y pachulí.',
                'price' => 175.00,
                'stock' => 30,
                'brand' => 'Viktor & Rolf',
                'size' => 100,
                'featured' => true,
            ],

            // PERFUMES ÁRABES (más económicos)
            [
                'category_id' => $arabeId,
                'name' => 'Lattafa Asad',
                'slug' => 'lattafa-asad',
                'description' => 'Fragancia oriental intensa con oud, especias y ámbar. Masculina y poderosa.',
                'price' => 85.00,
                'stock' => 50,
                'brand' => 'Lattafa',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $arabeId,
                'name' => 'Ajmal Amber Wood',
                'slug' => 'ajmal-amber-wood',
                'description' => 'Fragancia unisex cálida con ámbar, cedro y almizcle. Oriental clásica.',
                'price' => 75.00,
                'stock' => 60,
                'brand' => 'Ajmal',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $arabeId,
                'name' => 'Al Haramain Aventure',
                'slug' => 'al-haramain-laventure',
                'description' => 'Fragancia masculina fresca y amaderada, alternativa económica a Creed Aventus.',
                'price' => 65.00,
                'stock' => 70,
                'brand' => 'Al Haramain',
                'size' => 100,
                'featured' => true,
            ],
            [
                'category_id' => $arabeId,
                'name' => 'Rasasi Hawas',
                'slug' => 'rasasi-hawas',
                'description' => 'Fragancia masculina acuática fresca con manzana, bergamota y almizcle.',
                'price' => 70.00,
                'stock' => 55,
                'brand' => 'Rasasi',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $arabeId,
                'name' => 'Afnan 9 PM',
                'slug' => 'afnan-9pm',
                'description' => 'Fragancia masculina dulce y especiada con manzana, canela y vainilla.',
                'price' => 60.00,
                'stock' => 65,
                'brand' => 'Afnan',
                'size' => 100,
                'featured' => false,
            ],
            [
                'category_id' => $arabeId,
                'name' => 'Armaf Club de Nuit Intense Man',
                'slug' => 'armaf-club-de-nuit-intense',
                'description' => 'Fragancia masculina intensa con frutas, especias y almizcle. Excelente rendimiento.',
                'price' => 80.00,
                'stock' => 48,
                'brand' => 'Armaf',
                'size' => 105,
                'featured' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
