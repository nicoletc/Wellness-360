<?php
/**
 * Profile Page Data
 * Contains placeholder data for profile page
 */

$placeholderImage = 'uploads/placeholder.jpg';
$placeholderAvatar = 'uploads/placeholder_avatar.jpg';

// User statistics (will be replaced with actual data from database)
$userStats = [
    'articlesRead' => 47,
    'ordersPlaced' => 12,
    'challengesCompleted' => 3,
    'favorites' => 28,
];

// User badges/achievements
$userBadges = [
    [
        'icon' => 'user',
        'label' => 'Wellness Enthusiast',
        'color' => 'primary',
    ],
    [
        'icon' => 'star',
        'label' => 'Level 3',
        'color' => 'default',
    ],
    [
        'icon' => 'trophy',
        'label' => '456 Points',
        'color' => 'default',
    ],
];

// Active Challenges
$activeChallenges = [
    [
        'id' => 1,
        'title' => '30-Day Hydration Challenge',
        'image' => $placeholderImage,
        'daysRemaining' => 16,
        'progress' => 45,
        'startDate' => 'Nov 1, 2025',
        'endDate' => 'Nov 30, 2025',
    ],
];

// Recommended Content (for Overview tab)
$recommendedContent = [
    [
        'type' => 'article',
        'category' => 'Mental Health',
        'title' => 'Managing Stress Through Mindfulness',
        'description' => 'Based on your mental health interests',
        'image' => $placeholderImage,
        'link' => '#',
    ],
    [
        'type' => 'product',
        'category' => 'Product',
        'title' => 'Resistance Bands Set',
        'price' => 55,
        'description' => 'Complements your yoga mat purchase',
        'image' => $placeholderImage,
        'link' => '#',
    ],
    [
        'type' => 'event',
        'category' => 'Event',
        'title' => 'Mental Health Awareness Workshop',
        'date' => 'Nov 15, 2025',
        'description' => 'Popular among users with similar interests',
        'image' => $placeholderImage,
        'link' => '#',
    ],
];

// Orders (placeholder)
$orders = [
    [
        'id' => 1,
        'orderNumber' => 'ORD-2025-001',
        'date' => 'Nov 10, 2025',
        'status' => 'Delivered',
        'total' => 125.00,
        'items' => 3,
    ],
    [
        'id' => 2,
        'orderNumber' => 'ORD-2025-002',
        'date' => 'Nov 5, 2025',
        'status' => 'Processing',
        'total' => 89.50,
        'items' => 2,
    ],
];

// Favorites (placeholder)
$favorites = [
    [
        'id' => 1,
        'type' => 'article',
        'title' => 'Understanding Mental Health in Ghana',
        'image' => $placeholderImage,
        'date' => 'Nov 8, 2025',
    ],
    [
        'id' => 2,
        'type' => 'product',
        'title' => 'Organic Raw Shea Butter',
        'image' => $placeholderImage,
        'price' => 45.00,
    ],
];

?>

