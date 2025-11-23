<?php
/**
 * Community Page Data
 * Contains all data arrays for the community page
 */

$placeholderImage = 'uploads/placeholder.jpg';

// Statistics
$stats = [
    'activeMembers' => 12456,
    'discussions' => 3892,
    'challenges' => 124,
    'events' => 48,
];

// Categories for discussions
$discussionCategories = [
    'all' => 'All',
    'mental-health' => 'Mental Health',
    'nutrition' => 'Nutrition',
    'fitness' => 'Fitness',
    'lifestyle' => 'Lifestyle',
    'traditional' => 'Traditional Medicine',
    'wellness' => 'Wellness',
];

// Discussions
$discussions = [
    [
        'id' => 1,
        'author' => 'Ama Kofi',
        'authorImage' => $placeholderImage,
        'timestamp' => '2 hours ago',
        'title' => 'What are your favorite morning wellness routines?',
        'content' => 'I\'ve been trying to establish a consistent morning routine. Would love to hear what works for you!',
        'category' => 'lifestyle',
        'likes' => 34,
        'replies' => 12,
    ],
    [
        'id' => 2,
        'author' => 'Kwame Mensah',
        'authorImage' => $placeholderImage,
        'timestamp' => '5 hours ago',
        'title' => 'Healthy Ghanaian recipes for weight management',
        'content' => 'Looking for traditional recipes that are both delicious and nutritious...',
        'category' => 'nutrition',
        'likes' => 56,
        'replies' => 23,
    ],
    [
        'id' => 3,
        'author' => 'Abena Osei',
        'authorImage' => $placeholderImage,
        'timestamp' => '1 day ago',
        'title' => 'Managing stress as a working parent in Accra',
        'content' => 'The traffic alone is stressful! How do you all manage work-life balance?',
        'category' => 'mental-health',
        'likes' => 78,
        'replies' => 31,
    ],
    [
        'id' => 4,
        'author' => 'Kofi Asante',
        'authorImage' => $placeholderImage,
        'timestamp' => '2 days ago',
        'title' => 'Best exercises for home workouts in Ghana',
        'content' => 'Can\'t always make it to the gym. What equipment-free exercises do you recommend?',
        'category' => 'fitness',
        'likes' => 45,
        'replies' => 18,
    ],
    [
        'id' => 5,
        'author' => 'Efua Mensah',
        'authorImage' => $placeholderImage,
        'timestamp' => '3 days ago',
        'title' => 'Traditional herbs for immune support',
        'content' => 'My grandmother always used local herbs. Anyone have experience with natural immune boosters?',
        'category' => 'traditional',
        'likes' => 92,
        'replies' => 42,
    ],
];

// Challenges
$challenges = [
    [
        'id' => 1,
        'title' => '30-Day Hydration Challenge',
        'description' => 'Drink 8 glasses of water daily for 30 days',
        'image' => $placeholderImage,
        'participants' => 1247,
        'startDate' => 'Nov 1, 2025',
        'endDate' => 'Nov 30, 2025',
        'status' => 'active',
        'userProgress' => 45, // User's progress percentage if participating
        'isParticipating' => true,
    ],
    [
        'id' => 2,
        'title' => 'Morning Meditation Journey',
        'description' => '10 minutes of meditation every morning',
        'image' => $placeholderImage,
        'participants' => 892,
        'startDate' => 'Nov 8, 2025',
        'endDate' => 'Dec 8, 2025',
        'status' => 'active',
        'userProgress' => null,
        'isParticipating' => false,
    ],
    [
        'id' => 3,
        'title' => 'Fitness November',
        'description' => '20 minutes of exercise, 5 days a week',
        'image' => $placeholderImage,
        'participants' => 2341,
        'startDate' => 'Nov 1, 2025',
        'endDate' => 'Nov 30, 2025',
        'status' => 'active',
        'userProgress' => 60,
        'isParticipating' => true,
    ],
];

// Workshops/Events
$workshops = [
    [
        'id' => 1,
        'title' => 'Mental Health Awareness Workshop',
        'description' => 'Learn to identify and manage anxiety, stress, and depression with evidence-based techniques.',
        'image' => $placeholderImage,
        'type' => 'virtual',
        'host' => 'Dr. Ama Mensah',
        'date' => 'Nov 15, 2025',
        'time' => '10:00 AM - 12:00 PM',
        'location' => null,
        'registered' => 124,
        'capacity' => 200,
    ],
    [
        'id' => 2,
        'title' => 'Nutrition & Wellness Cooking Class',
        'description' => 'Cook healthy, delicious Ghanaian meals that support your wellness goals.',
        'image' => $placeholderImage,
        'type' => 'in-person',
        'host' => 'Chef Kwame Asante',
        'date' => 'Nov 18, 2025',
        'time' => '3:00 PM - 5:00 PM',
        'location' => 'Accra Central',
        'registered' => 45,
        'capacity' => 50,
    ],
    [
        'id' => 3,
        'title' => 'Yoga & Mindfulness Session',
        'description' => 'Start your day with gentle yoga and guided meditation for all levels.',
        'image' => $placeholderImage,
        'type' => 'virtual',
        'host' => 'Akua Boateng',
        'date' => 'Nov 20, 2025',
        'time' => '6:00 AM - 7:00 AM',
        'location' => null,
        'registered' => 89,
        'capacity' => 150,
    ],
];

?>

