/**
 * Workshop Management JavaScript
 * Handles validation and asynchronous operations for workshop CRUD
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load workshops on page load
    loadWorkshops();

    // Form event listeners
    const addForm = document.getElementById('addWorkshopForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddWorkshop);
    }

    const updateForm = document.getElementById('updateWorkshopForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateWorkshop);
    }

    // Search functionality
    const searchInput = document.getElementById('workshopSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filter functionality
    const filterSelect = document.getElementById('workshopFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', handleFilter);
    }

    // Image upload handlers
    const imageInput = document.getElementById('add_workshop_image');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }

    const updateImageInput = document.getElementById('update_workshop_image');
    if (updateImageInput) {
        updateImageInput.addEventListener('change', handleUpdateImagePreview);
    }
});

/**
 * Load all workshops from the server
 */
async function loadWorkshops() {
    const tbody = document.querySelector('#workshopsTable tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Loading workshops...</td></tr>';
    }

    try {
        const response = await fetch('../Actions/fetch_workshop_action.php');
        const result = await response.json();

        if (result.status) {
            displayWorkshops(result.data || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load workshops.',
                confirmButtonColor: '#7FB685'
            });
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #d4183d;">Error loading workshops.</td></tr>';
            }
        }
    } catch (error) {
        console.error('Error loading workshops:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while loading workshops.',
            confirmButtonColor: '#7FB685'
        });
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #d4183d;">Error loading workshops.</td></tr>';
        }
    }
}

/**
 * Display workshops in the table
 */
