/**
 * Workshop Registration JavaScript
 * Handles workshop registration functionality on the community page
 */

/**
 * Update workshop button state
 * @param {number} workshopId Workshop ID
 * @param {string} state 'register' or 'cancel'
 * @param {number} newRegisteredCount Updated registration count
 */
function updateWorkshopButton(workshopId, state, newRegisteredCount) {
    const button = document.getElementById(`workshop-btn-${workshopId}`);
    if (!button) return;
    
    // Get capacity from the registration info span
    const registrationInfo = button.closest('.workshop-registration').querySelector('.registration-info span');
    let capacity = 0;
    
    if (registrationInfo) {
        const capacityMatch = registrationInfo.textContent.match(/\/(\d+)/);
        capacity = capacityMatch ? parseInt(capacityMatch[1]) : 0;
        registrationInfo.textContent = `${newRegisteredCount}/${capacity} registered`;
        
        // Update progress bar
        const progressBar = button.closest('.workshop-registration').querySelector('.registration-progress');
        if (progressBar && capacity > 0) {
            const progress = (newRegisteredCount / capacity) * 100;
            progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
        }
    }
    
    if (state === 'cancel') {
        // Change to Cancel button
        button.className = 'btn btn-secondary workshop-cancel-btn';
        button.textContent = 'Cancel Registration';
        button.onclick = function() {
            cancelWorkshopRegistration(workshopId, capacity, newRegisteredCount);
        };
    } else {
        // Change to Register button
        button.className = 'btn btn-primary workshop-register-btn';
        button.textContent = 'Register Now';
        button.onclick = function() {
            registerForWorkshop(workshopId, capacity, newRegisteredCount);
        };
    }
}

/**
 * Register for a workshop
 * @param {number} workshopId Workshop ID
 * @param {number} maxCapacity Maximum participants
 * @param {number} currentRegistered Current registered count
 */
async function registerForWorkshop(workshopId, maxCapacity, currentRegistered) {
    // Check if workshop is full
    if (currentRegistered >= maxCapacity) {
        Swal.fire({
            icon: 'warning',
            title: 'Workshop Full',
            text: 'This workshop has reached its maximum capacity.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    // Show confirmation
    const result = await Swal.fire({
        title: 'Register for Workshop?',
        text: 'Are you sure you want to register for this workshop?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7FB685',
        cancelButtonColor: '#6B7E75',
        confirmButtonText: 'Yes, Register',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Registering...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const formData = new FormData();
        formData.append('workshop_id', workshopId);

        const response = await fetch('../Actions/register_workshop_action.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Successfully registered for the workshop!',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Update button to "Cancel Registration"
                updateWorkshopButton(workshopId, 'cancel', data.registration_count || currentRegistered + 1);
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: data.message || 'Failed to register for the workshop.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Registration error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while registering. Please try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Cancel workshop registration
 * @param {number} workshopId Workshop ID
 * @param {number} maxCapacity Maximum participants
 * @param {number} currentRegistered Current registered count
 */
async function cancelWorkshopRegistration(workshopId, maxCapacity, currentRegistered) {
    // Show confirmation
    const result = await Swal.fire({
        title: 'Cancel Registration?',
        text: 'Are you sure you want to cancel your registration for this workshop?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d4183d',
        cancelButtonColor: '#6B7E75',
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'Keep Registration'
    });

    if (!result.isConfirmed) {
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Cancelling...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const formData = new FormData();
        formData.append('workshop_id', workshopId);

        const response = await fetch('../Actions/cancel_workshop_registration_action.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Cancelled',
                text: data.message || 'Registration cancelled successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Update button to "Register Now"
                updateWorkshopButton(workshopId, 'register', data.registration_count || Math.max(0, currentRegistered - 1));
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Cancellation Failed',
                text: data.message || 'Failed to cancel registration.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Cancellation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while cancelling. Please try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

