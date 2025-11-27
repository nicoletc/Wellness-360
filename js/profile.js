/**
 * Profile JavaScript
 * Handles order details modal and other profile interactions
 */

// Load order details and show modal
async function showOrderDetails(orderId) {
    try {
        // Show loading state
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Loading...',
                text: 'Fetching order details...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        const response = await fetch(`../Actions/get_order_details_action.php?order_id=${orderId}`);
        const result = await response.json();
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        
        if (result.status === 'success') {
            displayOrderModal(result);
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to load order details.'
                });
            } else {
                alert(result.message || 'Failed to load order details.');
            }
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while loading order details.'
            });
        } else {
            alert('An error occurred while loading order details.');
        }
    }
}

// Display order details in modal
function displayOrderModal(data) {
    const order = data.order;
    const items = data.items;
    const payment = data.payment;
    const total = data.total;
    
    // Build items HTML
    let itemsHtml = '';
    if (items && items.length > 0) {
        itemsHtml = '<div style="max-height: 400px; overflow-y: auto; margin: 1rem 0;">';
        items.forEach(item => {
            // Normalize image path - if it already starts with ../../, use as-is, otherwise prepend ../
            let imagePath = item.product_image;
            if (!imagePath.startsWith('../') && !imagePath.startsWith('../../')) {
                if (imagePath.startsWith('uploads/')) {
                    imagePath = '../../' + imagePath;
                } else {
                    imagePath = '../' + imagePath;
                }
            }
            
            itemsHtml += `
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem;">
                    <img src="${imagePath}" 
                         alt="${item.product_title}" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                         onerror="this.src='../../uploads/placeholder.jpg';">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">${item.product_title}</h4>
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">
                            Quantity: ${item.quantity} × ₵${item.product_price.toFixed(2)}
                        </p>
                    </div>
                    <div style="font-weight: bold; font-size: 1.1rem;">
                        ₵${item.subtotal.toFixed(2)}
                    </div>
                </div>
            `;
        });
        itemsHtml += '</div>';
    } else {
        itemsHtml = '<p style="color: #666; text-align: center; padding: 2rem;">No items found.</p>';
    }
    
    // Build payment info HTML
    let paymentHtml = '';
    if (payment) {
        paymentHtml = `
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Payment Information</h4>
                <p style="margin: 0.25rem 0; color: #666;">
                    <strong>Amount:</strong> ${payment.currency} ${payment.amount.toFixed(2)}
                </p>
                <p style="margin: 0.25rem 0; color: #666;">
                    <strong>Payment Date:</strong> ${payment.payment_date}
                </p>
            </div>
        `;
    }
    
    // Show modal
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: `Order ${order.invoice_no}`,
            html: `
                <div style="text-align: left;">
                    <div style="margin-bottom: 1rem;">
                        <p style="margin: 0.5rem 0;"><strong>Order Date:</strong> ${order.order_date}</p>
                        <p style="margin: 0.5rem 0;"><strong>Status:</strong> 
                            <span style="padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: #28a745; color: white;">
                                ${order.order_status}
                            </span>
                        </p>
                    </div>
                    
                    <h4 style="margin: 1rem 0 0.5rem 0; font-size: 1.1rem; border-bottom: 2px solid #eee; padding-bottom: 0.5rem;">Order Items</h4>
                    ${itemsHtml}
                    
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #eee;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                            <span>Total:</span>
                            <span>₵${total.toFixed(2)}</span>
                        </div>
                    </div>
                    
                    ${paymentHtml}
                </div>
            `,
            width: '700px',
            confirmButtonText: 'Close',
            confirmButtonColor: '#007bff'
        });
    } else {
        // Fallback alert
        alert(`Order ${order.invoice_no}\nDate: ${order.order_date}\nStatus: ${order.order_status}\nTotal: ₵${total.toFixed(2)}`);
    }
}

// Update profile name
async function updateProfileName(newName) {
    try {
        const formData = new FormData();
        formData.append('customer_name', newName);
        
        const response = await fetch('../Actions/update_profile_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Update the name in the UI
            const profileNameElements = document.querySelectorAll('.profile-name');
            profileNameElements.forEach(el => {
                el.textContent = result.customer_name;
            });
            
            // Update greeting banner if exists
            const greetingText = document.querySelector('.greeting-text');
            if (greetingText) {
                const firstName = result.customer_name.split(' ')[0];
                greetingText.textContent = `Hello ${firstName}, get right back in!`;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: result.message || 'Your name has been updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert(result.message || 'Your name has been updated successfully.');
            }
            
            return true;
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to update profile.'
                });
            } else {
                alert(result.message || 'Failed to update profile.');
            }
            
            return false;
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating your profile.'
            });
        } else {
            alert('An error occurred while updating your profile.');
        }
        
        return false;
    }
}

// Show edit profile modal
function showEditProfileModal(currentName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Edit Profile',
            html: `
                <div style="text-align: left; padding: 0; width: 100%; box-sizing: border-box;">
                    <label for="profile-name-input" style="display: block; margin-bottom: 0.75rem; font-weight: 500; color: #333;">Name:</label>
                    <input type="text" 
                           id="profile-name-input" 
                           value="${currentName.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" 
                           placeholder="Enter your name"
                           style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box; outline: none; transition: border-color 0.3s ease; display: block;"
                           onfocus="this.style.borderColor='#007bff';"
                           onblur="this.style.borderColor='#ddd';">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            width: '450px',
            padding: '2rem',
            customClass: {
                popup: 'edit-profile-modal',
                htmlContainer: 'edit-profile-html'
            },
            didOpen: () => {
                const input = document.getElementById('profile-name-input');
                if (input) {
                    input.focus();
                    input.select();
                }
            },
            preConfirm: () => {
                const nameInput = document.getElementById('profile-name-input');
                const newName = nameInput ? nameInput.value.trim() : '';
                
                if (!newName) {
                    Swal.showValidationMessage('Name is required.');
                    return false;
                }
                
                if (newName.length < 2) {
                    Swal.showValidationMessage('Name must be at least 2 characters long.');
                    return false;
                }
                
                if (newName.length > 100) {
                    Swal.showValidationMessage('Name must not exceed 100 characters.');
                    return false;
                }
                
                // Check if name actually changed
                if (newName === currentName) {
                    Swal.showValidationMessage('Please enter a different name.');
                    return false;
                }
                
                return newName;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Show confirmation before updating
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm Changes',
                    text: `Are you sure you want to change your name to "${result.value}"?`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save changes',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d'
                }).then((confirmResult) => {
                    if (confirmResult.isConfirmed) {
                        updateProfileName(result.value);
                    }
                });
            }
        });
    } else {
        // Fallback to prompt
        const newName = prompt('Enter your new name:', currentName);
        if (newName && newName.trim() !== currentName) {
            updateProfileName(newName.trim());
        }
    }
}

// Initialize profile page
document.addEventListener('DOMContentLoaded', function() {
    // Handle order item clicks
    document.querySelectorAll('.order-item').forEach(item => {
        item.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            if (orderId) {
                showOrderDetails(orderId);
            }
        });
    });
    
    // Handle edit profile button click
    const editProfileBtn = document.querySelector('.btn-edit-profile');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            const profileNameElement = document.querySelector('.profile-name');
            const currentName = profileNameElement ? profileNameElement.textContent.trim() : '';
            
            if (currentName) {
                showEditProfileModal(currentName);
            }
        });
    }
});

