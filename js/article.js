/**
 * Article Management JavaScript
 * Handles validation and asynchronous operations for article CRUD
 */

let categories = [];

document.addEventListener('DOMContentLoaded', function() {
    // Load categories for dropdown
    loadCategories();
    
    // Load articles on page load
    loadArticles();

    // Form event listeners
    const addForm = document.getElementById('addArticleForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddArticle);
    }

    const updateForm = document.getElementById('updateArticleForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateArticle);
    }

    // Image upload handler
    const imageInput = document.getElementById('add_article_image');
    if (imageInput) {
        imageInput.addEventListener('change', handleAddImagePreview);
    }

    const updateImageInput = document.getElementById('update_article_image');
    if (updateImageInput) {
        updateImageInput.addEventListener('change', handleUpdateImagePreview);
    }

    // PDF upload handler
    const pdfInput = document.getElementById('article_pdf');
    if (pdfInput) {
        pdfInput.addEventListener('change', handlePdfPreview);
    }

    const updatePdfInput = document.getElementById('update_article_pdf');
    if (updatePdfInput) {
        updatePdfInput.addEventListener('change', handleUpdatePdfPreview);
    }

    // Search functionality
    const searchInput = document.getElementById('articleSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
});

/**
 * Load categories for dropdown
 */
async function loadCategories() {
    try {
        const response = await fetch('../Actions/fetch_categories_action.php');
        const result = await response.json();

        if (result.status) {
            categories = result.categories || [];
            populateCategoryDropdowns();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Populate category dropdowns
 */
function populateCategoryDropdowns() {
    const addSelect = document.getElementById('add_article_cat');
    const updateSelect = document.getElementById('update_article_cat');

    [addSelect, updateSelect].forEach(select => {
        if (select) {
            select.innerHTML = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.cat_id;
                option.textContent = cat.cat_name;
                select.appendChild(option);
            });
        }
    });
}

/**
 * Load all articles from the server
 */
async function loadArticles() {
    const tbody = document.querySelector('#articlesTable tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading articles...</td></tr>';
    }

    try {
        const response = await fetch('../Actions/fetch_article_action.php');
        const result = await response.json();

        if (result.status) {
            displayArticles(result.articles || []);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to load articles.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading articles:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Display articles in the table
 */
function displayArticles(articles) {
    const tbody = document.querySelector('#articlesTable tbody');
    if (!tbody) return;

    if (articles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No articles found.</td></tr>';
        return;
    }

    tbody.innerHTML = articles.map(article => {
        const hasPdf = article.has_pdf == 1 || article.has_pdf === true;
        const dateAdded = article.date_added ? formatDate(article.date_added) : '-';
        
        return `
        <tr>
            <td>
                <div class="article-cell">
                    <span>${escapeHtml(article.article_title)}</span>
                </div>
            </td>
            <td>${escapeHtml(article.article_author || 'N/A')}</td>
            <td>${escapeHtml(article.cat_name || 'N/A')}</td>
            <td>${article.article_views || 0}</td>
            <td>${dateAdded}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="Edit" onclick="openEditModal(${article.article_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" title="View" onclick="viewArticle(${article.article_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Delete" onclick="deleteArticle(${article.article_id}, '${escapeHtml(article.article_title)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

/**
 * Handle PDF preview for add form
 */
function handlePdfPreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('add_pdf_preview');
    
    if (file && preview) {
        if (file.type === 'application/pdf') {
            preview.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            preview.style.display = 'block';
        } else {
            preview.textContent = 'Please select a PDF file';
            preview.style.display = 'block';
        }
    }
}

/**
 * Handle PDF preview for update form
 */
function handleUpdatePdfPreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('update_pdf_preview');
    
    if (file && preview) {
        if (file.type === 'application/pdf') {
            preview.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            preview.style.display = 'block';
        } else {
            preview.textContent = 'Please select a PDF file';
            preview.style.display = 'block';
        }
    }
}

/**
 * Upload article PDF
 */
/**
 * Upload article image
 */
async function uploadArticleImage(file, articleId = 0) {
    if (!file) {
        return { status: true, path: '' };
    }

    if (!articleId || articleId <= 0) {
        return {
            status: false,
            message: 'Invalid article ID. Cannot upload image.'
        };
    }

    const formData = new FormData();
    formData.append('article_image', file);
    formData.append('article_id', articleId);

    try {
        const response = await fetch('../Actions/upload_article_image_action.php', {
            method: 'POST',
            body: formData
        });

        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Image upload HTTP error:', response.status, errorText);
            return {
                status: false,
                message: `Server error (${response.status}). ${errorText || 'Failed to upload image.'}`
            };
        }

        let result;
        try {
            const responseText = await response.text();
            console.log('Image upload raw response:', responseText);
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Image upload JSON parse error:', parseError);
            return {
                status: false,
                message: 'Invalid response from server. Please check server logs.'
            };
        }
        
        console.log('Image upload parsed response:', result);
        
        if (result.status === 'success') {
            return {
                status: true,
                path: result.image_path || result.path
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
            message: `Network error: ${error.message || 'Failed to upload image.'}`
        };
    }
}

/**
 * Handle add image preview
 */
function handleAddImagePreview(e) {
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
 * Handle update image preview
 */
function handleUpdateImagePreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('update_image_preview');
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else if (preview) {
        preview.style.display = 'none';
    }
}

async function uploadArticlePdf(file, articleId = 0) {
    if (!file) {
        return { status: true, path: '' };
    }

    if (!articleId || articleId <= 0) {
        return {
            status: false,
            message: 'Invalid article ID. Cannot upload PDF.'
        };
    }

    const formData = new FormData();
    formData.append('article_pdf', file);
    formData.append('article_id', articleId);

    try {
        const response = await fetch('../Actions/upload_article_pdf_action.php', {
            method: 'POST',
            body: formData
        });

        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('PDF upload HTTP error:', response.status, errorText);
            return {
                status: false,
                message: `Server error (${response.status}). ${errorText || 'Failed to upload PDF.'}`
            };
        }

        let result;
        try {
            const responseText = await response.text();
            console.log('PDF upload raw response:', responseText);
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('PDF upload JSON parse error:', parseError);
            return {
                status: false,
                message: 'Invalid response from server. Please check server logs.'
            };
        }
        
        console.log('PDF upload parsed response:', result);
        
        // Check response format - handle both 'success' string and true boolean
        const isSuccess = result.status === 'success' || result.status === true;
        
        if (isSuccess) {
            return {
                status: true,
                pdf_path: result.path || '',
                pdf_size: result.pdf_size || 0
            };
        } else {
            // Error from server
            const errorMsg = result.message || 'Failed to upload PDF to database.';
            console.error('PDF upload failed:', errorMsg, result);
            return {
                status: false,
                message: errorMsg
            };
        }
    } catch (error) {
        console.error('PDF upload network error:', error);
        return {
            status: false,
            message: `Network error: ${error.message || 'Failed to upload PDF. Please check your connection and try again.'}`
        };
    }
}

/**
 * Handle add article form submission
 */
async function handleAddArticle(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const articleTitle = form.querySelector('[name="article_title"]').value.trim();
    const articleAuthor = form.querySelector('[name="article_author"]').value.trim();
    const articleCat = form.querySelector('[name="article_cat"]').value;
    const pdfFile = form.querySelector('[name="article_pdf"]').files[0];

    // Validation
    if (!validateArticleForm(articleTitle, articleAuthor, articleCat)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    try {
        // First, add article without PDF to get article_id
        const formData = new FormData();
        formData.append('article_title', articleTitle);
        formData.append('article_author', articleAuthor);
        formData.append('article_cat', articleCat);
        formData.append('article_body', ''); // Empty initially

        let addResponse = await fetch('../Actions/add_article_action.php', {
            method: 'POST',
            body: formData
        });

        let addResult = await addResponse.json();

        if (!addResult.status) {
            throw new Error(addResult.message || 'Failed to add article.');
        }

        const articleId = addResult.article_id;
        
        // Small delay to ensure article is fully committed to database
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Track image upload status
        let imageUploadSuccess = false;
        let imageUploadError = null;
        
        // Upload image if provided
        if (imageFile) {
            // Check file size before uploading (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (imageFile.size > maxSize) {
                imageUploadError = {
                    type: 'size',
                    message: `Image too large: ${(imageFile.size / 1024 / 1024).toFixed(2)} MB (Maximum: 5 MB)`
                };
            } else {
                try {
                    const uploadResult = await uploadArticleImage(imageFile, articleId);
                    if (uploadResult.status) {
                        imageUploadSuccess = true;
                        console.log('Image uploaded successfully. Path:', uploadResult.path);
                    } else {
                        imageUploadError = {
                            type: 'upload',
                            message: uploadResult.message || 'Unknown error'
                        };
                    }
                } catch (uploadError) {
                    imageUploadError = {
                        type: 'network',
                        message: uploadError.message || 'Network error or file processing failed'
                    };
                }
            }
            
            // Show warning if image upload failed (but don't await - let it show in background)
            if (imageUploadError) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Image Upload Failed',
                    text: imageUploadError.message || 'Failed to upload image. Article will be created without image.',
                    confirmButtonColor: '#7FB685'
                });
            }
        }
        
        // Track PDF upload status
        let pdfUploadSuccess = false;
        let pdfUploadError = null;

        // Upload PDF if provided (PDF binary data is stored directly in database)
        if (pdfFile) {
            // Check file size before uploading (max 10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (pdfFile.size > maxSize) {
                pdfUploadError = {
                    type: 'size',
                    message: `File too large: ${(pdfFile.size / 1024 / 1024).toFixed(2)} MB (Maximum: 10 MB)`
                };
            } else {
                try {
                    const uploadResult = await uploadArticlePdf(pdfFile, articleId);
                    if (uploadResult.status) {
                        pdfUploadSuccess = true;
                        console.log('PDF uploaded successfully. Size:', uploadResult.pdf_size, 'bytes');
                    } else {
                        pdfUploadError = {
                            type: 'upload',
                            message: uploadResult.message || 'Unknown error'
                        };
                    }
                } catch (uploadError) {
                    pdfUploadError = {
                        type: 'network',
                        message: uploadError.message || 'Network error or file processing failed'
                    };
                }
            }
            
            // Show warning if PDF upload failed (but don't await - let it show in background)
            if (pdfUploadError) {
                // Show warning but don't block execution
                Swal.fire({
                    icon: 'warning',
                    title: 'Article Added',
                    html: `Article created successfully, but PDF upload failed:<br><br>
                           <strong>Error:</strong> ${pdfUploadError.message}<br><br>
                           The article exists in the database but the PDF was not saved. You can try uploading the PDF again by editing the article.`,
                    confirmButtonColor: '#7FB685',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Reset button state IMMEDIATELY after PDF upload attempt
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        // Close modal and show success
        closeAddModal();
        
        // Determine success message based on PDF upload status
        let successMessage = addResult.message || 'Article added successfully.';
        if (pdfFile && pdfUploadSuccess) {
            successMessage += ' PDF uploaded successfully.';
        } else if (pdfFile && !pdfUploadSuccess) {
            // Error message already shown above, just show basic success
        }
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: successMessage,
            confirmButtonColor: '#7FB685',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            form.reset();
            const preview = document.getElementById('add_pdf_preview');
            if (preview) preview.style.display = 'none';
            loadArticles();
        });

    } catch (error) {
        console.error('Add article error:', error);
        
        // ALWAYS reset button state on error
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to add article. Please try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Handle update article form submission
 */
async function handleUpdateArticle(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    const articleId = form.querySelector('[name="article_id"]').value;
    const articleTitle = form.querySelector('[name="article_title"]').value.trim();
    const articleAuthor = form.querySelector('[name="article_author"]').value.trim();
    const articleCat = form.querySelector('[name="article_cat"]').value;
    const imageFile = form.querySelector('[name="article_image"]')?.files[0];
    const pdfFile = form.querySelector('[name="article_pdf"]').files[0];
    const currentPdfPath = form.querySelector('[name="current_pdf_path"]').value;

    // Validation
    if (!validateArticleForm(articleTitle, articleAuthor, articleCat)) {
        return;
    }

    // Show loading state
    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline-block';
    submitBtn.disabled = true;

    // Track image upload status
    let imageUploadSuccess = false;
    let imageUploadError = null;

    // Track PDF upload status
    let pdfUploadSuccess = false;
    let pdfUploadError = null;

    try {
        // Upload new image if provided
        if (imageFile) {
            // Check file size before uploading (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (imageFile.size > maxSize) {
                imageUploadError = {
                    type: 'size',
                    message: `Image too large: ${(imageFile.size / 1024 / 1024).toFixed(2)} MB (Maximum: 5 MB)`
                };
            } else {
                try {
                    const uploadResult = await uploadArticleImage(imageFile, articleId);
                    if (uploadResult.status) {
                        imageUploadSuccess = true;
                        console.log('Update: Image uploaded successfully. Path:', uploadResult.path);
                    } else {
                        imageUploadError = {
                            type: 'upload',
                            message: uploadResult.message || 'Unknown error'
                        };
                    }
                } catch (uploadError) {
                    imageUploadError = {
                        type: 'network',
                        message: uploadError.message || 'Network error or file processing failed'
                    };
                }
            }
            
            // Show warning if image upload failed
            if (imageUploadError) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Image Upload Failed',
                    text: imageUploadError.message || 'Failed to upload image. Article will be updated without new image.',
                    confirmButtonColor: '#7FB685'
                });
            }
        }

        // Upload new PDF if provided (PDF binary data is stored directly in database)
        if (pdfFile) {
            console.log('Update: PDF file selected. Size:', pdfFile.size, 'bytes, Article ID:', articleId);
            
            // Check file size before uploading (max 10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (pdfFile.size > maxSize) {
                pdfUploadError = {
                    type: 'size',
                    message: `File too large: ${(pdfFile.size / 1024 / 1024).toFixed(2)} MB (Maximum: 10 MB)`
                };
            } else {
                try {
                    console.log('Update: Attempting PDF upload for article ID:', articleId);
                    const uploadResult = await uploadArticlePdf(pdfFile, articleId);
                    console.log('Update: PDF upload result:', uploadResult);
                    
                    if (uploadResult.status) {
                        pdfUploadSuccess = true;
                        console.log('Update: PDF uploaded successfully. Size:', uploadResult.pdf_size, 'bytes');
                    } else {
                        pdfUploadError = {
                            type: 'upload',
                            message: uploadResult.message || 'Unknown error'
                        };
                        console.error('Update: PDF upload failed:', pdfUploadError.message);
                    }
                } catch (uploadError) {
                    pdfUploadError = {
                        type: 'network',
                        message: uploadError.message || 'Network error or file processing failed'
                    };
                    console.error('Update: PDF upload exception:', uploadError);
                }
            }
            
            // Show error if PDF upload failed
            if (pdfUploadError) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'PDF Upload Failed',
                    html: `Failed to upload PDF to database:<br><br>
                           <strong>Error:</strong> ${pdfUploadError.message}<br><br>
                           The article will be updated, but the PDF was not saved. You can try uploading the PDF again.`,
                    confirmButtonColor: '#7FB685',
                    confirmButtonText: 'OK'
                });
            }
        }

        const formData = new FormData();
        formData.append('article_id', articleId);
        formData.append('article_title', articleTitle);
        formData.append('article_author', articleAuthor);
        formData.append('article_cat', articleCat);
        // Don't send article_body in update - it's handled separately via PDF upload

        const response = await fetch('../Actions/update_article_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        // Reset button state
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;

        if (result.status) {
            closeUpdateModal();
            
            // Determine success message based on PDF upload status
            let successMessage = result.message || 'Article updated successfully.';
            if (pdfFile && pdfUploadSuccess) {
                successMessage += ' PDF uploaded successfully.';
            } else if (pdfFile && !pdfUploadSuccess && !pdfUploadError) {
                // PDF was selected but upload wasn't attempted (shouldn't happen)
                successMessage += ' (PDF upload status unknown)';
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: successMessage,
                confirmButtonColor: '#7FB685',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                loadArticles();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update article. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Update article error:', error);
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open edit modal with article data
 */
async function openEditModal(articleId) {
    try {
        const response = await fetch(`../Actions/fetch_article_action.php?article_id=${articleId}`);
        const result = await response.json();

        if (result.status && result.article) {
            const article = result.article;
            const form = document.getElementById('updateArticleForm');
            
            if (form) {
                form.querySelector('[name="article_id"]').value = article.article_id;
                form.querySelector('[name="article_title"]').value = article.article_title;
                form.querySelector('[name="article_author"]').value = article.article_author;
                form.querySelector('[name="article_cat"]').value = article.article_cat;
                form.querySelector('[name="current_pdf_path"]').value = article.article_body || '';

                // Set image preview
                const imagePreview = document.getElementById('update_image_preview');
                if (imagePreview && article.article_image) {
                    // Database stores paths as ../../uploads/... which is correct for Admin/ folder
                    let imageSrc = article.article_image;
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

                // Set PDF preview
                const preview = document.getElementById('update_pdf_preview');
                if (preview && (article.has_pdf == 1 || article.has_pdf === true)) {
                    preview.textContent = `Current: PDF document is attached`;
                    preview.style.display = 'block';
                } else if (preview) {
                    preview.style.display = 'none';
                }
            }

            // Show modal
            document.getElementById('updateArticleModal').style.display = 'block';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Article not found.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Error loading article:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * View article details
 */
async function viewArticle(articleId) {
    try {
        const response = await fetch(`../Actions/fetch_article_action.php?article_id=${articleId}`);
        const result = await response.json();

        if (result.status && result.article) {
            openViewModal(result.article);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Article not found.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('View article error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Open view modal with article information
 */
function openViewModal(article) {
    const modal = document.getElementById('viewArticleModal');
    if (!modal) return;

    document.getElementById('view_article_id').textContent = article.article_id;
    document.getElementById('view_article_title').textContent = article.article_title;
    document.getElementById('view_article_author').textContent = article.article_author;
    document.getElementById('view_article_cat').textContent = article.cat_name || 'N/A';
    document.getElementById('view_article_views').textContent = article.article_views || 0;
    document.getElementById('view_date_added').textContent = article.date_added ? formatDate(article.date_added) : 'N/A';
    
    const pdfLink = document.getElementById('view_article_pdf_link');
    if (pdfLink && (article.has_pdf == 1 || article.has_pdf === true)) {
        pdfLink.href = '../Actions/get_article_pdf_action.php?article_id=' + article.article_id;
        pdfLink.style.display = 'inline-block';
        pdfLink.textContent = 'View PDF';
    } else if (pdfLink) {
        pdfLink.style.display = 'none';
    }

    modal.style.display = 'block';
}

/**
 * Delete an article
 */
async function deleteArticle(articleId, articleTitle) {
    const result = await Swal.fire({
        title: 'Delete Article?',
        text: `Are you sure you want to delete "${articleTitle}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d4183d',
        cancelButtonColor: '#7FB685',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('article_id', articleId);

        const response = await fetch('../Actions/delete_article_action.php', {
            method: 'POST',
            body: formData
        });

        const deleteResult = await response.json();

        if (deleteResult.status) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: deleteResult.message || 'Article deleted successfully.',
                confirmButtonColor: '#7FB685',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadArticles();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: deleteResult.message || 'Failed to delete article. Please try again.',
                confirmButtonColor: '#7FB685'
            });
        }
    } catch (error) {
        console.error('Delete article error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error. Please check your connection and try again.',
            confirmButtonColor: '#7FB685'
        });
    }
}

/**
 * Validate article form
 */
function validateArticleForm(articleTitle, articleAuthor, articleCat) {
    if (!articleTitle || articleTitle.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Article title is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (articleTitle.length < 3) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Article title must be at least 3 characters long.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!articleAuthor || articleAuthor.trim().length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Article author is required.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (articleAuthor.length < 2) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Article author must be at least 2 characters long.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    if (!articleCat || articleCat <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please select a category.',
            confirmButtonColor: '#7FB685'
        });
        return false;
    }

    return true;
}

/**
 * Close add modal
 */
function closeAddModal() {
    const modal = document.getElementById('addArticleModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close update modal
 */
function closeUpdateModal() {
    const modal = document.getElementById('updateArticleModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('viewArticleModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Handle search
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#articlesTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Utility: Format date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

/**
 * Utility: Debounce function
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

