/**
 * Admin Ticket Notification Red Dot Badge (Live Realtime Indicator)
 */
(function () {
    'use strict';

    function findSemuaTiketNavLink() {
        // Find sidebar menu item for "Semua Tiket"
        const navLinks = document.querySelectorAll('.sidebar .nav-item a.nav-link, aside.main-sidebar a.nav-link');
        for (let i = 0; i < navLinks.length; i++) {
            const link = navLinks[i];
            const href = link.getAttribute('href') || '';
            const text = (link.textContent || '').trim();

            if (href.includes('admin/tickets') || text.includes('Semua Tiket')) {
                return link;
            }
        }
        return null;
    }

    function updateTicketRedDot(data) {
        const targetLink = findSemuaTiketNavLink();
        if (!targetLink) return;

        // Ensure target link has relative positioning for badge placement
        targetLink.style.position = 'relative';

        let existingBadge = targetLink.querySelector('.ticket-badge-dot, .ticket-badge-count');

        if (data && data.has_unread) {
            const total = data.total_unread || 0;
            const labelText = total > 99 ? '99+' : total;

            if (!existingBadge) {
                existingBadge = document.createElement('span');
                existingBadge.className = total > 1 ? 'ticket-badge-count' : 'ticket-badge-dot';
                if (total > 1) {
                    existingBadge.innerText = labelText;
                }
                existingBadge.setAttribute('title', `${data.open_tickets_count || 0} tiket open, ${data.unread_chats_count || 0} chat baru`);
                targetLink.appendChild(existingBadge);
            } else {
                if (total > 1) {
                    existingBadge.className = 'ticket-badge-count';
                    existingBadge.innerText = labelText;
                } else {
                    existingBadge.className = 'ticket-badge-dot';
                    existingBadge.innerText = '';
                }
                existingBadge.style.display = 'inline-block';
                existingBadge.setAttribute('title', `${data.open_tickets_count || 0} tiket open, ${data.unread_chats_count || 0} chat baru`);
            }
        } else {
            if (existingBadge) {
                existingBadge.style.display = 'none';
            }
        }
    }

    function fetchUnreadCount() {
        fetch('/admin/notifications/unread-count', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response not ok');
            return res.json();
        })
        .then(data => {
            updateTicketRedDot(data);
        })
        .catch(err => {
            // Silently ignore network interruptions
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initial fetch
        fetchUnreadCount();

        // Polling interval (every 6 seconds)
        setInterval(fetchUnreadCount, 6000);
    });
})();

