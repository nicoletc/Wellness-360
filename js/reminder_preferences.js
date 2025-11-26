/**
 * Reminder Preferences JavaScript
 * Handles reminder preferences form
 */

document.addEventListener('DOMContentLoaded', function() {
    const preferencesForm = document.getElementById('reminderPreferencesForm');
    
    // Handle form submission
    if (preferencesForm) {
        preferencesForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(preferencesForm);
            
            // Handle reminder_time - HTML time input returns HH:MM, we need HH:MM:SS
            let reminderTime = formData.get('reminder_time') || '09:00';
            // Only add :00 if it's not already in HH:MM:SS format
            if (reminderTime && reminderTime.length === 5 && reminderTime.indexOf(':') === 2) {
                reminderTime = reminderTime + ':00';
            }
            // If it's already in HH:MM:SS format, use it as is
            // If it's somehow longer, truncate to HH:MM:SS
            if (reminderTime && reminderTime.length > 8) {
                reminderTime = reminderTime.substring(0, 8);
            }
            
            const data = {
                reminder_frequency: formData.get('reminder_frequency'),
                preferred_categories: formData.getAll('preferred_categories[]').map(id => parseInt(id)),
                email_reminders_enabled: formData.get('email_reminders_enabled') ? 1 : 0,
                reminder_time: reminderTime
            };
            
            // If no categories selected, set to null (all categories)
            if (data.preferred_categories.length === 0) {
                data.preferred_categories = null;
            }
            
            try {
                const response = await fetch('../Actions/save_reminder_preferences_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status) {
                    // Show in-page notification
                    if (window.notifications) {
                        window.notifications.success('Your reminder preferences have been saved.', 3000);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Preferences Saved',
                            text: result.message || 'Your reminder preferences have been saved.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Preferences saved successfully!');
                    }
                } else {
                    throw new Error(result.message || 'Failed to save preferences.');
                }
            } catch (error) {
                console.error('Error saving preferences:', error);
                // Show in-page notification
                if (window.notifications) {
                    window.notifications.error(error.message || 'Failed to save preferences. Please try again.', 5000);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save preferences. Please try again.'
                    });
                } else {
                    alert('Error: ' + error.message);
                }
            }
        });
    }
});

