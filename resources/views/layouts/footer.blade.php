<footer class="mptb-footer">
    <div class="footer-left">
        <strong>PT. MITRA PELANGI TERANGI BANGSA</strong>
    </div>
    <div class="footer-center">
        &copy; {{ date('Y') }} IT Support MPTB
    </div>
    <div class="footer-right">
        <span class="footer-version">v{{ config('app.version') }}</span>
    </div>
</footer>

<style>
/* Footer Utama MPTB */
.mptb-footer {
    width: 100%;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px 20px;
    background: #ffffff;
    border-top: 1px solid #dee2e6;
    border-radius: 6px;
    color: #6c757d;
    font-size: 13px;
    margin-top: 25px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
}

/* Kiri: Nama Perusahaan */
.footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #343a40;
}

/* Tengah: Copyright */
.footer-center {
    text-align: center;
    color: #6c757d;
}

/* Kanan: Badge Versi */
.footer-right {
    text-align: right;
    display: flex;
    align-items: center;
}

.footer-version {
    background: #17a2b8;
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    display: inline-block;
}

/* Responsif: Layar Tablet & Mobile (<= 768px) */
@media (max-width: 768px) {
    .mptb-footer {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        gap: 8px;
        padding: 14px 12px;
        font-size: 12.5px;
    }

    .footer-left,
    .footer-center,
    .footer-right {
        width: 100%;
        justify-content: center;
        text-align: center;
    }
}

/* Responsif: Layar HP Kecil (<= 480px) */
@media (max-width: 480px) {
    .mptb-footer {
        font-size: 11.5px;
        padding: 12px 8px;
        margin-top: 15px;
    }

    .footer-version {
        font-size: 10.5px;
        padding: 2px 8px;
    }
}
</style>