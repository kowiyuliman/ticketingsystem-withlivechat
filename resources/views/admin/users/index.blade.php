@extends('adminlte::page')

@section('title','User Management')

@section('content_header')
<h3>User Management</h3>
@stop

@section('content')

<div class="card mb-3">
    <div class="card-body">
     @if(auth()->user()->role == 'admin')
        <div class="mb-3">
            <button class="btn btn-secondary btn-filter" data-role="all">
                <i class="fas fa-users"></i> All
            </button>
            <button class="btn btn-danger btn-filter" data-role="admin">
                <i class="fas fa-user-shield"></i> Admin
            </button>
            <button class="btn btn-primary btn-filter" data-role="leader">
                <i class="fas fa-user-tie"></i> Leader
            </button>
            <button class="btn btn-dark btn-filter" data-role="management">
                <i class="fas fa-briefcase"></i> Management
            </button>
            <button class="btn btn-success btn-filter" data-role="user">
                <i class="fas fa-user"></i> User
            </button>
        </div>
    @endif

        <a href="/admin/users/create" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i>
                Tambah User
        </a>

        @if(auth()->user()->role == 'admin')
            <form action="/admin/users/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

    <form id="bulkDeleteForm"
        action="/admin/users/bulkDelete"
        method="POST">
        @csrf
        @method('DELETE')
        <input type="hidden"
            name="selected_users"
            id="selected_users">
        <button type="submit"
                id="btnDeleteSelected"
                class="btn btn-danger mb-3"
                onclick="return confirm('Hapus semua user yang dipilih?')">
            <i class="fas fa-trash"></i>
            Hapus Terpilih
            (<span id="selectedCount">0</span>)
        </button>

    </form>

<table id="table-user" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="checkAll">
            </th>
            <th>No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Leader</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>
                <input type="checkbox" class="user-checkbox" value="{{ $user->id }}">
            </td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->username }}</td>
            <td>
                <span class="badge bg-info">
                    {{ $user->role }}
                </span>
            </td>
            <td>
                {{ $user->leader->name ?? '-' }}
            </td>
            <td>
                <a href="/admin/users/edit/{{ $user->id }}"
                    class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="/admin/users/delete/{{ $user->id }}"
                    method="POST"
                    style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm"
                        onclick="return confirm('Hapus user?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody> 
</table>
<div id="toast-container"></div>
@include('layouts.footer')
@stop


@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        td:first-child, th:first-child {
                    text-align: center;
                    width: 50px;
                }

        .btn-filter.active {
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            transform: scale(1.05);
        }

        .toast-custom{
            position:fixed;
            top:20px;
            right:20px;
            min-width:300px;
            padding:15px 20px;
            color:#fff;
            border-radius:10px;
            z-index:9999;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
            animation:slideIn .4s ease;
        }

        .toast-success{
            background:#28a745;
        }

        .toast-error{
            background:#dc3545;
        }

        @keyframes slideIn{
            from{
                transform:translateX(100%);
                opacity:0;
            }
            to{
                transform:translateX(0);
                opacity:1;
            }
        }
    </style>
@stop


@section('js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        let table = $('#table-user').DataTable({
            responsive: true,
            autoWidth: false,

            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    searchable: false
                },
                {
                    targets: 6,
                    orderable: false,
                    searchable: false
                }
            ]
            
        });
                
        //hapus banyak user
        $('#checkAll').on('click', function(){
            $('.user-checkbox').prop(
                'checked',
                $(this).prop('checked')
            );
            updateSelectedCount();
        });

        $('#bulkDeleteForm').submit(function(e){
            let selected = [];
            $('.user-checkbox:checked').each(function(){
                selected.push($(this).val());
            });
            if(selected.length === 0){
                alert('Pilih minimal 1 user');
                return false;
            }
            $('#selected_users').val(
                JSON.stringify(selected)
            );
        });

        function updateSelectedCount(){
            let total =
                $('.user-checkbox:checked').length;
            $('#selectedCount').text(total);
            let btn = $('#btnDeleteSelected');
            btn.prop('disabled', total === 0);
            if(total > 0){
                btn.removeClass('btn-secondary')
                .addClass('btn-danger');
            }else{
                btn.removeClass('btn-danger')
                .addClass('btn-secondary');
            }
        }

        $(document).on(
            'change',
            '.user-checkbox',
            function(){
                updateSelectedCount();
            }
        );

        table.on('draw', function(){
            updateSelectedCount();
        });

        $('.btn-filter').click(function(){
            let role = $(this).data('role');

            if(role === 'all'){
                table.search('').draw();
            }else{
                table.search(role).draw();
            }
        });

        //nomor urut tetap rapi
        table.on('order.dt search.dt', function () {
             table.column(1, { search:'applied', order:'applied' })
                .nodes()
                .each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
        }).draw(); 
    });

    function showToast(message, type='success')
    {
        let toast = document.createElement('div');

        toast.className =
            'toast-custom toast-' + type;

        toast.innerHTML = message;

        document.body.appendChild(toast);

        setTimeout(() => {

            toast.style.opacity = '0';
            toast.style.transform =
                'translateX(100%)';

            setTimeout(() => {

                toast.remove();

            },500);

        },3000);
    }

    @if(session('success'))
        showToast(
            "{{ session('success') }}",
            'success'
        );
    @endif

    @if(session('error'))
        showToast(
            "{{ session('error') }}",
            'error'
        );
    @endif
    
</script>
@stop