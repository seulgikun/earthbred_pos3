// manager-ai.js

document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatWindow = document.getElementById('chatWindow');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function appendMessage(text, isUser) {
        const wrapper = document.createElement('div');
        wrapper.className = `chat-message ${isUser ? 'user-message' : 'bot-message'}`;

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.textContent = isUser ? '👤' : '🤖';

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${isUser ? 'user-bubble' : 'bot-bubble'}`;
        
        if (isUser) {
            bubble.textContent = text;
        } else {
            // Use marked.js if available
            if (typeof marked !== 'undefined') {
                bubble.innerHTML = marked.parse(text);
            } else {
                bubble.textContent = text;
            }
        }

        wrapper.appendChild(avatar);
        wrapper.appendChild(bubble);
        chatWindow.appendChild(wrapper);

        // Scroll to bottom
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function addLoadingBubble() {
        const wrapper = document.createElement('div');
        wrapper.className = 'chat-message bot-message loading-message';
        wrapper.id = 'loadingBubble';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.textContent = '🤖';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble bot-bubble';
        bubble.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';

        wrapper.appendChild(avatar);
        wrapper.appendChild(bubble);
        chatWindow.appendChild(wrapper);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function removeLoadingBubble() {
        const loadingBubble = document.getElementById('loadingBubble');
        if (loadingBubble) {
            loadingBubble.remove();
        }
    }

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        // Append user message
        appendMessage(message, true);
        chatInput.value = '';

        // Add loading indicator
        addLoadingBubble();

        // Call API
        fetch(BASE + '/api/manager/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            removeLoadingBubble();
            if (data.success) {
                appendMessage(data.reply, false);
            } else {
                appendMessage(data.message || 'Error communicating with AI.', false);
            }
        })
        .catch(err => {
            removeLoadingBubble();
            appendMessage('A network error occurred. Please try again.', false);
        });
    });
});
