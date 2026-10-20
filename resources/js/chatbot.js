document.addEventListener('DOMContentLoaded', function () {
    const chatbot = document.querySelector('[data-chatbot]');

    if (!chatbot) {
        return;
    }

    const launcher = chatbot.querySelector('[data-chatbot-launcher]');
    const chatPanel = chatbot.querySelector('#mihom-chat-panel, [data-chatbot-panel]');
    const chatInput = chatbot.querySelector('#chat-input, [data-chatbot-input]');
    const sendButton = chatbot.querySelector('#send-btn, [data-chatbot-send]');
    const chatMessages = chatbot.querySelector('#chat-messages, [data-chatbot-messages]');

    function setChatbotOpen(isOpen) {
        if (!chatPanel || !launcher) {
            return;
        }

        chatPanel.hidden = !isOpen;
        chatPanel.classList.toggle('hidden', !isOpen);
        launcher.setAttribute('aria-expanded', String(isOpen));

        if (isOpen) {
            chatInput?.focus();
        } else {
            launcher.focus();
        }
    }

    launcher?.addEventListener('click', () => {
        const isOpen = chatPanel && !chatPanel.hidden && !chatPanel.classList.contains('hidden');

        setChatbotOpen(!isOpen);
    });

    chatbot.querySelectorAll('[data-chatbot-close], [data-chatbot-minimize]').forEach((button) => {
        button.addEventListener('click', () => setChatbotOpen(false));
    });

    if (!chatInput || !sendButton || !chatMessages) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || chatbot?.dataset.csrfToken;
    const endpoint = chatbot?.dataset.endpoint || '/api/chat';
    const welcome = chatbot?.querySelector('[data-chatbot-welcome]');
    const properties = chatbot?.querySelector('[data-chatbot-properties]');
    const suggestions = chatbot?.querySelectorAll('[data-chatbot-question]') || [];
    const chatForm = chatInput.closest('form');
    let isSending = false;

    if (!document.querySelector('meta[name="csrf-token"]')) {
        console.warn(
            'Meta CSRF token belum ada. Tambahkan <meta name="csrf-token" content="{{ csrf_token() }}"> di dalam <head> Blade.',
        );
    }

    function scrollToLatestMessage() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addMessage(role, content) {
        const bubble = document.createElement('p');

        bubble.className = role === 'user'
            ? 'max-w-[88%] self-end rounded-2xl rounded-br-md bg-primary px-4 py-3 text-sm leading-6 text-white'
            : 'max-w-[88%] self-start rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3 text-sm leading-6 text-secondary';
        bubble.textContent = content;
        chatMessages.append(bubble);
        scrollToLatestMessage();

        return bubble;
    }

    function addPropertyCards(listings) {
        if (!properties) {
            return;
        }

        properties.replaceChildren();

        listings.forEach((listing) => {
            if (typeof listing.title !== 'string' || typeof listing.url !== 'string') {
                return;
            }

            const propertyUrl = new URL(listing.url, window.location.origin);

            if (propertyUrl.origin !== window.location.origin) {
                return;
            }

            const card = document.createElement('a');
            const title = document.createElement('span');
            const details = document.createElement('span');

            card.href = propertyUrl.href;
            card.className = 'flex flex-col gap-1 rounded-xl border border-slate-200 p-3 text-sm transition hover:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary';
            title.className = 'font-display font-semibold text-secondary';
            details.className = 'text-xs text-slate-600';
            title.textContent = listing.title;
            details.textContent = [
                typeof listing.location === 'string' ? listing.location : '',
                Number.isFinite(Number(listing.price))
                    ? `Rp ${new Intl.NumberFormat('id-ID').format(Number(listing.price))}`
                    : '',
            ].filter(Boolean).join(' · ');
            card.append(title, details);
            properties.append(card);
        });

        scrollToLatestMessage();
    }

    async function sendMessage() {
        const message = chatInput.value.trim();

        if (message === '' || isSending) {
            return;
        }

        if (!csrfToken) {
            addMessage(
                'bot',
                'Token keamanan tidak tersedia. Tambahkan meta CSRF token pada layout Blade lalu muat ulang halaman.',
            );

            return;
        }

        isSending = true;
        chatInput.value = '';
        chatInput.disabled = true;
        sendButton.disabled = true;
        welcome?.classList.add('hidden');
        chatMessages.classList.remove('hidden');
        chatMessages.hidden = false;
        properties?.replaceChildren();
        addMessage('user', message);

        const loadingBubble = addMessage('bot', 'Sedang mengetik...');
        loadingBubble.setAttribute('role', 'status');
        loadingBubble.classList.add('animate-pulse', 'text-slate-500');

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message }),
            });
            const result = await response.json();

            loadingBubble.remove();

            if (!response.ok) {
                throw new Error('Maaf, server sedang sibuk. Silakan coba lagi.');
            }

            const reply = result.message
                || result.reply
                || result.answer
                || result.choices?.[0]?.message?.content;

            if (typeof reply !== 'string' || reply.trim() === '') {
                throw new Error('Maaf, server sedang sibuk. Silakan coba lagi.');
            }

            addMessage('bot', reply.trim());
            addPropertyCards(Array.isArray(result.properties) ? result.properties : []);
        } catch {
            loadingBubble.remove();
            addMessage('bot', 'Maaf, server sedang sibuk. Silakan coba lagi.');
        } finally {
            isSending = false;
            chatInput.disabled = false;
            sendButton.disabled = chatInput.value.trim() === '';
            chatInput.focus();
        }
    }

    sendButton.addEventListener('click', (event) => {
        event.preventDefault();
        void sendMessage();
    });

    chatInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            void sendMessage();
        }
    });

    chatInput.addEventListener('input', () => {
        sendButton.disabled = isSending || chatInput.value.trim() === '';
    });

    chatForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        void sendMessage();
    });

    suggestions.forEach((button) => {
        button.addEventListener('click', () => {
            chatInput.value = button.dataset.chatbotQuestion || button.textContent.trim();
            void sendMessage();
        });
    });
});
