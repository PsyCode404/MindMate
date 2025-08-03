<?php 
$page_title = "MindMate Chat";
$additional_css = '<link rel="stylesheet" href="../css/chat.css">';
include '../includes/header.php'; 
?>

<div class="chat-container">
    <div class="chat-content">
        <div class="chat-header">
            <h1><i class="fas fa-comments"></i>MindMate Chat</h1>
            <div class="header-controls">
                <button id="clear-chat" class="clear-chat" title="Clear conversation">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button id="theme-toggle" class="theme-toggle" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be inserted here -->
        </div>

        <div class="typing-indicator hidden" id="typing-indicator">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <div class="chat-input-container">
            <div class="input-wrapper">
                <textarea 
                    id="chat-input" 
                    placeholder="Type your message here..."
                    rows="1"
                    maxlength="500"
                ></textarea>
                <div class="char-counter" id="char-counter">0/500</div>
            </div>
            <button id="send-button" class="send-button" disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script src="../js/chat.js"></script>

<?php include '../includes/footer.php'; ?>
