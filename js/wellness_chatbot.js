/**
 * Wellness 360 AI Chatbot Widget
 * Provides live customer support and wellness guidance
 */

(function() {
    'use strict';

    const WellnessChatbot = {
        isOpen: false,
        messageCount: 0,
        maxFreeQueries: 5, // Increased for better user experience
        customerId: null,

        init() {
            // Get customer ID if logged in
            this.customerId = this.getCustomerId();
            this.createWidget();
            this.attachEventListeners();
        },

        getCustomerId() {
            // Try to get customer ID from session (if available in global scope)
            if (typeof window.currentUserId !== 'undefined') {
                return window.currentUserId;
            }
            return null;
        },

        createWidget() {
            const widgetHTML = `
                <div id="wellnessChatbotContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: 'Poppins', Arial, sans-serif;">
                    <!-- Chat Button -->
                    <button id="wellnessChatbotToggle" style="
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, #7FB685, #6FA375);
                        color: white;
                        border: none;
                        box-shadow: 0 4px 12px rgba(127, 182, 133, 0.4);
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 24px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-comments"></i>
                    </button>
                    <!-- Chat Window -->
                    <div id="wellnessChatbotWindow" style="
                        display: none;
                        position: absolute;
                        bottom: 80px;
                        right: 0;
                        width: 380px;
                        max-width: calc(100vw - 40px);
                        height: 550px;
                        max-height: calc(100vh - 100px);
                        background: white;
                        border-radius: 15px;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                        flex-direction: column;
                        overflow: hidden;
                    ">
                        <!-- Header -->
                        <div style="
                            background: linear-gradient(135deg, #7FB685, #6FA375);
                            color: white;
                            padding: 18px 20px;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        ">
                            <div>
                                <h5 style="margin: 0; font-size: 16px; font-weight: 600;">
                                    <i class="fas fa-robot" style="margin-right: 8px;"></i>AI Wellness Assistant
                                </h5>
                                <small style="opacity: 0.9; font-size: 12px;">Your personalized wellness guide</small>
                            </div>
                            <button id="wellnessChatbotClose" style="
                                background: transparent;
                                border: none;
                                color: white;
                                font-size: 20px;
                                cursor: pointer;
                                padding: 5px;
                                width: 30px;
                                height: 30px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                border-radius: 50%;
                                transition: background 0.2s;
                            ">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- Messages Area -->
                        <div id="wellnessChatbotMessages" style="
                            flex: 1;
                            padding: 20px;
                            overflow-y: auto;
                            background: #f8f9fa;
                        ">
                            <div class="wellness-chat-message bot-message" style="
                                background: white;
                                padding: 15px;
                                border-radius: 15px;
                                margin-bottom: 15px;
                                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                            ">
                                <p style="margin: 0 0 10px 0; color: #2C3E35; font-weight: 500;">
                                    <i class="fas fa-robot" style="color: #7FB685; margin-right: 8px;"></i>
                                    Hello! I'm your AI Wellness Assistant. I can help you with:
                                </p>
                                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #666; font-size: 14px;">
                                    <li>Wellness articles and resources</li>
                                    <li>Product recommendations</li>
                                    <li>Workshop information</li>
                                    <li>Community features</li>
                                    <li>General wellness questions</li>
                                </ul>
                                <p style="margin: 10px 0 0 0; color: #666; font-size: 12px;">
                                    <i class="fas fa-info-circle" style="color: #7FB685; margin-right: 5px;"></i>
                                    Ask me anything about wellness!
                                </p>
                            </div>
                        </div>
                        <!-- Input Area -->
                        <div style="
                            padding: 15px;
                            background: white;
                            border-top: 1px solid #e0e0e0;
                        ">
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="wellnessChatbotInput" placeholder="Ask about wellness..." style="
                                    flex: 1;
                                    padding: 12px 15px;
                                    border: 2px solid #e0e0e0;
                                    border-radius: 25px;
                                    outline: none;
                                    font-size: 14px;
                                    font-family: 'Poppins', Arial, sans-serif;
                                    transition: border-color 0.2s;
                                ">
                                <button id="wellnessChatbotSend" style="
                                    width: 45px;
                                    height: 45px;
                                    border-radius: 50%;
                                    background: linear-gradient(135deg, #7FB685, #6FA375);
                                    color: white;
                                    border: none;
                                    cursor: pointer;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    transition: transform 0.2s;
                                ">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div id="wellnessQueryLimitWarning" style="
                                margin-top: 10px;
                                padding: 10px;
                                background: #fff3cd;
                                border-radius: 8px;
                                font-size: 12px;
                                color: #856404;
                                display: none;
                            ">
                                <i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
                                You've reached the free query limit. <a href="../View/profile.php" style="color: #7FB685; font-weight: bold; text-decoration: none;">Upgrade to Premium</a> for unlimited access.
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add widget to body
            const div = document.createElement('div');
            div.innerHTML = widgetHTML;
            document.body.appendChild(div);

            // Add styles
            const style = document.createElement('style');
            style.textContent = `
                #wellnessChatbotToggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 16px rgba(127, 182, 133, 0.5);
                }
                #wellnessChatbotClose:hover {
                    background: rgba(255, 255, 255, 0.2);
                }
                #wellnessChatbotInput:focus {
                    border-color: #7FB685;
                }
                #wellnessChatbotSend:hover {
                    transform: scale(1.05);
                }
                .wellness-chat-message {
                    animation: fadeIn 0.3s ease;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .user-message {
                    background: linear-gradient(135deg, #7FB685, #6FA375) !important;
                    color: white !important;
                    margin-left: auto;
                    max-width: 80%;
                }
                .bot-message {
                    background: white;
                    color: #2C3E35;
                    max-width: 85%;
                }
                #wellnessChatbotMessages::-webkit-scrollbar {
                    width: 6px;
                }
                #wellnessChatbotMessages::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }
                #wellnessChatbotMessages::-webkit-scrollbar-thumb {
                    background: #7FB685;
                    border-radius: 3px;
                }
                #wellnessChatbotMessages::-webkit-scrollbar-thumb:hover {
                    background: #6FA375;
                }
            `;
            document.head.appendChild(style);
        },

        attachEventListeners() {
            const toggle = document.getElementById('wellnessChatbotToggle');
            const close = document.getElementById('wellnessChatbotClose');
            const send = document.getElementById('wellnessChatbotSend');
            const input = document.getElementById('wellnessChatbotInput');

            if (toggle) {
                toggle.addEventListener('click', () => this.toggleChat());
            }
            if (close) {
                close.addEventListener('click', () => this.closeChat());
            }
            if (send) {
                send.addEventListener('click', () => this.sendMessage());
            }
            if (input) {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.sendMessage();
                    }
                });
            }
        },

        toggleChat() {
            const window = document.getElementById('wellnessChatbotWindow');
            if (window) {
                this.isOpen = !this.isOpen;
                window.style.display = this.isOpen ? 'flex' : 'none';
                
                if (this.isOpen) {
                    const input = document.getElementById('wellnessChatbotInput');
                    if (input) {
                        setTimeout(() => input.focus(), 100);
                    }
                    // Scroll to bottom
                    const messages = document.getElementById('wellnessChatbotMessages');
                    if (messages) {
                        messages.scrollTop = messages.scrollHeight;
                    }
                }
            }
        },

        closeChat() {
            this.isOpen = false;
            const window = document.getElementById('wellnessChatbotWindow');
            if (window) {
                window.style.display = 'none';
            }
        },

        async sendMessage() {
            const input = document.getElementById('wellnessChatbotInput');
            const warning = document.getElementById('wellnessQueryLimitWarning');
            
            if (!input || !input.value.trim()) return;
            
            const message = input.value.trim();
            input.value = '';

            // Check query limit for non-premium users
            const isPremium = await this.checkPremiumStatus();
            if (!isPremium && this.messageCount >= this.maxFreeQueries) {
                if (warning) {
                    warning.style.display = 'block';
                }
                this.addMessage('You\'ve reached the free query limit. Please upgrade to Premium for unlimited access!', 'bot');
                return;
            }

            this.addMessage(message, 'user');
            this.messageCount++;

            // Show typing indicator
            this.showTypingIndicator();

            // Get AI response
            setTimeout(async () => {
                const response = await this.generateResponse(message);
                this.hideTypingIndicator();
                this.addMessage(response, 'bot');
            }, 800);
        },

        async generateResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Wellness Hub & Articles
            if (lowerMessage.includes('article') || lowerMessage.includes('wellness hub') || lowerMessage.includes('read') || lowerMessage.includes('content')) {
                return 'Great! Our Wellness Hub has articles on mental health, nutrition, fitness, and more. You can browse by category or search for specific topics. Would you like recommendations based on your interests?';
            }
            
            // Products & Shop
            if (lowerMessage.includes('product') || lowerMessage.includes('shop') || lowerMessage.includes('buy') || lowerMessage.includes('purchase')) {
                return 'Our shop offers a variety of wellness products from verified vendors. You can browse by category, filter by price, and read reviews. All products are quality-checked and verified. Need help finding something specific?';
            }
            
            // Workshops
            if (lowerMessage.includes('workshop') || lowerMessage.includes('event') || lowerMessage.includes('class') || lowerMessage.includes('session')) {
                return 'We host workshops on various wellness topics! You can find upcoming workshops in the Community section. Topics include fitness, nutrition, mental health, and more. Would you like to see what\'s coming up?';
            }
            
            // Community
            if (lowerMessage.includes('community') || lowerMessage.includes('discussion') || lowerMessage.includes('forum') || lowerMessage.includes('connect')) {
                return 'Our community is a great place to connect with others on their wellness journey! You can join discussions, share experiences, and participate in workshops. All discussions are anonymous and supportive.';
            }
            
            // Mental Health
            if (lowerMessage.includes('mental health') || lowerMessage.includes('stress') || lowerMessage.includes('anxiety') || lowerMessage.includes('depression') || lowerMessage.includes('mindfulness')) {
                return 'Mental health is important! We have articles and resources on managing stress, anxiety, mindfulness, and more. You can also find workshops and community discussions on these topics. Remember, it\'s okay to seek help.';
            }
            
            // Nutrition
            if (lowerMessage.includes('nutrition') || lowerMessage.includes('diet') || lowerMessage.includes('meal') || lowerMessage.includes('food') || lowerMessage.includes('eat')) {
                return 'Nutrition is key to wellness! We have articles on healthy eating, meal planning, and nutrition tips. Our shop also has quality food products from verified vendors. Need specific dietary advice?';
            }
            
            // Fitness
            if (lowerMessage.includes('fitness') || lowerMessage.includes('exercise') || lowerMessage.includes('workout') || lowerMessage.includes('gym') || lowerMessage.includes('training')) {
                return 'Fitness is essential for wellness! Check out our fitness articles and workshops. We have resources for all fitness levels, from beginners to advanced. You can also find fitness products in our shop.';
            }
            
            // Account/Profile
            if (lowerMessage.includes('account') || lowerMessage.includes('profile') || lowerMessage.includes('login') || lowerMessage.includes('sign up') || lowerMessage.includes('register')) {
                return 'You can create an account to access personalized recommendations, track your wellness journey, save favorites, and join the community. Sign up is free and takes just a minute!';
            }
            
            // Contact/Support
            if (lowerMessage.includes('contact') || lowerMessage.includes('support') || lowerMessage.includes('help') || lowerMessage.includes('email') || lowerMessage.includes('phone')) {
                return 'You can reach us at wellnessallround@gmail.com or call 0204567321. We\'re here Monday-Friday, 9 AM - 6 PM. You can also use the contact form on this page for detailed inquiries.';
            }
            
            // Delivery/Shipping
            if (lowerMessage.includes('delivery') || lowerMessage.includes('ship') || lowerMessage.includes('deliver') || lowerMessage.includes('shipping')) {
                return 'We offer delivery services for products purchased through our shop. Delivery times and costs vary by location. Check product pages for specific delivery information.';
            }
            
            // Price/Cost
            if (lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('expensive') || lowerMessage.includes('afford')) {
                return 'Our products are priced fairly to support both vendors and customers. Prices vary by product and vendor. You can filter products by price range in the shop. We also offer special deals and discounts!';
            }
            
            // Greetings
            if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey') || lowerMessage.includes('greetings')) {
                return 'Hello! Welcome to Wellness 360! I\'m here to help you with wellness articles, products, workshops, community features, and general wellness questions. What would you like to know?';
            }
            
            // Thank you
            if (lowerMessage.includes('thank') || lowerMessage.includes('thanks')) {
                return 'You\'re welcome! I\'m here anytime you need help. Feel free to ask about wellness topics, products, workshops, or anything else related to your wellness journey!';
            }
            
            // Default response
            return 'That\'s a great question! I can help you with wellness articles, product recommendations, workshop information, community features, and general wellness guidance. Could you provide more details about what you\'d like to know?';
        },

        async checkPremiumStatus() {
            // In production, check user's premium status from API
            // For now, return false (free user)
            // You can implement this by calling an API endpoint
            if (this.customerId) {
                try {
                    const response = await fetch(`../Actions/check_premium_status_action.php?customer_id=${this.customerId}`);
                    const data = await response.json();
                    return data.isPremium || false;
                } catch (error) {
                    return false;
                }
            }
            return false;
        },

        addMessage(text, type) {
            const messages = document.getElementById('wellnessChatbotMessages');
            if (!messages) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = `wellness-chat-message ${type}-message`;
            
            if (type === 'user') {
                messageDiv.style.cssText = 'background: linear-gradient(135deg, #7FB685, #6FA375); color: white; padding: 12px 15px; border-radius: 15px; margin-bottom: 15px; margin-left: auto; max-width: 80%;';
            } else {
                messageDiv.style.cssText = 'background: white; color: #2C3E35; padding: 12px 15px; border-radius: 15px; margin-bottom: 15px; max-width: 85%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);';
            }
            
            messageDiv.innerHTML = `<p style="margin: 0; line-height: 1.5;">${this.escapeHtml(text)}</p>`;
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        },

        showTypingIndicator() {
            const messages = document.getElementById('wellnessChatbotMessages');
            if (!messages) return;

            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'wellness-chat-message bot-message';
            typingDiv.style.cssText = 'background: white; color: #2C3E35; padding: 12px 15px; border-radius: 15px; margin-bottom: 15px; max-width: 85%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);';
            typingDiv.innerHTML = '<p style="margin: 0;"><i class="fas fa-robot" style="color: #7FB685; margin-right: 8px;"></i><span class="typing-dots"><span>.</span><span>.</span><span>.</span></span></p>';
            messages.appendChild(typingDiv);
            messages.scrollTop = messages.scrollHeight;
        },

        hideTypingIndicator() {
            const typing = document.getElementById('typingIndicator');
            if (typing) {
                typing.remove();
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => WellnessChatbot.init());
    } else {
        WellnessChatbot.init();
    }

    // Show introduction on first visit (optional)
    if (!localStorage.getItem('wellness_chatbot_intro_shown')) {
        setTimeout(() => {
            const intro = confirm('Meet Your AI Wellness Assistant! I can help you with wellness articles, products, workshops, and answer your wellness questions. Would you like to try it?');
            if (intro) {
                WellnessChatbot.toggleChat();
            }
            localStorage.setItem('wellness_chatbot_intro_shown', 'true');
        }, 3000);
    }

})();

