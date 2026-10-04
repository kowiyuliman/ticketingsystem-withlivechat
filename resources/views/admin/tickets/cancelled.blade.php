@extends('adminlte::page')

@section('title','Ticket Cancel')

@section('content_header')
<h1>Ticket Cancel</h1>
@stop

@section('content')
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">
                Daftar Ticket Cancel
            </h3>
        </div>
        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped"
                id="cancelTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Ticket</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tanggal Cancel</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->nama }}</td>
                        <td>{{ $ticket->kategori }}</td>
                        <td>
                            <span class="badge badge-danger">
                                Cancelled
                            </span>
                        </td>
                        <td>
                            {{ $ticket->updated_at->format('d-m-Y H:i') }}
                        </td>
                        <td>
                            <a href="/admin/ticket/show/{{ $ticket->id }}"
                            class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@include('layouts.footer')
@stop


@section('js')
    <script>
        $('#cancelTable').DataTable({
            responsive:true,
            autoWidth:false,
            pageLength:10
        });
    </script>
@stop