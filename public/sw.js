// Service worker do Norte — recebe Web Push e mostra a notificação
self.addEventListener('push', (event) => {
    let dados = { title: 'Norte', body: 'Você tem um lembrete.', url: '/agenda' };
    try {
        dados = Object.assign(dados, event.data.json());
    } catch (e) { /* payload não-JSON: usa o padrão */ }

    event.waitUntil(
        self.registration.showNotification(dados.title, {
            body: dados.body,
            icon: '/icon.svg',
            badge: '/icon.svg',
            data: { url: dados.url },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/agenda';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((abas) => {
            for (const aba of abas) {
                if ('focus' in aba) { aba.navigate(url); return aba.focus(); }
            }
            return clients.openWindow(url);
        })
    );
});