function displayWorkshops(workshops) {
    const tbody = document.querySelector('#workshopsTable tbody');
    if (!tbody) return;

    if (workshops.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">No workshops found.</td></tr>';
        return;
    }

    tbody.innerHTML = workshops.map(workshop => {
        const date = new Date(workshop.workshop_date + 'T' + workshop.workshop_time);
        const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        const location = workshop.workshop_type === 'in-person' ? (workshop.location || 'N/A') : 'Virtual';
        const typeBadge = workshop.workshop_type === 'in-person' 
            ? '<span class="badge badge-primary">In-Person</span>' 
            : '<span class="badge badge-success">Virtual</span>';
        const registeredCount = parseInt(workshop.registered_count || 0);
        const maxParticipants = parseInt(workshop.max_participants || 0);

        return `
            <tr>
                <td>${escapeHtml(workshop.workshop_title)}</td>
                <td>${escapeHtml(workshop.workshop_leader)}</td>
                <td>${formattedDate}</td>
                <td>${formattedTime}</td>
                <td>${typeBadge}</td>
                <td>${escapeHtml(location)}</td>
                <td>${maxParticipants}</td>
                <td>${registeredCount} / ${maxParticipants}</td>
                <td>
                    <button class="btn-icon" onclick="viewAttendees(${workshop.workshop_id}, '${escapeHtml(workshop.workshop_title)}')" title="View Attendees">
                        <i class="fas fa-users"></i>
                    </button>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon" onclick="viewWorkshop(${workshop.workshop_id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" onclick="editWorkshop(${workshop.workshop_id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" onclick="deleteWorkshop(${workshop.workshop_id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Handle add workshop form submission
 */
async function handleAddWorkshop(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    // Validate form
    const workshopTitle = form.workshop_title.value.trim();
    const workshopLeader = form.workshop_leader.value.trim();
    const workshopDate = form.workshop_date.value;
    const workshopTime = form.workshop_time.value;
    const workshopType = form.workshop_type.value;
    const location = form.location ? form.location.value.trim() : '';
    const maxParticipants = parseInt(form.max_participants.value);

    if (workshopTitle.length < 3 || workshopTitle.length > 200) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Workshop title must be between 3-200 characters.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    if (workshopType === 'in-person' && !location) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Location is required for in-person workshops.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    if (maxParticipants <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Maximum participants must be greater than 0.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    // Show loading state
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-block';

    // Prepare form data
    const formData = new FormData(form);

    try {
        const response = await fetch('../Actions/add_workshop_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status) {
            const workshopId = result.workshop_id;
            
            // Upload image if provided
            const imageFile = form.workshop_image?.files[0];
            if (imageFile && workshopId) {
                const uploadResult = await uploadWorkshopImage(imageFile, workshopId);
                if (!uploadResult.status) {
                    // Workshop was created but image upload failed
                    Swal.fire({
                        icon: 'warning',
                        title: 'Workshop Created',
                        text: 'Workshop created but image upload failed: ' + (uploadResult.message || 'Unknown error'),
                        confirmButtonColor: '#7FB685'
                    });
                }
            }
            
            // Close modal first
            closeAddModal();
            form.reset();
            const preview = document.getElementById('add_image_preview');
            if (preview) preview.style.display = 'none';
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message || 'Workshop created successfully!',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadWorkshops();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to create workshop.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error adding workshop:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while creating the workshop.',
            confirmButtonColor: '#7FB685'
        });
    } finally {
        // Reset loading state
        submitBtn.disabled = false;
        btnText.style.display = 'inline-block';
        btnLoader.style.display = 'none';
    }
}

/**
 * Handle update workshop form submission
 */
async function handleUpdateWorkshop(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    // Validate form
    const workshopTitle = form.workshop_title.value.trim();
    const workshopLeader = form.workshop_leader.value.trim();
    const workshopDate = form.workshop_date.value;
    const workshopTime = form.workshop_time.value;
    const workshopType = form.workshop_type.value;
    const location = form.location ? form.location.value.trim() : '';
    const maxParticipants = parseInt(form.max_participants.value);

    if (workshopTitle.length < 3 || workshopTitle.length > 200) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Workshop title must be between 3-200 characters.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    if (workshopType === 'in-person' && !location) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Location is required for in-person workshops.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    if (maxParticipants <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Maximum participants must be greater than 0.',
            confirmButtonColor: '#7FB685'
        });
        return;
    }

    // Show loading state
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-block';

    // Prepare form data
    const formData = new FormData(form);

    try {
        const response = await fetch('../Actions/update_workshop_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status) {
            const workshopId = parseInt(form.workshop_id.value);
            
            // Upload image if provided
            const imageFile = form.workshop_image?.files[0];
            if (imageFile && workshopId) {
                const uploadResult = await uploadWorkshopImage(imageFile, workshopId);
                if (!uploadResult.status) {
                    // Workshop was updated but image upload failed
                    Swal.fire({
                        icon: 'warning',
                        title: 'Workshop Updated',
                        text: 'Workshop updated but image upload failed: ' + (uploadResult.message || 'Unknown error'),
                        confirmButtonColor: '#7FB685'
                    });
                }
            }
            
            // Close modal first
            closeUpdateModal();
            form.reset();
            const preview = document.getElementById('update_image_preview');
            if (preview) preview.style.display = 'none';
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message || 'Workshop updated successfully!',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadWorkshops();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update workshop.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error updating workshop:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while updating the workshop.',
            confirmButtonColor: '#7FB685'
        });
    } finally {
        // Reset loading state
        submitBtn.disabled = false;
        btnText.style.display = 'inline-block';
        btnLoader.style.display = 'none';
    }
}

/**
 * View workshop details
 */
async function viewWorkshop(workshopId) {
    try {
        const response = await fetch('../Actions/fetch_workshop_action.php');
        const result = await response.json();

        if (result.status) {
            const workshop = result.data.find(w => w.workshop_id == workshopId);
            if (workshop) {
                document.getElementById('view_workshop_id').textContent = workshop.workshop_id;
                document.getElementById('view_workshop_title').textContent = workshop.workshop_title;
                document.getElementById('view_workshop_desc').textContent = workshop.workshop_desc || 'N/A';
                document.getElementById('view_workshop_leader').textContent = workshop.workshop_leader;
                document.getElementById('view_workshop_date').textContent = new Date(workshop.workshop_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                document.getElementById('view_workshop_time').textContent = workshop.workshop_time;
                document.getElementById('view_workshop_type').textContent = workshop.workshop_type === 'in-person' ? 'In-Person' : 'Virtual';
                
                const locationRow = document.getElementById('view_location_row');
                if (workshop.workshop_type === 'in-person') {
                    document.getElementById('view_location').textContent = workshop.location || 'N/A';
                    locationRow.style.display = 'flex';
                } else {
                    locationRow.style.display = 'none';
                }
                
                document.getElementById('view_max_participants').textContent = workshop.max_participants;
                document.getElementById('view_customer_name').textContent = workshop.customer_name || 'N/A';
                
                // Set workshop image
                const imagePreview = document.getElementById('view_workshop_image');
                if (imagePreview && workshop.workshop_image) {
                    // Database stores paths as ../../uploads/... which is correct for Admin/ folder
                    let imageSrc = workshop.workshop_image;
                    if (!imageSrc.startsWith('../../') && !imageSrc.startsWith('../')) {
                        if (imageSrc.startsWith('uploads/')) {
                            imageSrc = '../../' + imageSrc;
                        } else {
                            imageSrc = '../../uploads/' + imageSrc;
                        }
                    }
                    imagePreview.src = imageSrc;
                    imagePreview.style.display = 'block';
                } else if (imagePreview) {
                    imagePreview.style.display = 'none';
                }

                document.getElementById('viewWorkshopModal').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error viewing workshop:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while loading workshop details.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Edit workshop
 */
async function editWorkshop(workshopId) {
    try {
        const response = await fetch('../Actions/fetch_workshop_action.php');
        const result = await response.json();

        if (result.status) {
            const workshop = result.data.find(w => w.workshop_id == workshopId);
            if (workshop) {
                document.getElementById('update_workshop_id').value = workshop.workshop_id;
                document.getElementById('update_workshop_title').value = workshop.workshop_title;
                document.getElementById('update_workshop_desc').value = workshop.workshop_desc || '';
                document.getElementById('update_workshop_leader').value = workshop.workshop_leader;
                document.getElementById('update_workshop_date').value = workshop.workshop_date;
                document.getElementById('update_workshop_time').value = workshop.workshop_time;
                document.getElementById('update_workshop_type').value = workshop.workshop_type;
                document.getElementById('update_location').value = workshop.location || '';
                document.getElementById('update_max_participants').value = workshop.max_participants;

                // Reset image preview
                const updatePreview = document.getElementById('update_image_preview');
                if (updatePreview) updatePreview.style.display = 'none';
                const updateImageInput = document.getElementById('update_workshop_image');
                if (updateImageInput) updateImageInput.value = '';

                toggleLocationField(workshop.workshop_type, 'update');
                document.getElementById('updateWorkshopModal').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error loading workshop for edit:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while loading workshop details.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * View workshop attendees
 */
async function viewAttendees(workshopId, workshopTitle) {
    try {
        const response = await fetch(`../Actions/fetch_workshop_attendees_action.php?workshop_id=${workshopId}`);
        const result = await response.json();

        if (result.status) {
            // Set workshop title
            document.getElementById('attendeesWorkshopTitle').textContent = result.workshop_title || workshopTitle;
            
            // Display attendees
            const tbody = document.querySelector('#attendeesTable tbody');
            if (tbody) {
                if (result.attendees && result.attendees.length > 0) {
                    tbody.innerHTML = result.attendees.map((attendee, index) => {
                        const registeredDate = new Date(attendee.registered_at);
                        const formattedDate = registeredDate.toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        const statusBadge = attendee.status === 'registered' 
                            ? '<span class="badge badge-success">Registered</span>'
                            : '<span class="badge badge-warning">Cancelled</span>';
                        
                        return `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${escapeHtml(attendee.customer_name || 'N/A')}</td>
                                <td>${escapeHtml(attendee.customer_email || 'N/A')}</td>
                                <td>${formattedDate}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No attendees registered yet.</td></tr>';
                }
            }
            
            // Show modal
            document.getElementById('viewAttendeesModal').style.display = 'block';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load attendees.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading attendees:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while loading attendees.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Delete workshop
 */
async function deleteWorkshop(workshopId) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d4183d',
        cancelButtonColor: '#6B7E75',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('workshop_id', workshopId);

            const response = await fetch('../Actions/delete_workshop_action.php', {
                method: 'POST',
                body: formData
            });

            const deleteResult = await response.json();

            if (deleteResult.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: deleteResult.message || 'Workshop has been deleted.',
                    confirmButtonColor: '#7FB685'
                });
                loadWorkshops();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: deleteResult.message || 'Failed to delete workshop.',
                    confirmButtonColor: '#7FB685'
                });
            }
        } catch (error) {
            console.error('Error deleting workshop:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the workshop.',
                confirmButtonColor: '#7FB685'
            });
        }
    }
}

