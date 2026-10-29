const notificationCenter = document.querySelector('#notification-center');

if (notificationCenter) {
    const notificationItems = document.querySelector('#notification-items');
    const notificationBadge = document.querySelector('#notification-badge');
    const notificationCount = document.querySelector('#notification-count');
    const seenNotificationIds = new Set();
    const csrfToken = notificationCenter.dataset.csrfToken;
    let hasLoadedNotifications = false;

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

    const showToast = (notification) => {
        let container = document.querySelector('#notification-toasts');

        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-toasts';
            container.className = 'pointer-events-none fixed right-4 top-20 z-[100] flex w-[min(90vw,24rem)] flex-col gap-3';
            container.setAttribute('aria-live', 'polite');
            document.body.append(container);
        }

        const toast = document.createElement('a');
        toast.href = notification.url;
        toast.dataset.notificationRead = notification.id;
        toast.className = 'pointer-events-auto block rounded-xl border border-slate-200 bg-white p-4 shadow-xl transition hover:border-primary';

        const title = document.createElement('p');
        title.className = 'font-bold text-slate-800';
        title.textContent = notification.title;

        const message = document.createElement('p');
        message.className = 'mt-1 text-sm text-slate-600';
        message.textContent = notification.message;

        toast.append(title, message);
        container.append(toast);
        window.setTimeout(() => toast.remove(), 8000);
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

            payload.notifications.forEach((notification) => {
                if (hasLoadedNotifications && !seenNotificationIds.has(notification.id)) {
                    showToast(notification);
                }

                seenNotificationIds.add(notification.id);
            });

            hasLoadedNotifications = true;
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
    window.setInterval(() => {
        if (!document.hidden) {
            pollNotifications();
        }
    }, 5000);
}
