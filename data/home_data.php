<?php
/**
 * Home Page Data
 * Contains all data arrays for the home page
 */

// Define image paths
$heroImage = '../uploads/index.png';
$placeholderImage = '../uploads/placeholder.jpg';

// Features data array
$features = [
    [
        'icon' => 'sparkles',
        'title' => 'Verified Content',
        'description' => 'Evidence-based wellness articles reviewed by health professionals',
    ],
    [
        'icon' => 'shield',
        'title' => 'Trusted Products',
        'description' => 'Curated marketplace with verified vendors and authentic reviews',
    ],
    [
        'icon' => 'users',
        'title' => 'Community Support',
        'description' => 'Connect with others on similar wellness journeys across Ghana',
    ],
];

// Wellness Tips data array
$wellnessTips = [
    [
        'title' => 'Managing Stress Through Mindfulness',
        'category' => 'Mental Health',
        'author' => 'Dr. Ama Mensah',
        'image' => $placeholderImage,
        'readTime' => '5 min read',
    ],
    [
        'title' => 'Traditional Ghanaian Foods for Wellness',
        'category' => 'Nutrition',
        'author' => 'Chef Kwame Asante',
        'image' => $placeholderImage,
        'readTime' => '7 min read',
    ],
    [
        'title' => 'Home Workouts for Busy Professionals',
        'category' => 'Fitness',
        'author' => 'Coach Nana Osei',
        'image' => $placeholderImage,
        'readTime' => '6 min read',
    ],
];

// Products data array
$products = [
    [
        'name' => 'Organic Shea Butter',
        'vendor' => 'Natural Ghana',
        'price' => '₵45',
        'rating' => 4.8,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'name' => 'Herbal Tea Collection',
        'vendor' => 'Wellness Herbs GH',
        'price' => '₵35',
        'rating' => 4.9,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'name' => 'Yoga Mat & Accessories',
        'vendor' => 'FitLife Ghana',
        'price' => '₵120',
        'rating' => 4.7,
        'image' => $placeholderImage,
        'verified' => true,
    ],
    [
        'name' => 'Mindfulness Journal',
        'vendor' => 'Peaceful Minds',
        'price' => '₵25',
        'rating' => 4.6,
        'image' => $placeholderImage,
        'verified' => true,
    ],
];

// Events data array
$events = [
    [
        'title' => 'Mental Health Awareness Workshop',
        'date' => 'Nov 15, 2025',
        'time' => '10:00 AM',
        'type' => 'Virtual',
        'attendees' => 124,
    ],
    [
        'title' => 'Nutrition & Wellness Cooking Class',
        'date' => 'Nov 18, 2025',
        'time' => '3:00 PM',
        'type' => 'In-Person',
        'attendees' => 45,
    ],
    [
        'title' => '30-Day Fitness Challenge Kickoff',
        'date' => 'Nov 20, 2025',
        'time' => '6:00 AM',
        'type' => 'Virtual',
        'attendees' => 289,
    ],
];
?>

