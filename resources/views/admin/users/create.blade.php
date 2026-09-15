@extends('adminlte::page')

@section('title','Tambah User')

@section('content_header')
<h1>Tambah User</h1>
@stop

@section('content')

<div class="row">
    <div class="col-md-6">

        {{-- ALERT ERROR --}}
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card card-outline card-primary shadow">
            <div class="card-header">
                <h3 class="card-title">Form User</h3>
            </div>
            <form action="/admin/users/store" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mt-2">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group mt-2">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required>
                            <div class="input-group-append">
                                <span class="input-group-text"
                                    onclick="togglePassword()"
                                    style="cursor:pointer;">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 🔥 ROLE (Leader tidak bisa pilih role) --}}
                    @if(auth()->user()->role == 'admin')
                    <div class="form-group mt-2">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="user">User</option>
                            <option value="leader">Leader</option>
                            <option value="admin">Admin</option>
                            <option value="management">Management / Bos (Hanya Dashboard)</option>
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="role" value="user">
                    @endif
                </div>
                <div class="card-footer text-right">
                    <a href="/admin/users" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@section('js')

    <script>
        function togglePassword() {

            let password = document.getElementById('password');
            let eyeIcon = document.getElementById('eyeIcon');

            if(password.type === 'password') {

                password.type = 'text';

                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');

            } else {

                password.type = 'password';

                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
@stop


@stop