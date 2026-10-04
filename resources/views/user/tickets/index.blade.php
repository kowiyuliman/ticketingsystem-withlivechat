@extends('adminlte::page')

@section('title','Tickets')

@section('content')

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Ticket Code</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Assigned</th>
            <th>Aksi</th>
        </tr>
    </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_code }}</td>
                <td>{{ $ticket->nama }}</td>
                <td>{{ $ticket->kategori }}</td>
                <td>{{ $ticket->status }}</td>
                <td>{{ $ticket->assigned_to }}</td>
                <td>
                    <a href="/admin/ticket/{{$ticket->id}}" class="btn btn-sm btn-primary">
                    Detail
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
</table>

@include('layouts.footer')
@endsection