/**
 * Handle search
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#workshopsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

/**
 * Handle filter
 */
function handleFilter(e) {
    const filterValue = e.target.value;
    const rows = document.querySelectorAll('#workshopsTable tbody tr');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    rows.forEach(row => {
        if (filterValue === 'all') {
            row.style.display = '';
            return;
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length < 5) {
            row.style.display = 'none';
            return;
        }
        
        const typeCell = cells[4];
        const dateCell = cells[2];
        
        if (filterValue === 'in-person' || filterValue === 'virtual') {
            const typeText = typeCell.textContent.toLowerCase();
            row.style.display = typeText.includes(filterValue) ? '' : 'none';
        } else if (filterValue === 'upcoming') {
            const dateText = dateCell.textContent;
            const rowDate = new Date(dateText);
            row.style.display = rowDate >= today ? '' : 'none';
        } else if (filterValue === 'past') {
            const dateText = dateCell.textContent;
            const rowDate = new Date(dateText);
            row.style.display = rowDate < today ? '' : 'none';
        }
    });
}

/**
 * Handle image preview for add form
 */
function handleImagePreview(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('add_image_preview');
    const previewImg = document.getElementById('add_image_preview_img');
    
    if (file && previewDiv && previewImg) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else if (previewDiv) {
        previewDiv.style.display = 'none';
    }
}

/**
 * Handle image preview for update form
 */
function handleUpdateImagePreview(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('update_image_preview');
    const previewImg = document.getElementById('update_image_preview_img');
    
    if (file && previewDiv && previewImg) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else if (previewDiv) {
        previewDiv.style.display = 'none';
    }
}

/**
 * Upload workshop image
 */
async function uploadWorkshopImage(file, workshopId = 0) {
    if (!file) {
        return { status: 'success', path: '' };
    }

    const formData = new FormData();
    formData.append('workshop_image', file);
    formData.append('workshop_id', workshopId);

    try {
        const response = await fetch('../Actions/upload_workshop_image_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            return {
                status: true,
                path: result.path
            };
        } else {
            return {
                status: false,
                message: result.message || 'Failed to upload image.'
            };
        }
    } catch (error) {
        console.error('Image upload error:', error);
        return {
            status: false,
            message: 'Failed to upload image. Please try again.'
        };
    }
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

