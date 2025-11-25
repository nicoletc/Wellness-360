# User Activity Tracking & Recommendations System

## Overview
This system tracks user behavior (time spent on pages, articles read, products viewed) and uses this data to provide personalized recommendations and daily reminders.

## How It Works

### 1. **Activity Tracking**
- **JavaScript Library** (`js/activity_tracker.js`): Automatically tracks:
  - Time spent on pages (articles, products, shop, wellness hub)
  - Page views
  - Scroll depth (engagement indicator)
  - Visibility changes (when user switches tabs)

### 2. **Database Tables**
Four new tables track user behavior:

#### `user_activity`
- Records every page view and time spent
- Links activities to categories
- Tracks both logged-in users and guests (by IP)

#### `user_interests`
- Aggregates user interests based on activity
- Calculates interest scores (views + time spent)
- Updates automatically as users browse

#### `daily_reminders`
- Stores daily reminders and motivational quotes
- One reminder per user per day
- Can include product reminders, article suggestions, etc.

#### `motivational_quotes`
- Stores motivational quotes by category
- Used for daily wellness reminders

### 3. **Recommendation Algorithm**
The recommendation system (`ProfileModel::getRecommendedContent()`) works by:

1. **Getting User Interests**: Finds top 3 categories user is most interested in
2. **Product Recommendations**:
   - Shows products from interest categories
   - Excludes products already viewed but not purchased
   - Prioritizes products with stock available
3. **Article Recommendations**:
   - Shows articles from interest categories
   - Excludes articles already read
   - Prioritizes recent articles
4. **Mixing Content**: Shuffles products and articles for variety

### 4. **Daily Reminders**
- **Motivational Quotes**: Based on user's top interest category
- **Product Reminders**: For products viewed but not purchased (last 7 days)
- **Article Reminders**: For new articles in user's interest categories

## Implementation Steps

### Step 1: Run Database Migrations
```sql
-- Run: settings/user_activity_tracking_migration.sql
-- This creates: user_activity, user_interests, daily_reminders, motivational_quotes tables
```

### Step 2: Seed Motivational Quotes
```sql
-- Run: data/motivational_quotes.sql
-- This populates sample motivational quotes
```

### Step 3: Tracking is Automatic
- The `activity_tracker.js` is already included in:
  - `View/single_article.php`
  - `View/single_product.php`
  - `View/wellness_hub.php`
  - `View/shop.php`

### Step 4: View Recommendations
- Go to Profile → "Recommended" tab
- Recommendations appear based on browsing history

## How Recommendations Improve Over Time

1. **Initial State**: No recommendations (user needs to browse first)
2. **After First Browse**: Recommendations based on first category viewed
3. **After Multiple Sessions**: Recommendations refine based on:
   - Most time spent categories
   - Most viewed categories
   - Products viewed but not purchased
   - Articles read multiple times

## Daily Reminders Feature

### To Display Daily Reminders:
Add this to `index.php` or `profile.php`:

```javascript
// Fetch and display daily reminder
async function showDailyReminder() {
    try {
        const response = await fetch('../Actions/get_daily_reminder_action.php');
        const result = await response.json();
        
        if (result.status && result.reminder) {
            // Display reminder (e.g., in a banner or modal)
            Swal.fire({
                icon: 'info',
                title: result.reminder.title,
                text: result.reminder.message,
                confirmButtonText: 'Got it!',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error fetching reminder:', error);
    }
}

// Call on page load (optional - can be triggered by user)
// showDailyReminder();
```

## Files Created/Modified

### New Files:
1. `settings/user_activity_tracking_migration.sql` - Database schema
2. `data/motivational_quotes.sql` - Seed data for quotes
3. `js/activity_tracker.js` - Frontend tracking library
4. `Classes/ActivityModel.php` - Activity tracking model
5. `Classes/ReminderModel.php` - Reminders and quotes model
6. `Actions/record_activity_action.php` - API endpoint for tracking
7. `Actions/get_daily_reminder_action.php` - API endpoint for reminders

### Modified Files:
1. `Classes/ProfileModel.php` - Updated `getRecommendedContent()` with real algorithm
2. `View/single_article.php` - Added activity tracker script
3. `View/single_product.php` - Added activity tracker script
4. `View/wellness_hub.php` - Added activity tracker script
5. `View/shop.php` - Added activity tracker script

## Privacy & Performance

- **Privacy**: Guest users tracked by IP only (no personal data)
- **Performance**: Activity recording is asynchronous (non-blocking)
- **Spam Prevention**: Duplicate views within 1 hour are ignored
- **Efficiency**: Interest scores calculated incrementally (not on every query)

## Next Steps (Optional Enhancements)

1. **Email Reminders**: Send daily reminders via email
2. **Push Notifications**: Browser push notifications for reminders
3. **Similar Users**: Recommend based on users with similar interests
4. **Trending Content**: Mix trending items with personalized recommendations
5. **A/B Testing**: Test different recommendation algorithms

