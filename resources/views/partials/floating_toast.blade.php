<!-- Global Floating Toast Notification Container -->
<div id="floating-toast-container" dir="ltr"></div>

<!-- Modern Floating Copy IP Toast Pill (Top Center) -->
<div id="floating-copy-toast" class="floating-copy-toast" style="display: none;">
    <div class="copy-toast-content">
       
        <span class="copy-toast-text" id="copy-toast-title">IP Address disalin</span>
        <code class="copy-toast-ip" id="copy-toast-ip-text"></code>
    </div>
</div>

<!-- Modern Top Floating Remote Desktop Confirmation Bubble -->
<div id="vnc-remote-bubble" class="vnc-remote-bubble" style="display: none;">
    <div class="vnc-bubble-content">
        <div class="vnc-bubble-text-wrap">
            <span class="vnc-bubble-question">Apakah yakin akan remote?</span>
            <code class="vnc-bubble-ip" id="vnc-bubble-ip-text">192.168.1.1</code>
        </div>
        <div class="vnc-bubble-divider"></div>
        <div class="vnc-bubble-actions">
            <button type="button" class="vnc-bubble-btn vnc-btn-yes" onclick="confirmVncRemote()">
                <i class="fas fa-check mr-1"></i> Ya
            </button>
            <button type="button" class="vnc-bubble-btn vnc-btn-no" onclick="closeVncModal()">
                <i class="fas fa-times mr-1"></i> Tidak
            </button>
        </div>
    </div>
</div>

<style>
/* Toast Container (Top Right for system toasts) */
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
    border-left: 4px solid #0284c7;
}
.floating-toast-success .toast-icon {
    color: #0284c7;
    background: #e0f2fe;
}

.floating-toast-error {
    border-left: 4px solid #e11d48;
}
.floating-toast-error .toast-icon {
    color: #e11d48;
    background: #ffe4e6;
}

.floating-toast-warning {
    border-left: 4px solid #d97706;
}
.floating-toast-warning .toast-icon {
    color: #d97706;
    background: #fef3c7;
}

.floating-toast-info {
    border-left: 4px solid #2563eb;
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

/* Modern Floating Copy IP Toast Pill (Top Center) */
.floating-copy-toast {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(-24px) scale(0.96);
    z-index: 9999999;
    opacity: 0;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
}

.floating-copy-toast.show {
    transform: translateX(-50%) translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
}

.floating-copy-toast.hide {
    transform: translateX(-50%) translateY(-24px) scale(0.96);
    opacity: 0;
    pointer-events: none;
}

.copy-toast-content {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(15, 23, 42, 0.94);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #ffffff;
    padding: 9px 18px;
    border-radius: 9999px;
    box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.12);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 13.5px;
    font-weight: 500;
}

.copy-toast-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 800;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
    flex-shrink: 0;
}

.copy-toast-text {
    color: #f8fafc;
    font-weight: 600;
    letter-spacing: 0.2px;
    white-space: nowrap;
}

.copy-toast-ip {
    background: rgba(56, 189, 248, 0.18);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.35);
    padding: 2px 9px;
    border-radius: 6px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

/* Modern Top Floating Remote Confirmation Bubble */
.vnc-remote-bubble {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(-28px) scale(0.95);
    z-index: 99999999;
    opacity: 0;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
}

.vnc-remote-bubble.show {
    transform: translateX(-50%) translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
}

.vnc-remote-bubble.hide {
    transform: translateX(-50%) translateY(-28px) scale(0.95);
    opacity: 0;
    pointer-events: none;
}

.vnc-bubble-content {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    color: #ffffff;
    padding: 8px 14px 8px 18px;
    border-radius: 9999px;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.14);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 13.5px;
}

.vnc-bubble-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7, #2563eb);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    box-shadow: 0 0 12px rgba(2, 132, 199, 0.5);
    flex-shrink: 0;
}

.vnc-bubble-text-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.vnc-bubble-question {
    color: #f8fafc;
    font-weight: 600;
    letter-spacing: 0.2px;
    font-size: 13.5px;
}

.vnc-bubble-ip {
    background: rgba(56, 189, 248, 0.18);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.4);
    padding: 2px 8px;
    border-radius: 6px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.4px;
}

.vnc-bubble-divider {
    width: 1px;
    height: 22px;
    background: rgba(255, 255, 255, 0.15);
}

