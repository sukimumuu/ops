self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    const payload = event.data.json();

    event.waitUntil(self.registration.showNotification(payload.title, {
        body: payload.body,
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        data: { url: payload.data?.url || payload.url },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = new URL(event.notification.data?.url || '/', self.location.origin);

    if (targetUrl.origin !== self.location.origin) {
        return;
    }

    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        for (const client of clients) {
            if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                return client.navigate(targetUrl.href).then((navigatedClient) => {
                    return (navigatedClient || client).focus();
                });
            }
        }

        return self.clients.openWindow(targetUrl.href);
    }));
});
