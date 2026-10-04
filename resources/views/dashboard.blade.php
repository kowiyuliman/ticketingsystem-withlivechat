@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>MPTB Ticketing Dashboard</h1>
@stop

@section('content')

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $open }}</h3>
                <p>Open Ticket</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pending }}</h3>
                <p>Pending Ticket</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $progress }}</h3>
                <p>On Progress</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $closed }}</h3>
                <p>Closed Ticket</p>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
@stop