.vnc-bubble-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.vnc-bubble-btn {
    border: none;
    cursor: pointer;
    font-size: 12.5px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 9999px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

.vnc-btn-yes {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
}

.vnc-btn-yes:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.6);
}

.vnc-btn-no {
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.vnc-btn-no:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff !important;
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

    // Top-Center Modern Floating Copy Pill Toast
    let copyToastTimeout = null;
    window.showCopyToast = function(ip, message = 'IP Address disalin') {
        let toast = document.getElementById('floating-copy-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'floating-copy-toast';
            toast.className = 'floating-copy-toast';
            toast.innerHTML = `
                <div class="copy-toast-content">
                    <span class="copy-toast-icon"><i class="fas fa-check"></i></span>
                    <span class="copy-toast-text" id="copy-toast-title">${message}</span>
                    <code class="copy-toast-ip" id="copy-toast-ip-text">${ip || ''}</code>
                </div>
            `;
            document.body.appendChild(toast);
        } else {
            const titleEl = document.getElementById('copy-toast-title');
            const ipEl = document.getElementById('copy-toast-ip-text');
            if (titleEl) titleEl.textContent = message;
            if (ipEl) {
                ipEl.textContent = ip || '';
                ipEl.style.display = ip ? 'inline-block' : 'none';
            }
        }

        toast.style.display = 'block';
        toast.offsetHeight; // Force reflow
        toast.classList.remove('hide');
        toast.classList.add('show');

        if (copyToastTimeout) clearTimeout(copyToastTimeout);
        copyToastTimeout = setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.classList.contains('hide')) {
                    toast.style.display = 'none';
                }
            }, 350);
        }, 2200);
    };

    // Global copy IP address handler
    window.copyIpAddress = function(ip, btn) {
        if (!ip || ip === '-' || ip === '127.0.0.1') {
            window.showFloatingToast('IP Address tidak valid untuk disalin', 'warning', 'Perhatian', 3000);
            return;
        }

        var textArea = document.createElement("textarea");
        textArea.value = ip;
        textArea.setAttribute("readonly", "");
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.width = "2em";
        textArea.style.height = "2em";
        textArea.style.padding = "0";
        textArea.style.border = "none";
        textArea.style.outline = "none";
        textArea.style.boxShadow = "none";
        textArea.style.background = "transparent";
        textArea.style.opacity = "0.01";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(0, 99999);

        var copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (err) {
            copied = false;
        }
        document.body.removeChild(textArea);

        if (!copied && navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(ip);
            copied = true;
        }

        if (btn) {
            var icon = btn.querySelector('i');
            if (icon) {
                var oldClass = icon.className;
                icon.className = 'fas fa-check text-success';
                setTimeout(function() { icon.className = oldClass; }, 1800);
            }
        }

        window.showCopyToast(ip, 'IP Address disalin');
    };

    // Modern Top Floating Remote Desktop Confirmation Bubble Controller
    let currentVncIp = '';
    let currentVncTicketId = '';

    window.openVncModal = function(ticketId, ip, laptop = '-', user = '-') {
        if (!ip || ip === '-' || ip === '127.0.0.1') {
            window.showFloatingToast('IP Address tidak valid untuk remote TightVNC', 'warning', 'Perhatian', 3000);
            return;
        }

        currentVncIp = ip;
        currentVncTicketId = ticketId;

        const bubble = document.getElementById('vnc-remote-bubble');
        const ipEl = document.getElementById('vnc-bubble-ip-text');

        if (ipEl) ipEl.textContent = ip;

        if (bubble) {
            bubble.style.display = 'block';
            bubble.offsetHeight; // Force reflow
            bubble.classList.remove('hide');
            bubble.classList.add('show');
        }
    };

    window.closeVncModal = function() {
        const bubble = document.getElementById('vnc-remote-bubble');
        if (!bubble) return;
        bubble.classList.remove('show');
        bubble.classList.add('hide');
        setTimeout(() => {
            if (bubble.classList.contains('hide')) {
                bubble.style.display = 'none';
            }
        }, 300);
    };

    window.confirmVncRemote = function() {
        if (currentVncIp) {
            // Trigger protocol launch
            window.location.href = 'vnc://' + currentVncIp;

            // Notify backend asynchronously
            if (currentVncTicketId) {
                fetch('{{ url("/admin/ticket") }}/' + currentVncTicketId + '/launch-vnc', {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).catch(function() {});
            }
        }
        closeVncModal();
    };

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVncModal();
        }
    });

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
