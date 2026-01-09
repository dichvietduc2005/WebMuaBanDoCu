<?php
// ChatWidget Component - Mobile-First Responsive Version
// Optimized for both desktop and mobile devices
?>

<!-- Chat Trigger Button -->
<div id="chat-trigger" 
     class="position-fixed"
     style="bottom: 20px; right: 20px; z-index: 9990; cursor: pointer; transition: all 0.3s;"
     onclick="toggle_chat_widget()">
    <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-lg chat-trigger-btn"
         style="width: 56px; height: 56px; background: linear-gradient(135deg, #2563eb, #4f46e5);">
        <i class="fas fa-comment-dots" style="font-size: 24px;"></i>
        <!-- Notification Badge -->
        <span class="position-absolute border border-white rounded-circle bg-danger"
              style="top: 0; right: 0; width: 14px; height: 14px;"></span>
    </div>
</div>

<!-- Chat Widget Container -->
<div id="chat-widget" 
     class="position-fixed bg-white shadow-lg d-flex flex-column overflow-hidden chat-widget-container"
     style="z-index: 9999; transform-origin: bottom right; transition: all 0.3s ease-out; opacity: 0; pointer-events: none; transform: scale(0);">
    
    <!-- Header -->
    <div class="p-3 d-flex align-items-center justify-content-between text-white chat-header"
         style="background: linear-gradient(135deg, #2563eb, #4f46e5); flex-shrink: 0;">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center border border-white-50"
                 style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); flex-shrink: 0;">
                <i class="fas fa-headset"></i>
            </div>
            <div>
                <h6 class="m-0 fw-bold" style="font-size: 15px;">Hỗ trợ trực tuyến</h6>
                <small class="d-flex align-items-center gap-1" style="font-size: 11px; opacity: 0.9;">
                    <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                    Thường trả lời ngay
                </small>
            </div>
        </div>
        <button onclick="toggle_chat_widget()" class="btn btn-sm text-white rounded-circle p-0 d-flex align-items-center justify-content-center" 
                style="width: 36px; height: 36px; background: rgba(255,255,255,0.2);">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Messages Area -->
    <div id="ChatMessagesContainer" class="flex-grow-1 overflow-auto p-3 bg-light d-flex flex-column gap-3">
        <!-- Welcome Message -->
        <div class="align-self-start bg-white text-secondary rounded-3 p-3 shadow-sm border" style="max-width: 85%; border-top-left-radius: 2px !important;">
            <small class="d-block mb-1 fw-bold">HIHand Support</small>
            Xin chào! 👋<br>
            Chúng tôi có thể giúp gì cho bạn hôm nay?
        </div>

        <!-- Dynamic Messages Container -->
        <div id="messages" class="d-flex flex-column gap-2">
            <!-- JS will append messages here -->
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-2 p-sm-3 bg-white border-top" style="flex-shrink: 0;">
        <div class="d-flex align-items-end gap-2 bg-light rounded-3 p-2 border">
            <textarea id="chat-input" 
                      rows="1"
                      class="form-control border-0 bg-transparent shadow-none p-1" 
                      style="resize: none; font-size: 16px; max-height: 80px; min-height: 38px;"
                      placeholder="Nhập tin nhắn..."
                      onkeypress="on_key_press(event)"></textarea>
            
            <button onclick="send_messages()" 
                    class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width: 40px; height: 40px;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<!-- Scripts -->
<?php
use App\Core\UrlHelper;
?>
<script src="<?php echo UrlHelper::js('user_chat_system.js'); ?>"></script>
<style>
    /* =====================================================
       CHAT WIDGET - MOBILE RESPONSIVE STYLES
       ===================================================== */
    
    /* Desktop styles (default) */
    .chat-widget-container {
        bottom: 24px;
        right: 24px;
        width: 380px;
        height: 520px;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .chat-header {
        border-radius: 16px 16px 0 0;
    }
    
    /* Tablet styles */
    @media (max-width: 768px) {
        .chat-widget-container {
            width: 340px;
            height: 480px;
            bottom: 20px;
            right: 16px;
        }
        
        #chat-trigger {
            bottom: 16px !important;
            right: 16px !important;
        }
    }
    
    /* Mobile styles - Full screen */
    @media (max-width: 480px) {
        .chat-widget-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            max-height: 100vh !important;
            max-height: 100dvh !important; /* Dynamic viewport height for mobile browsers */
            border-radius: 0 !important;
            border: none !important;
        }
        
        .chat-header {
            border-radius: 0 !important;
            padding: 12px 16px !important;
            /* Safe area for notch/status bar */
            padding-top: max(12px, env(safe-area-inset-top)) !important;
        }
        
        #chat-trigger {
            bottom: 16px !important;
            right: 16px !important;
        }
        
        .chat-trigger-btn {
            width: 52px !important;
            height: 52px !important;
        }
        
        #chat-input {
            font-size: 16px !important; /* Prevent zoom on iOS */
        }
        
        /* Input area safe area for bottom navigation */
        .chat-widget-container > div:last-child {
            padding-bottom: max(12px, env(safe-area-inset-bottom)) !important;
        }
    }
    
    /* =====================================================
       WIDGET TOGGLE ANIMATIONS
       ===================================================== */
    
    #chat-widget.scale-100.opacity-100 {
        opacity: 1 !important;
        pointer-events: auto !important;
        transform: scale(1) !important;
    }
    
    #chat-widget.scale-0.opacity-0 {
        opacity: 0 !important;
        pointer-events: none !important;
        transform: scale(0) !important;
    }

    #chat-trigger.scale-0.opacity-0 {
        opacity: 0 !important;
        transform: scale(0) !important;
        pointer-events: none !important;
    }

    /* =====================================================
       SCROLLBAR STYLING
       ===================================================== */
    
    #ChatMessagesContainer::-webkit-scrollbar {
        width: 6px;
    }
    #ChatMessagesContainer::-webkit-scrollbar-track {
        background: transparent;
    }
    #ChatMessagesContainer::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }
    
    /* Hide scrollbar on mobile for cleaner look */
    @media (max-width: 480px) {
        #ChatMessagesContainer::-webkit-scrollbar {
            width: 0;
            display: none;
        }
        #ChatMessagesContainer {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    }
    
    /* =====================================================
       MESSAGE BUBBLE STYLES
       ===================================================== */
    
    .user-message {
        align-self: flex-end;
        background-color: #2563eb;
        color: white;
        border-radius: 1rem;
        border-top-right-radius: 0.125rem;
        padding: 0.625rem 1rem;
        max-width: 85%;
        font-size: 0.9375rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        margin-bottom: 0.5rem;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    .admin-message {
        align-self: flex-start;
        background-color: white;
        color: #374151;
        border-radius: 1rem;
        border-top-left-radius: 0.125rem;
        padding: 0.625rem 1rem;
        max-width: 85%;
        font-size: 0.9375rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
        margin-bottom: 0.5rem;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    /* Mobile message adjustments */
    @media (max-width: 480px) {
        .user-message,
        .admin-message {
            max-width: 88%;
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }
    }
    
    /* =====================================================
       TOUCH INTERACTION IMPROVEMENTS
       ===================================================== */
    
    @media (hover: none) and (pointer: coarse) {
        /* Touch devices */
        .chat-trigger-btn:active {
            transform: scale(0.95);
        }
        
        #chat-widget button:active {
            opacity: 0.8;
        }
    }
</style>
