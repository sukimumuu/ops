const notificationCenter = document.querySelector('#notification-center');

if (notificationCenter) {
    const notificationItems = document.querySelector('#notification-items');
    const notificationBadge = document.querySelector('#notification-badge');
    const notificationCount = document.querySelector('#notification-count');
    const browserNotificationsToggle = document.querySelector('#browser-notifications-toggle');
    const csrfToken = notificationCenter.dataset.csrfToken;

    const loadingMessage = document.createElement('p');
    loadingMessage.className = 'px-5 py-8 text-center text-sm text-slate-400';
    loadingMessage.textContent = 'Memuat notifikasi...';
    notificationItems.replaceChildren(loadingMessage);

    const updateUnreadCount = (count) => {
        notificationBadge.textContent = count > 99 ? '99+' : String(count);
        notificationBadge.classList.toggle('hidden', count === 0);
        notificationCount.textContent = `${count} baru`;
    };

    const renderNotifications = (notifications) => {
        notificationItems.replaceChildren();

        if (notifications.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'px-5 py-8 text-center text-sm text-slate-400';
            emptyMessage.textContent = 'Belum ada notifikasi baru.';
            notificationItems.append(emptyMessage);
            return;
        }

        notifications.forEach((notification) => {
            const link = document.createElement('a');
            link.href = notification.url;
            link.dataset.notificationRead = notification.id;
            link.className = 'relative flex items-start gap-3.5 bg-orange-50/20 p-4 transition hover:bg-slate-50';

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1 pr-2';

            const title = document.createElement('p');
            title.className = 'truncate text-sm font-bold text-slate-800';
            title.textContent = notification.title;

            const message = document.createElement('p');
            message.className = 'mt-0.5 line-clamp-2 text-xs text-slate-500';
            message.textContent = notification.message;

            const time = document.createElement('span');
            time.className = 'mt-1.5 inline-block text-xs font-semibold text-primary';
            time.textContent = new Date(notification.created_at).toLocaleString('id-ID');

            const unreadMarker = document.createElement('span');
            unreadMarker.className = 'mt-1.5 size-2.5 shrink-0 rounded-full bg-primary';
            unreadMarker.setAttribute('aria-label', 'Belum dibaca');

            content.append(title, message, time);
            link.append(content, unreadMarker);
            notificationItems.append(link);
        });
    };

    const pollNotifications = async () => {
        try {
            const response = await fetch(notificationCenter.dataset.feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Notification polling failed with status ${response.status}`);
            }

            const payload = await response.json();
            renderNotifications(payload.notifications);
            updateUnreadCount(payload.unread_count);

        } catch (error) {
            console.error('Unable to load notifications.', error);
        }
    };

    const markAsRead = async (notificationId) => {
        const readUrl = notificationCenter.dataset.readUrl.replace(
            '__notification__',
            encodeURIComponent(notificationId),
        );
        const response = await fetch(readUrl, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Marking notification as read failed with status ${response.status}`);
        }

        const payload = await response.json();
        updateUnreadCount(payload.unread_count);
    };

    const pushSupported = window.isSecureContext
        && 'serviceWorker' in navigator
        && 'PushManager' in window
        && 'Notification' in window;
    const vapidPublicKey = notificationCenter.dataset.vapidPublicKey;

    const updatePushButton = (isSubscribed) => {
        browserNotificationsToggle.textContent = isSubscribed
            ? 'Matikan notifikasi browser'
            : 'Aktifkan notifikasi browser';
        browserNotificationsToggle.dataset.subscribed = String(isSubscribed);
    };

    const savePushSubscription = async (subscription) => {
        const response = await fetch(notificationCenter.dataset.subscriptionUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(subscription.toJSON()),
        });

        if (!response.ok) {
            throw new Error(`Saving browser notification subscription failed with status ${response.status}`);
        }
    };

    const deletePushSubscription = async (subscription) => {
        const endpointHash = Array.from(new Uint8Array(
            await crypto.subtle.digest('SHA-256', new TextEncoder().encode(subscription.endpoint)),
        ), (byte) => byte.toString(16).padStart(2, '0')).join('');
        const deleteUrl = notificationCenter.dataset.subscriptionDeleteUrl.replace(
            '0'.repeat(64),
            endpointHash,
        );
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Removing browser notification subscription failed with status ${response.status}`);
        }
    };

    const decodeVapidPublicKey = (key) => {
        const paddedKey = key.replace(/-/g, '+').replace(/_/g, '/');
        const decoded = window.atob(paddedKey);

        return Uint8Array.from(decoded, (character) => character.charCodeAt(0));
    };

    const subscribeForPush = async (registration) => {
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: decodeVapidPublicKey(vapidPublicKey),
        });

        await savePushSubscription(subscription);
        updatePushButton(true);

        return subscription;
    };

    const initializeBrowserNotifications = async () => {
        if (!pushSupported) {
            return;
        }

        browserNotificationsToggle.hidden = false;

        if (!vapidPublicKey) {
            browserNotificationsToggle.disabled = true;
            browserNotificationsToggle.textContent = 'Notifikasi browser belum dikonfigurasi';
            return;
        }

        const registration = await navigator.serviceWorker.register('/service-worker.js');
        let subscription = await registration.pushManager.getSubscription();
        updatePushButton(subscription !== null);

        if (subscription) {
            await savePushSubscription(subscription);
        }

        browserNotificationsToggle.addEventListener('click', async () => {
            browserNotificationsToggle.disabled = true;

            try {
                if (subscription) {
                    await deletePushSubscription(subscription);
                    await subscription.unsubscribe();
                    subscription = null;
                    updatePushButton(false);
                    return;
                }

                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    throw new Error(`Browser notification permission is ${permission}.`);
                }

                subscription = await subscribeForPush(registration);
            } catch (error) {
                console.error('Unable to update browser notification subscription.', error);
                window.alert('Notifikasi browser tidak dapat diaktifkan. Periksa izin browser dan coba lagi.');
            } finally {
                browserNotificationsToggle.disabled = false;
            }
        });

        if (
            notificationCenter.dataset.autoRequestPermission === 'true'
            && Notification.permission === 'default'
            && !sessionStorage.getItem('browser-push-permission-requested')
        ) {
            sessionStorage.setItem('browser-push-permission-requested', 'true');

            try {
                await Notification.requestPermission();
            } catch (error) {
                console.error('Unable to request browser notification permission automatically.', error);
            }
        }

        if (Notification.permission === 'granted' && !subscription) {
            try {
                subscription = await subscribeForPush(registration);
            } catch (error) {
                console.error('Unable to subscribe to browser notifications automatically.', error);
            }
        }
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-notification-read]');

        if (!link) {
            return;
        }

        event.preventDefault();
        markAsRead(link.dataset.notificationRead)
            .catch((error) => console.error('Unable to mark notification as read.', error))
            .finally(() => window.location.assign(link.href));
    });

    pollNotifications();
    initializeBrowserNotifications().catch((error) => {
        console.error('Unable to initialize browser notifications.', error);
    });
    window.setInterval(() => {
        if (!document.hidden) {
            pollNotifications();
        }
    }, 5000);
}
