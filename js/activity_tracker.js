/**
 * User Activity Tracker
 * Tracks time spent on pages, page views, and user interactions for recommendations
 */

class ActivityTracker {
    constructor() {
        this.startTime = Date.now();
        this.pageType = this.detectPageType();
        this.contentId = this.getContentId();
        this.categoryId = null;
        this.isActive = true;
        this.visibilityChangeHandler = null;
        this.heartbeatInterval = null;
        
        // Initialize tracking
        this.init();
    }
    
    /**
     * Detect what type of page this is
     */
    detectPageType() {
        const path = window.location.pathname;
        
        if (path.includes('single_article.php')) {
            return 'article';
        } else if (path.includes('single_product.php')) {
            return 'product';
        } else if (path.includes('wellness_hub.php')) {
            return 'page';
        } else if (path.includes('shop.php')) {
            return 'page';
        } else {
            return 'page';
        }
    }
    
    /**
     * Get content ID from URL or page
     */
    getContentId() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        
        if (id) {
            return parseInt(id);
        }
        
        // Try to get from data attribute
        const contentElement = document.querySelector('[data-content-id]');
        if (contentElement) {
            return parseInt(contentElement.getAttribute('data-content-id'));
        }
        
        return 0;
    }
    
    /**
     * Initialize tracking
     */
    init() {
        // Track page view immediately
        this.recordPageView();
        
        // Set up visibility change tracking (when user switches tabs)
        this.setupVisibilityTracking();
        
        // Set up heartbeat to send time updates periodically
        this.setupHeartbeat();
        
        // Track time when page is unloaded
        window.addEventListener('beforeunload', () => {
            this.recordTimeSpent();
        });
        
        // Track scroll depth (engagement indicator)
        this.trackScrollDepth();
    }
    
    /**
     * Record a page view
     */
    async recordPageView() {
        try {
            const response = await fetch('../Actions/record_activity_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity_type: 'page_view',
                    content_type: this.pageType,
                    content_id: this.contentId,
                    time_spent_seconds: 0
                })
            });
            
            const result = await response.json();
            
            // If category_id is returned, store it
            if (result.category_id) {
                this.categoryId = result.category_id;
            }
        } catch (error) {
            console.error('Error recording page view:', error);
        }
    }
    
    /**
     * Record time spent on page
     */
    async recordTimeSpent() {
        if (!this.isActive) return;
        
        const timeSpent = Math.floor((Date.now() - this.startTime) / 1000);
        
        if (timeSpent < 3) return; // Don't record if less than 3 seconds
        
        try {
            await fetch('../Actions/record_activity_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity_type: 'time_spent',
                    content_type: this.pageType,
                    content_id: this.contentId,
                    category_id: this.categoryId,
                    time_spent_seconds: timeSpent
                })
            });
        } catch (error) {
            console.error('Error recording time spent:', error);
        }
    }
    
    /**
     * Set up visibility change tracking
     * Pauses tracking when user switches tabs
     */
    setupVisibilityTracking() {
        this.visibilityChangeHandler = () => {
            if (document.hidden) {
                // User switched away - record time spent so far
                this.recordTimeSpent();
                this.isActive = false;
            } else {
                // User came back - reset start time
                this.startTime = Date.now();
                this.isActive = true;
            }
        };
        
        document.addEventListener('visibilitychange', this.visibilityChangeHandler);
    }
    
    /**
     * Set up heartbeat to send periodic updates
     * Sends time spent every 30 seconds
     */
    setupHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            if (this.isActive && !document.hidden) {
                this.recordTimeSpent();
                // Reset start time for next interval
                this.startTime = Date.now();
            }
        }, 30000); // Every 30 seconds
    }
    
    /**
     * Track scroll depth as engagement indicator
     */
    trackScrollDepth() {
        let maxScroll = 0;
        let scrollCheckInterval = null;
        
        const checkScroll = () => {
            const scrollPercent = Math.round(
                (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100
            );
            
            if (scrollPercent > maxScroll) {
                maxScroll = scrollPercent;
                
                // Record milestones (25%, 50%, 75%, 100%)
                if (maxScroll >= 25 && maxScroll < 50) {
                    this.recordEngagement('scroll_25');
                } else if (maxScroll >= 50 && maxScroll < 75) {
                    this.recordEngagement('scroll_50');
                } else if (maxScroll >= 75 && maxScroll < 100) {
                    this.recordEngagement('scroll_75');
                } else if (maxScroll >= 100) {
                    this.recordEngagement('scroll_100');
                    // Stop tracking once user reaches bottom
                    if (scrollCheckInterval) {
                        clearInterval(scrollCheckInterval);
                    }
                }
            }
        };
        
        scrollCheckInterval = setInterval(checkScroll, 1000);
    }
    
    /**
     * Record engagement event
     */
    async recordEngagement(eventType) {
        try {
            await fetch('../Actions/record_activity_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity_type: 'page_view', // Changed from 'engagement' to valid ENUM value
                    content_type: this.pageType,
                    content_id: this.contentId,
                    category_id: this.categoryId,
                    engagement_type: eventType
                })
            });
        } catch (error) {
            console.error('Error recording engagement:', error);
        }
    }
    
    /**
     * Clean up tracking
     */
    destroy() {
        if (this.visibilityChangeHandler) {
            document.removeEventListener('visibilitychange', this.visibilityChangeHandler);
        }
        
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
        
        this.recordTimeSpent();
    }
}

// Auto-initialize tracker when DOM is ready
let activityTracker = null;

document.addEventListener('DOMContentLoaded', function() {
    // Only track if user is on a content page (article, product, etc.)
    const path = window.location.pathname;
    if (path.includes('single_article.php') || 
        path.includes('single_product.php') || 
        path.includes('wellness_hub.php') || 
        path.includes('shop.php')) {
        activityTracker = new ActivityTracker();
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (activityTracker) {
        activityTracker.destroy();
    }
});

