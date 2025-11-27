<?php
/**
 * Wellness Hub Page Data
 * Contains all data arrays for the wellness hub page
 */

$placeholderImage = '../uploads/placeholder.jpg';

// Categories for filtering
$categories = [
    'all' => 'All',
    'mental-health' => 'Mental Health',
    'nutrition' => 'Nutrition',
    'fitness' => 'Fitness',
    'lifestyle' => 'Lifestyle',
    'traditional' => 'Traditional Medicine',
];

// Content types for filtering
$contentTypes = [
    'all' => 'All Content',
    'articles' => 'Articles',
    'videos' => 'Videos',
];

// Featured Articles
$featuredArticles = [
    [
        'id' => 1,
        'title' => 'Understanding Mental Health in Ghana: Breaking the Stigma',
        'category' => 'mental-health',
        'contentType' => 'articles',
        'author' => 'Dr. Ama Mensah',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Mental health awareness is growing in Ghana. Learn about the importance of mental wellness and how to access support.',
        'readTime' => '8 min read',
        'date' => 'Nov 10, 2025',
        'views' => 1245,
        'likes' => 89,
        'featured' => true,
    ],
    [
        'id' => 2,
        'title' => 'Traditional Ghanaian Superfoods for Modern Wellness',
        'category' => 'nutrition',
        'contentType' => 'articles',
        'author' => 'Chef Kwame Asante',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Discover the nutritional power of traditional Ghanaian foods and how to incorporate them into your daily diet.',
        'readTime' => '6 min read',
        'date' => 'Nov 8, 2025',
        'views' => 987,
        'likes' => 76,
        'featured' => true,
    ],
];

// All Articles
$articles = [
    [
        'id' => 3,
        'title' => 'Managing Stress Through Mindfulness',
        'category' => 'mental-health',
        'contentType' => 'articles',
        'author' => 'Dr. Ama Mensah',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Practical mindfulness techniques to help you manage daily stress and improve your mental well-being.',
        'readTime' => '5 min read',
        'date' => 'Nov 5, 2025',
        'views' => 654,
        'likes' => 45,
        'featured' => false,
    ],
    [
        'id' => 4,
        'title' => 'Home Workouts for Busy Professionals',
        'category' => 'fitness',
        'contentType' => 'videos',
        'author' => 'Coach Nana Osei',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Effective workout routines you can do at home, perfect for busy schedules.',
        'readTime' => '6 min read',
        'date' => 'Nov 3, 2025',
        'views' => 823,
        'likes' => 67,
        'featured' => false,
    ],
    [
        'id' => 5,
        'title' => 'The Benefits of Shea Butter for Skin Health',
        'category' => 'lifestyle',
        'contentType' => 'articles',
        'author' => 'Dr. Efua Adjei',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Explore the natural healing properties of shea butter and how to use it for optimal skin health.',
        'readTime' => '4 min read',
        'date' => 'Nov 1, 2025',
        'views' => 542,
        'likes' => 38,
        'featured' => false,
    ],
    [
        'id' => 6,
        'title' => 'Herbal Remedies: Traditional Wisdom Meets Modern Science',
        'category' => 'traditional',
        'contentType' => 'articles',
        'author' => 'Dr. Kofi Boateng',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Learn about traditional Ghanaian herbal remedies and their scientifically proven benefits.',
        'readTime' => '7 min read',
        'date' => 'Oct 28, 2025',
        'views' => 1123,
        'likes' => 92,
        'featured' => false,
    ],
    [
        'id' => 7,
        'title' => 'Building Healthy Sleep Habits',
        'category' => 'lifestyle',
        'contentType' => 'articles',
        'author' => 'Dr. Ama Mensah',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Tips and strategies for improving your sleep quality and establishing a healthy sleep routine.',
        'readTime' => '5 min read',
        'date' => 'Oct 25, 2025',
        'views' => 789,
        'likes' => 56,
        'featured' => false,
    ],
    [
        'id' => 8,
        'title' => 'Plant-Based Nutrition: A Beginner\'s Guide',
        'category' => 'nutrition',
        'contentType' => 'articles',
        'author' => 'Chef Kwame Asante',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Discover how to transition to a plant-based diet while maintaining proper nutrition.',
        'readTime' => '9 min read',
        'date' => 'Oct 22, 2025',
        'views' => 456,
        'likes' => 34,
        'featured' => false,
    ],
    [
        'id' => 9,
        'title' => 'Yoga and Meditation for Stress Relief',
        'category' => 'fitness',
        'contentType' => 'videos',
        'author' => 'Coach Nana Osei',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'Simple yoga poses and meditation techniques to help reduce stress and improve flexibility.',
        'readTime' => '6 min read',
        'date' => 'Oct 20, 2025',
        'views' => 678,
        'likes' => 51,
        'featured' => false,
    ],
    [
        'id' => 10,
        'title' => 'Understanding Anxiety: Signs, Symptoms, and Support',
        'category' => 'mental-health',
        'contentType' => 'articles',
        'author' => 'Dr. Ama Mensah',
        'authorImage' => $placeholderImage,
        'image' => $placeholderImage,
        'excerpt' => 'A comprehensive guide to understanding anxiety and finding the right support resources.',
        'readTime' => '8 min read',
        'date' => 'Oct 18, 2025',
        'views' => 934,
        'likes' => 73,
        'featured' => false,
    ],
];

// Popular Topics/Tags
$popularTopics = [
    'Mental Health',
    'Nutrition',
    'Fitness',
    'Wellness',
    'Self-Care',
    'Traditional Medicine',
    'Healthy Living',
    'Mindfulness',
];

// Statistics
$stats = [
    'totalArticles' => 500,
    'totalAuthors' => 45,
    'totalViews' => 125000,
    'totalLikes' => 8900,
];
?>

