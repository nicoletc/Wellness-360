/**
 * Discussions JavaScript
 * Handles discussion creation and reply functionality
 */

// Create Discussion
document.addEventListener('DOMContentLoaded', function() {
    const postForm = document.getElementById('postForm');
    const categoryNewInput = document.getElementById('postCategoryNew');
    const categorySelect = document.getElementById('postCategorySelect');
    const categoryHiddenInput = document.getElementById('postCategory');
    
    // Handle mutual exclusivity - when one is filled, disable/clear the other
    if (categoryNewInput && categorySelect) {
        categoryNewInput.addEventListener('input', function() {
            if (this.value.trim()) {
                categorySelect.disabled = true;
                categorySelect.value = '';
                categoryHiddenInput.value = this.value.trim();
            } else {
                categorySelect.disabled = false;
                if (categorySelect.value) {
                    categoryHiddenInput.value = categorySelect.value;
                } else {
                    categoryHiddenInput.value = '';
                }
            }
        });
        
        categorySelect.addEventListener('change', function() {
            if (this.value) {
                categoryNewInput.disabled = true;
                categoryNewInput.value = '';
                // Use the category ID if it's numeric, otherwise use the text value
                categoryHiddenInput.value = this.value;
            } else {
                categoryNewInput.disabled = false;
                if (categoryNewInput.value.trim()) {
                    categoryHiddenInput.value = categoryNewInput.value.trim();
                } else {
                    categoryHiddenInput.value = '';
                }
            }
        });
    }
    
    if (postForm) {
        postForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('postSubmitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Get category from hidden input (set by the mutual exclusivity handlers)
            let category = categoryHiddenInput.value.trim();
            const title = document.getElementById('postTitle').value.trim();
            const content = document.getElementById('postContent').value.trim();
            
            // Validation
            if (!category || !title || !content) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all fields, including selecting or typing a category.'
                });
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            
            try {
                const formData = new FormData();
                formData.append('category', category);
                formData.append('title', title);
                formData.append('content', content);
                
                const response = await fetch('../Actions/create_discussion_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Discussion created successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to show new discussion, preserving the discussions tab
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('tab', 'discussions');
                        window.location.href = currentUrl.toString();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to create discussion. Please try again.'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }
        });
    }
});

// Toggle Replies Section
function toggleReplies(discussionId) {
    const repliesSection = document.getElementById('replies-' + discussionId);
    const repliesList = document.getElementById('replies-list-' + discussionId);
    const toggleBtn = event.target.closest('.btn-reply-toggle');
    
    if (!repliesSection) return;
    
    if (repliesSection.style.display === 'none') {
        repliesSection.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Replies';
        
        // Load replies if not already loaded
        if (repliesList && repliesList.children.length === 0) {
            loadReplies(discussionId);
        }
    } else {
        repliesSection.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fas fa-comment-dots"></i> View Replies';
    }
}

// Load Replies for a Discussion
async function loadReplies(discussionId) {
    const repliesList = document.getElementById('replies-list-' + discussionId);
    
    if (!repliesList) return;
    
    // Show loading state
    repliesList.innerHTML = '<div class="reply-loading"><i class="fas fa-spinner fa-spin"></i> Loading replies...</div>';
    
    try {
        const response = await fetch(`../Actions/fetch_replies_action.php?comm_id=${discussionId}`);
        const result = await response.json();
        
        if (result.status === 'success' && result.replies) {
            displayReplies(discussionId, result.replies);
        } else {
            repliesList.innerHTML = '<div class="no-replies">No replies yet. Be the first to reply!</div>';
        }
    } catch (error) {
        console.error('Error loading replies:', error);
        repliesList.innerHTML = '<div class="reply-error">Error loading replies. Please try again.</div>';
    }
}

// Display Replies
function displayReplies(discussionId, replies) {
    const repliesList = document.getElementById('replies-list-' + discussionId);
    
    if (!repliesList) return;
    
    if (replies.length === 0) {
        repliesList.innerHTML = '<div class="no-replies">No replies yet. Be the first to reply!</div>';
        return;
    }
    
    let html = '';
    replies.forEach(reply => {
        html += `
            <div class="reply-item">
                <div class="reply-author">
                    <img src="${reply.authorImage || '../../uploads/placeholder.jpg'}" 
                         alt="Anonymous"
                         class="reply-avatar"
                         onerror="this.onerror=null; this.style.display='none';">
                    <div class="reply-author-info">
                        <span class="reply-author-name">Anonymous</span>
                        <span class="reply-timestamp">${reply.timestamp || 'Just now'}</span>
                    </div>
                </div>
                <div class="reply-content">
                    ${escapeHtml(reply.content || '')}
                </div>
            </div>
        `;
    });
    
    repliesList.innerHTML = html;
}

// Add Reply
document.addEventListener('DOMContentLoaded', function() {
    // Handle reply form submissions
    document.querySelectorAll('.reply-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const discussionId = form.id.replace('replyForm-', '');
            const submitBtn = form.querySelector('.reply-submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            const contentInput = form.querySelector('.reply-input');
            const content = contentInput.value.trim();
            
            if (!content) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a reply.'
                });
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            
            try {
                const formData = new FormData();
                formData.append('comm_id', discussionId);
                formData.append('content', content);
                
                const response = await fetch('../Actions/add_reply_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Clear form
                    contentInput.value = '';
                    
                    // Reload replies
                    if (result.replies) {
                        displayReplies(discussionId, result.replies);
                    } else {
                        loadReplies(discussionId);
                    }
                    
                    // Update reply count
                    updateReplyCount(discussionId);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Reply added successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to add reply. Please try again.'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }
        });
    });
});

// Update Reply Count
function updateReplyCount(discussionId) {
    const discussionCard = document.querySelector(`[data-discussion-id="${discussionId}"]`);
    if (!discussionCard) return;
    
    const replyCountSpan = discussionCard.querySelector('.reply-count');
    if (replyCountSpan) {
        // Reload replies to get updated count
        loadReplies(discussionId).then(() => {
            // Count replies in the list
            const repliesList = document.getElementById('replies-list-' + discussionId);
            if (repliesList) {
                const replyItems = repliesList.querySelectorAll('.reply-item');
                const count = replyItems.length;
                replyCountSpan.textContent = count;
            }
        });
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

