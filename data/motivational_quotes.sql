-- Motivational Quotes Seed Data
-- Insert sample motivational quotes by category
-- Run this SQL script to populate the motivational_quotes table
-- Note: category_id should match your category table. Update the IDs below based on your actual category IDs.

-- First, get category IDs (you may need to adjust these based on your actual category table)
-- Mental Health category (assuming cat_id = 1, adjust as needed)
-- Nutrition category (assuming cat_id = 2, adjust as needed)
-- Fitness category (assuming cat_id = 3, adjust as needed)

INSERT INTO motivational_quotes (category_id, quote_text, author, is_active) VALUES
-- Mental Health Quotes (category_id = 1, adjust if different)
(1, 'Your mental health is a priority. Your happiness is essential. Your self-care is a necessity.', NULL, 1),
(1, 'It\'s okay to not be okay. What\'s not okay is to stay that way.', NULL, 1),
(1, 'Healing is not linear. It\'s okay to have bad days.', NULL, 1),
(1, 'You are stronger than you think, braver than you believe, and more capable than you imagine.', NULL, 1),
(1, 'The mind is everything. What you think you become.', 'Buddha', 1),
(1, 'Happiness is not something ready-made. It comes from your own actions.', 'Dalai Lama', 1),

-- Nutrition Quotes (category_id = 2, adjust if different)
(2, 'Take care of your body. It\'s the only place you have to live.', 'Jim Rohn', 1),
(2, 'Let food be thy medicine and medicine be thy food.', 'Hippocrates', 1),
(2, 'The food you eat can be either the safest and most powerful form of medicine or the slowest form of poison.', 'Ann Wigmore', 1),
(2, 'Every time you eat is an opportunity to nourish your body.', NULL, 1),
(2, 'An apple a day keeps the doctor away.', 'Proverb', 1),
(2, 'You are what you eat. Choose wisely.', NULL, 1),

-- Fitness Quotes (category_id = 3, adjust if different)
(3, 'The only bad workout is the one that didn\'t happen.', NULL, 1),
(3, 'Exercise is a celebration of what your body can do, not a punishment for what you ate.', NULL, 1),
(3, 'Strength doesn\'t come from what you can do. It comes from overcoming the things you once thought you couldn\'t.', NULL, 1),
(3, 'Your body can stand almost anything. It\'s your mind that you have to convince.', NULL, 1),
(3, 'To enjoy the glow of good health, you must exercise.', 'Gene Tunney', 1),
(3, 'The journey of a thousand miles begins with a single step.', 'Lao Tzu', 1),

-- General Wellness Quotes (category_id = NULL for general quotes)
(NULL, 'Wellness is the complete integration of body, mind, and spirit.', 'Greg Anderson', 1),
(NULL, 'The greatest wealth is health.', 'Virgil', 1),
(NULL, 'Health is not about the weight you lose, but about the life you gain.', NULL, 1),
(NULL, 'Your body hears everything your mind says. Stay positive.', NULL, 1),
(NULL, 'Self-care is giving the world the best of you, instead of what\'s left of you.', NULL, 1),
(NULL, 'Wellness is not a destination, it\'s a way of life.', NULL, 1),
(NULL, 'The only way to do great work is to love what you do.', 'Steve Jobs', 1),
(NULL, 'Believe you can and you\'re halfway there.', 'Theodore Roosevelt', 1),
(NULL, 'The best way to predict the future is to create it.', 'Peter Drucker', 1);

