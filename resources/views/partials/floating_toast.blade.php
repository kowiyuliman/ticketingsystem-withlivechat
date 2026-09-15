<!-- Global Floating Toast Notification Container -->
<div id="floating-toast-container" dir="ltr"></div>

<style>
#floating-toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 380px;
    width: calc(100vw - 40px);
    pointer-events: none;
}

.floating-toast-item {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 14px;
    box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.15), 0 4px 12px -2px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(226, 232, 240, 0.9);
    position: relative;
    overflow: hidden;
    transform: translateX(120%);
    opacity: 0;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease-out;
}

.floating-toast-item.show {
    transform: translateX(0);
    opacity: 1;
}

.floating-toast-item.hide {
    transform: translateX(120%);
    opacity: 0;
}

/* Toast Color Variants */
.floating-toast-success {
    border-left: 4px solid #0284c7; /* Sky 600 */
}
.floating-toast-success .toast-icon {
    color: #0284c7;
    background: #e0f2fe;
}

.floating-toast-error {
    border-left: 4px solid #e11d48; /* Rose 600 */
}
.floating-toast-error .toast-icon {
    color: #e11d48;
    background: #ffe4e6;
}

.floating-toast-warning {
    border-left: 4px solid #d97706; /* Amber 600 */
}
.floating-toast-warning .toast-icon {
    color: #d97706;
    background: #fef3c7;
}

.floating-toast-info {
    border-left: 4px solid #2563eb; /* Blue 600 */
}
.floating-toast-info .toast-icon {
    color: #2563eb;
    background: #dbeafe;
}

.toast-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    flex-shrink: 0;
}

.toast-content {
    flex: 1;
    min-width: 0;
}

.toast-title {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 2px;
}

.toast-message {
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    line-height: 1.4;
    word-wrap: break-word;
}

.toast-close-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    transition: color 0.2s;
    flex-shrink: 0;
}

.toast-close-btn:hover {
    color: #334155;
}

.toast-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    background: rgba(2, 132, 199, 0.4);
    transform-origin: left;
    animation: toastProgress 4s linear forwards;
}

.floating-toast-error .toast-progress-bar {
    background: rgba(225, 29, 72, 0.4);
}
.floating-toast-warning .toast-progress-bar {
    background: rgba(217, 119, 6, 0.4);
}
.floating-toast-info .toast-progress-bar {
    background: rgba(37, 99, 235, 0.4);
}

@keyframes toastProgress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
</style>

<script>
(function() {
    window.showFloatingToast = function(message, type = 'success', title = null, duration = 4000) {
        let container = document.getElementById('floating-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'floating-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `floating-toast-item floating-toast-${type}`;

        let iconSymbol = 'ℹ️';
        let defaultTitle = 'Notifikasi';

        if (type === 'success') {
            iconSymbol = '✓';
            defaultTitle = 'Berhasil';
        } else if (type === 'error') {
            iconSymbol = '✕';
            defaultTitle = 'Gagal';
        } else if (type === 'warning') {
            iconSymbol = '⚠️';
            defaultTitle = 'Peringatan';
        } else if (type === 'info') {
            iconSymbol = 'ℹ️';
            defaultTitle = 'Informasi';
        }

        const displayTitle = title || defaultTitle;

        toast.innerHTML = `
            <div class="toast-icon">${iconSymbol}</div>
            <div class="toast-content">
                <div class="toast-title">${displayTitle}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close-btn" onclick="dismissToast(this.parentElement)">&times;</button>
            <div class="toast-progress-bar" style="animation-duration: ${duration}ms;"></div>
        `;

        container.appendChild(toast);

        // Trigger entrance animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto dismiss timer
        let timer = setTimeout(() => {
            dismissToast(toast);
        }, duration);

        toast.addEventListener('mouseenter', () => {
            const bar = toast.querySelector('.toast-progress-bar');
            if (bar) bar.style.animationPlayState = 'paused';
            clearTimeout(timer);
        });

        toast.addEventListener('mouseleave', () => {
            const bar = toast.querySelector('.toast-progress-bar');
            if (bar) bar.style.animationPlayState = 'running';
            timer = setTimeout(() => {
                dismissToast(toast);
            }, 1500);
        });
    };

    window.dismissToast = function(toast) {
        if (!toast || toast.classList.contains('hide')) return;
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 350);
    };

    // Alias for existing showToast calls across views
    window.showToast = function(msg, type = 'success', duration = 4000) {
        window.showFloatingToast(msg, type, null, duration);
    };

    // Auto-trigger floating toasts for Laravel Session Flash messages on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showFloatingToast(@json(session('success')), 'success', 'Berhasil', 4000);
        @endif
        @if(session('error'))
            showFloatingToast(@json(session('error')), 'error', 'Error', 4000);
        @endif
        @if(session('info'))
            showFloatingToast(@json(session('info')), 'info', 'Informasi', 4000);
        @endif
        @if(session('warning'))
            showFloatingToast(@json(session('warning')), 'warning', 'Peringatan', 4000);
        @endif
        @if(session('status'))
            showFloatingToast(@json(session('status')), 'info', 'Status', 4000);
        @endif
    });
})();
</script>

