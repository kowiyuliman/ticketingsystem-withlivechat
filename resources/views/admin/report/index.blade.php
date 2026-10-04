@extends('adminlte::page')

@section('title','Report Ticket')

@section('content_header')
<h1>Report Ticket</h1>
@stop

@section('content')

{{-- FILTER --}}
<div class="card">
    <div class="card-header">Filter</div>
    <div class="card-body">
        <form method="GET" action="/admin/report">
            <div class="row">
                <div class="col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="open">Open</option>
                        <option value="on_progress">On Progress</option>
                        <option value="pending">Pending</option>
                        <option value="closed">Closed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control">
                        <option value="">Semua</option>
                        <option value="hardware">Hardware</option>
                        <option value="software">Software</option>
                        <option value="network">Network</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Teknisi</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">Semua</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        <br>
            <button class="btn btn-primary">
                Filter
            </button>
            <a href="/admin/report/export?{{ http_build_query(request()->all()) }}" 
               class="btn btn-success">
            Export CSV
            </a>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card mt-3">
    <div class="card-header">Data Ticket</div>
    <div class="card-body">

        <table id="table-report" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>IT</th>
                    <th>Created</th>
                    <th>Started</th>
                    <th>Resolved</th>
                    <th>Durasi (Menit)</th>
                </tr>
            </thead>

            <tbody>
                @foreach($tickets as $t)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $t->ticket_code }}</td>
                    <td>{{ $t->nama }}</td>
                    <td>{{ $t->kategori }}</td>
                    <td>{{ $t->status }}</td>
                    <td>{{ $t->technician->name ?? '-' }}</td>
                    <td>{{ $t->created_at }}</td>
                    <td>{{ $t->started_at ?? '-' }}</td>
                    <td>{{ $t->resolved_at ?? '-' }}</td>
                    <td>
                        @if($t->started_at && $t->resolved_at)
                            {{ (int) $t->started_at->diffInMinutes($t->resolved_at) }} menit
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

@include('layouts.footer')
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        td:first-child, th:first-child {
                    text-align: center;
                    width: 50px;
                }
    </style>
@stop


@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

 
    <script>
        $(document).ready(function(){
            $('#table-report').DataTable({
                responsive: true
            });
        });
    </script>

    <script>
        let table = $('#your-table-id').DataTable();
        table.on('order.dt search.dt', function () {
            table.column(0, { search:'applied', order:'applied' })
                .nodes()
                .each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
        }).draw();

    </script>
@stop
