@extends('adminlte::page')
@section('title', 'Create Ticket')
@section('content_header')
    <h1>Create Ticket</h1>
@stop
@section('content')
    <form method="POST" action="/create-ticket" enctype="multipart/form-data" id="ticketForm">
        @csrf
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" placeholder="Nama" required>
        </div>
        <div class="form-group">
            <label>Nomor Meja</label>
            <input type="text" name="nomor_meja" class="form-control" placeholder="Nomor Meja" required>
        </div>
        <div class="form-group">
            <label>Nomor Ruangan</label>
            <input type="text" name="nomor_ruangan" class="form-control" placeholder="Nomor Ruangan" required>
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp</label>
            <input type="text" name="no_whatsapp" class="form-control" placeholder="Nomor WhatsApp" required>
        </div>
        <div class="form-group">
            <label>IP Address</label>
            <input type="text" name="ip_address" class="form-control" placeholder="IP Address Bisa cek melalui aplikasi CheckIP di desktop (optional)">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" placeholder="Jelaskan kendala secara detail" required></textarea>
        </div>
        <button type="submit" class="btn btn-success mt-2" id="submitBtn">
            <span id="btnText">Submit Ticket</span>
            <span id="btnLoading" style="display: none;">
                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                Mengirim...
            </span>
        </button>
    </form>
<br>
    @include('layouts.footer')
@stop

@section('js')
<script>
    document.getElementById('ticketForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');

        // Cegah double submit
        if (btn.disabled) {
            e.preventDefault();
            return;
        }

        // Disable tombol & tampilkan loading
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
    });
</script>
@stop