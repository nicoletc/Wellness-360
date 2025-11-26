# Daily Reminders System - Implementation Guide

## What We've Implemented

### 1. **Category-Based Daily Reminders**
- Daily reminders are now personalized based on the user's **top interest categories**
- The system analyzes user activity (time spent, page views) to determine their most engaged categories
- Reminders include motivational quotes matched to the user's interests

### 2. **How It Works**

#### Step 1: Activity Tracking
- As users browse articles, products, and pages, their activity is automatically tracked
- Time spent on pages and categories is recorded in the `user_activity` table
- Interest scores are calculated and stored in `user_interests` table

#### Step 2: Interest Calculation
- The system calculates interest scores based on:
  - Number of views in each category
  - Time spent on category-related content
  - Recent activity (last 7 days weighted more)

#### Step 3: Daily Reminder Generation
- Each day, when a logged-in user visits the homepage:
  1. System checks if a reminder exists for today
  2. If not, it generates a new one based on their **top interest category**
  3. Selects a motivational quote relevant to that category
  4. Adds category-specific encouragement message
  5. Displays the reminder in a banner at the top of the page

### 3. **Files Created/Modified**

#### New Files:
- `Actions/mark_reminder_read_action.php` - Marks reminders as read

#### Modified Files:
- `Classes/ReminderModel.php` - Enhanced to match user interests to category-based quotes
- `data/motivational_quotes.sql` - Updated with category mappings (needs category IDs)
- `index.php` - Added daily reminder banner display
- `Css/style.css` - Added styles for reminder banner

## How to Test

### Step 1: Run Database Migrations
```sql
-- Run this first to create tables
SOURCE settings/user_activity_tracking_migration.sql;

-- Then seed motivational quotes
-- IMPORTANT: Update category IDs in data/motivational_quotes.sql to match your actual category IDs
-- Check your category table: SELECT cat_id, cat_name FROM category;
-- Then update the INSERT statements in motivational_quotes.sql with correct IDs
SOURCE data/motivational_quotes.sql;
```

### Step 2: Update Category IDs in Quotes
Before running `motivational_quotes.sql`, check your category table:
```sql
SELECT cat_id, cat_name FROM category;
```

Then update the category IDs in `data/motivational_quotes.sql`:
- Mental Health quotes → Use the cat_id for "Mental Health" category
- Nutrition quotes → Use the cat_id for "Nutrition" category  
- Fitness quotes → Use the cat_id for "Fitness" category
- General quotes → Keep as NULL

### Step 3: Generate User Activity
1. **Log in** as a user
2. **Browse content** in different categories:
   - Read articles in "Mental Health" category
   - View products in "Nutrition" category
   - Spend time on "Fitness" related pages
3. **Wait a few minutes** or browse multiple pages to build up activity data

### Step 4: View Daily Reminder
1. **Visit the homepage** (`index.php`)
2. **Look for the reminder banner** at the top (below the header)
3. The reminder should show:
   - A title like "Daily Mental Health Reminder" (based on your top interest)
   - A motivational quote relevant to that category
   - Category-specific encouragement message

### Step 5: Verify It's Working
Check the database:
```sql
-- See user's interests
SELECT ui.*, c.cat_name 
FROM user_interests ui
JOIN category c ON ui.category_id = c.cat_id
WHERE ui.customer_id = YOUR_USER_ID
ORDER BY ui.interest_score DESC;

-- See daily reminders
SELECT dr.*, c.cat_name
FROM daily_reminders dr
LEFT JOIN category c ON dr.category_id = c.cat_id
WHERE dr.customer_id = YOUR_USER_ID
ORDER BY dr.created_at DESC;
```

## Features

### ✅ Category-Based Personalization
- Reminders match user's most engaged category
- Different quotes for Mental Health, Nutrition, Fitness, etc.

### ✅ Daily Generation
- One reminder per user per day
- Automatically generated when user visits homepage
- Stored in database for consistency

### ✅ User-Friendly Display
- Beautiful banner at top of homepage
- Easy to close
- Automatically marks as read when displayed

### ✅ Smart Fallback
- If user has no interests yet → Shows general wellness quote
- If no quotes for category → Falls back to general quotes
- Always provides value even for new users

## Customization

### Add More Quotes
Edit `data/motivational_quotes.sql` and add more quotes:
```sql
INSERT INTO motivational_quotes (category_id, quote_text, author, is_active) VALUES
(YOUR_CATEGORY_ID, 'Your quote here', 'Author Name', 1);
```

### Add Category Encouragements
Edit `Classes/ReminderModel.php` in the `getCategoryEncouragement()` method to add more category-specific messages.

### Change Display Location
The reminder banner is in `index.php`. You can move it or add it to other pages like `profile.php`.

## Troubleshooting

### Reminder Not Showing?
1. **Check if user is logged in** - Reminders only show for logged-in users
2. **Check database** - Verify `daily_reminders` table has a record for today
3. **Check browser console** - Look for JavaScript errors
4. **Check activity** - User needs some browsing activity to generate interests

### Wrong Category in Reminder?
- The system uses the **top interest** category
- Check `user_interests` table to see which category has the highest score
- Browse more in your desired category to increase its score

### Quotes Not Category-Specific?
- Verify category IDs in `motivational_quotes.sql` match your actual category IDs
- Check that quotes have the correct `category_id` set

## Next Steps (Optional Enhancements)

1. **Email Reminders** - Send daily reminders via email
2. **Reminder Preferences** - Let users choose reminder frequency/categories ✅ (Already implemented)
3. **Reminder History** - Show past reminders in profile ✅ (Already implemented)
4. **Multiple Reminders** - Allow multiple reminders per day (morning, afternoon, evening)
5. **Notification Customization** - Allow users to customize notification appearance and duration

