@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
<h1>Edit User</h1>
@stop
 
@section('content')


@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">

    {{-- LEFT: INFO USER --}}
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi User</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" value="{{ $user->name }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="{{ $user->username }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="{{ strtoupper($user->role) }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Dibuat</label>
                    <input type="text" 
                        value="{{ $user->created_at->format('d-m-Y H:i') }}" 
                        class="form-control" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: EDIT FORM --}}
    <div class="col-md-6">
        <form action="{{ url('/admin/users/update/'.$user->id) }}" method="POST">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Update User</h3>
            </div>
            <div class="card-body">
                {{-- NAMA --}}
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" 
                        name="name" 
                        value="{{ old('name', $user->name) }}" 
                        class="form-control" 
                        required>
                </div>
                {{-- USERNAME --}}
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" 
                        name="username" 
                        value="{{ old('username', $user->username) }}" 
                        class="form-control" 
                        required>
                </div>


                {{-- PASSWORD (OPTIONAL) --}}
                <div class="form-group mt-2">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Kosongkan jika tidak ingin mengubah password">
                            <div class="input-group-append">
                                <span class="input-group-text"
                                    onclick="togglePassword()"
                                    style="cursor:pointer;">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                    </div>


                {{-- ROLE --}}
                @if(auth()->user()->role == 'admin')
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                                user
                            </option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                admin
                            </option>
                            <option value="management" {{ old('role', $user->role) == 'management' ? 'selected' : '' }}>
                                management (Bos / Hanya Dashboard)
                            </option>
                        </select>
                    </div>
                @endif
            </div>
            <div class="card-footer text-right">
                <a href="/admin/users" class="btn btn-secondary">
                    Kembali
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </div>
        </form>
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