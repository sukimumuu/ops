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
    const formFillEnabled = chatbot.dataset.formFill === 'true';
    const wizardRoot = formFillEnabled
        ? document.querySelector('[x-data^="listingWizard"]')
        : null;
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

    const FORM_FILL_SUCCESS_MESSAGE = 'Baik, saya telah mengisi formulir sesuai data Anda. Silakan diperiksa kembali.';

    const FORM_FILL_ENUMS = {
        property_type: ['bangunan', 'tanah'],
        building_type: ['rumah', 'apartemen', 'ruko', 'villa', 'gudang', 'kantor'],
        land_type: ['kavling', 'sawah', 'kebun', 'komersial', 'industri'],
        transaction_type: ['dijual', 'disewakan'],
        rent_period: ['bulan', 'tahun'],
        condition: ['baru', 'siap_huni', 'butuh_renovasi'],
        certificate_type: ['SHM', 'SHGB', 'GIRIK', 'AJB', 'OTHER'],
        land_contour: ['datar', 'miring', 'berbukit'],
        road_access: ['jalan_utama', 'jalan_lingkungan', 'gang'],
        zone_type: ['perumahan', 'komersial', 'industri', 'pertanian', 'campuran'],
    };

    const FORM_FILL_TEXT_LIMITS = {
        title: 255,
        description: 2000,
        address: 500,
    };

    const FORM_FILL_NUMBERS = {
        land_area_sqm: false,
        building_area_sqm: false,
        front_width: false,
        bedrooms: true,
        bathrooms: true,
        floors: true,
    };

    const FORM_FILL_LABELS = {
        property_type: 'tipe properti',
        building_type: 'jenis bangunan',
        land_type: 'jenis tanah',
        transaction_type: 'tujuan transaksi',
        rent_period: 'periode sewa',
        title: 'judul properti',
        description: 'deskripsi',
        price: 'harga',
        negotiable: 'status nego',
        land_area_sqm: 'luas tanah',
        building_area_sqm: 'luas bangunan',
        bedrooms: 'kamar tidur',
        bathrooms: 'kamar mandi',
        floors: 'jumlah lantai',
        condition: 'kondisi bangunan',
        certificate_type: 'sertifikat',
        address: 'alamat',
        front_width: 'lebar depan',
        land_contour: 'kontur tanah',
        road_access: 'akses jalan',
        zone_type: 'peruntukan zona',
    };

    function getWizardState() {
        if (!wizardRoot || !window.Alpine || typeof window.Alpine.$data !== 'function') {
            return null;
        }

        try {
            const state = window.Alpine.$data(wizardRoot);

            return state && typeof state === 'object' && state.form ? state : null;
        } catch {
            return null;
        }
    }

    function applyFormFill(payload) {
        const state = getWizardState();

        if (!state || !payload || typeof payload !== 'object' || Array.isArray(payload)) {
            return null;
        }

        const applied = [];
        const skipped = [];
        const markApplied = (key) => {
            if (!applied.includes(key)) {
                applied.push(key);
            }
        };
        const markSkipped = (key) => {
            if (!skipped.includes(key)) {
                skipped.push(key);
            }
        };

        const requestedType = FORM_FILL_ENUMS.property_type.includes(payload.property_type)
            ? payload.property_type
            : null;
        const inferredType = requestedType
            || (payload.building_type !== undefined ? 'bangunan' : null)
            || (payload.land_type !== undefined ? 'tanah' : null);

        if (inferredType !== null) {
            state.form.property_type = inferredType;
            markApplied('property_type');
        }

        const propertyType = state.form.property_type;

        if (FORM_FILL_ENUMS.transaction_type.includes(payload.transaction_type)) {
            state.form.transaction_type = payload.transaction_type;
            markApplied('transaction_type');
        } else if (payload.transaction_type !== undefined) {
            markSkipped('transaction_type');
        }

        const setEnum = (key) => {
            const value = payload[key];

            if (value === undefined) {
                return;
            }

            if (FORM_FILL_ENUMS[key].includes(value)) {
                state.form[key] = value;
                markApplied(key);
            } else {
                markSkipped(key);
            }
        };

        const setText = (key) => {
            const value = payload[key];

            if (value === undefined) {
                return;
            }

            if (typeof value !== 'string') {
                markSkipped(key);
                return;
            }

            const trimmed = value.trim();

            if (trimmed === '' || trimmed.length > FORM_FILL_TEXT_LIMITS[key]) {
                markSkipped(key);
                return;
            }

            state.form[key] = trimmed;
            markApplied(key);
        };

        const setNumber = (key) => {
            const value = payload[key];

            if (value === undefined) {
                return;
            }

            const number = Number(value);
            const minimum = key === 'floors' ? 1 : 0;

            if (!Number.isFinite(number) || number < minimum) {
                markSkipped(key);
                return;
            }

            state.form[key] = FORM_FILL_NUMBERS[key] ? Math.round(number) : number;
            markApplied(key);
        };

        setEnum('building_type');
        setEnum('land_type');

        ['condition', 'certificate_type', 'land_contour', 'road_access', 'zone_type']
            .forEach(setEnum);

        Object.keys(FORM_FILL_TEXT_LIMITS).forEach(setText);
        Object.keys(FORM_FILL_NUMBERS).forEach(setNumber);

        if (payload.price !== undefined) {
            const price = Number(payload.price);

            if (Number.isFinite(price) && price >= 0) {
                state.form.price = state.formatCurrency(String(Math.round(price)));
                markApplied('price');
            } else {
                markSkipped('price');
            }
        }

        if (typeof payload.negotiable === 'boolean') {
            state.form.negotiable = payload.negotiable;
            markApplied('negotiable');
        } else if (payload.negotiable !== undefined) {
            markSkipped('negotiable');
        }

        if (payload.rent_period !== undefined) {
            if (state.form.transaction_type === 'disewakan'
                && FORM_FILL_ENUMS.rent_period.includes(payload.rent_period)) {
                state.form.rent_period = payload.rent_period;
                markApplied('rent_period');
            } else {
                markSkipped('rent_period');
            }
        }

        const knownFields = new Set([
            ...Object.keys(FORM_FILL_ENUMS),
            ...Object.keys(FORM_FILL_TEXT_LIMITS),
            ...Object.keys(FORM_FILL_NUMBERS),
            'price',
            'negotiable',
        ]);

        Object.keys(payload).forEach((key) => {
            if (!knownFields.has(key)) {
                markSkipped(key);
            }
        });

        if (typeof state.syncRentPeriodState === 'function') {
            state.syncRentPeriodState();
        }

        if (applied.length > 0) {
            if (state.errors && typeof state.errors === 'object') {
                applied.forEach((key) => {
                    delete state.errors[key];
                });
            }

            if (state.currentStep !== 1) {
                state.currentStep = 1;
            }
        }

        return {
            applied,
            skipped,
            propertyType,
        };
    }

    function formFillMessages(outcome) {
        if (outcome.applied.length === 0) {
            return [
                'Maaf, saya belum dapat mengisi formulir dari deskripsi tersebut. Coba sebutkan tipe properti, harga, dan lokasinya.',
            ];
        }

        const messages = [FORM_FILL_SUCCESS_MESSAGE];

        if (outcome.skipped.length > 0) {
            const fields = [...new Set(outcome.skipped)]
                .map((key) => FORM_FILL_LABELS[key] || key)
                .join(', ');

            messages.push(`Catatan: ${fields} belum dapat diterapkan otomatis. Silakan lengkapi manual.`);
        }

        return messages;
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
            const requestBody = { message };

            if (formFillEnabled && wizardRoot) {
                requestBody.context = 'listing_create';
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(requestBody),
            });
            const result = await response.json();

            loadingBubble.remove();

            if (!response.ok) {
                throw new Error('Maaf, server sedang sibuk. Silakan coba lagi.');
            }

            if (result.type === 'form_fill') {
                const outcome = applyFormFill(result.data);

                if (outcome === null) {
                    addMessage(
                        'bot',
                        'Maaf, data formulir dari asisten tidak dapat diterapkan. Silakan isi formulir secara manual.',
                    );

                    return;
                }

                formFillMessages(outcome).forEach((replyMessage) => addMessage('bot', replyMessage));

                if (outcome.applied.length > 0 && wizardRoot) {
                    wizardRoot.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                return;
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
