// manager-ai.js
// Chat history is persisted in sessionStorage so navigating away and back
// keeps the conversation intact for the duration of the browser session.
// Each role (owner, manager, etc.) gets its own isolated history key.

const _role = (localStorage.getItem('userRole') || 'manager').toLowerCase().trim();
const CHAT_STORAGE_KEY = `earthbred_ai_chat_history_${_role}`;

document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    const chatForm   = document.getElementById('chatForm');
    const chatInput  = document.getElementById('chatInput');
    const chatWindow = document.getElementById('chatWindow');
    const csrfMeta   = document.querySelector('meta[name="csrf-token"]');
    const csrfToken  = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // ─── History helpers ─────────────────────────────────────────────────────

    function loadHistory() {
        try {
            return JSON.parse(sessionStorage.getItem(CHAT_STORAGE_KEY)) || [];
        } catch (_) {
            return [];
        }
    }

    function saveHistory(history) {
        try {
            sessionStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(history));
        } catch (_) { /* storage full – silently skip */ }
    }

    function addToHistory(text, isUser) {
        const history = loadHistory();
        history.push({ text, isUser, ts: Date.now() });
        saveHistory(history);
    }

    // ─── Rendering ───────────────────────────────────────────────────────────

    function appendMessage(text, isUser, skipSave) {
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
            if (typeof marked !== 'undefined') {
                bubble.innerHTML = marked.parse(text);
            } else {
                bubble.textContent = text;
            }
        }

        wrapper.appendChild(avatar);
        wrapper.appendChild(bubble);
        chatWindow.appendChild(wrapper);
        chatWindow.scrollTop = chatWindow.scrollHeight;

        if (!skipSave) {
            addToHistory(text, isUser);
        }
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
        const el = document.getElementById('loadingBubble');
        if (el) el.remove();
    }

    // ─── Restore history on load ─────────────────────────────────────────────

    function restoreHistory() {
        const history = loadHistory();
        if (history.length === 0) return;

        // Remove the default welcome bubble before restoring so we don't
        // duplicate it when there is already a saved conversation.
        const defaultBubble = chatWindow.querySelector('.bot-message');
        if (defaultBubble) defaultBubble.remove();

        history.forEach(({ text, isUser }) => {
            appendMessage(text, isUser, /* skipSave */ true);
        });
    }

    restoreHistory();

    // ─── Clear chat button ───────────────────────────────────────────────────

    const clearBtn = document.getElementById('clearChatBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            sessionStorage.removeItem(CHAT_STORAGE_KEY);
            // Reload page to show the fresh welcome message
            window.location.reload();
        });
    }

    // ─── Send message ────────────────────────────────────────────────────────

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        appendMessage(message, true);
        chatInput.value = '';

        addLoadingBubble();

        fetch(BASE + '/api/manager/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message })
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
        .catch(() => {
            removeLoadingBubble();
            appendMessage('A network error occurred. Please try again.', false);
        });
    });
});
