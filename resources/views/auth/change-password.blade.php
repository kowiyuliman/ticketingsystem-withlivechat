@extends('adminlte::page')

@section('title', 'Change Password')

@section('content_header')
<h1>Change Password</h1>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Ganti Password</h3>
            </div>

            <form method="POST" action="/change-password">
                @csrf

                <div class="card-body">

                    {{-- SUCCESS --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- {{-- PASSWORD LAMA --}}
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="current_password" class="form-control" required>
                        @error('current_password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div> -->

                    {{-- PASSWORD BARU --}}
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required>
                        @error('new_password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- KONFIRMASI --}}
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button class="btn btn-primary">
                        Update Password
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>

@include('layouts.footer')
@stop