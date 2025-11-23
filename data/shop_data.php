<?php
/**
 * Shop Page Data
 * Contains all data arrays for the shop page
 */

$placeholderImage = 'uploads/placeholder.jpg';

// Categories for filtering
$categories = [
    'all' => 'All Products',
    'natural-remedies' => 'Natural Remedies',
    'nutrition-supplements' => 'Nutrition & Supplements',
    'fitness-equipment' => 'Fitness Equipment',
    'self-care' => 'Self-Care',
    'books-journals' => 'Books & Journals',
];

// Products
$products = [
    [
        'id' => 1,
        'name' => 'Organic Raw Shea Butter',
        'vendor' => 'Natural Ghana',
        'category' => 'self-care',
        'price' => 45,
        'rating' => 5.0,
        'reviews' => 234,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 2,
        'name' => 'Herbal Tea Collection (5-Pack)',
        'vendor' => 'Wellness Herbs GH',
        'category' => 'natural-remedies',
        'price' => 35,
        'rating' => 5.0,
        'reviews' => 189,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 3,
        'name' => 'Premium Yoga Mat & Strap Set',
        'vendor' => 'FitLife Ghana',
        'category' => 'fitness-equipment',
        'price' => 120,
        'rating' => 5.0,
        'reviews' => 98,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 4,
        'name' => 'Vitamin D3 Supplements',
        'vendor' => 'Health Plus GH',
        'category' => 'nutrition-supplements',
        'price' => 55,
        'rating' => 4.8,
        'reviews' => 156,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 5,
        'name' => 'Wellness Journal & Planner',
        'vendor' => 'Peaceful Minds',
        'category' => 'books-journals',
        'price' => 25,
        'rating' => 4.9,
        'reviews' => 87,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 6,
        'name' => 'Essential Oil Diffuser Set',
        'vendor' => 'Natural Ghana',
        'category' => 'self-care',
        'price' => 85,
        'rating' => 4.7,
        'reviews' => 142,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 7,
        'name' => 'Traditional Herbal Remedies Pack',
        'vendor' => 'Wellness Herbs GH',
        'category' => 'natural-remedies',
        'price' => 60,
        'rating' => 4.9,
        'reviews' => 201,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'id' => 8,
        'name' => 'Resistance Bands Set',
        'vendor' => 'FitLife Ghana',
        'category' => 'fitness-equipment',
        'price' => 40,
        'rating' => 4.6,
        'reviews' => 112,
        'image' => $placeholderImage,
        'verified' => true,
    ],
];

// Price range
$priceRange = [
    'min' => 0,
    'max' => 500,
];
?>

