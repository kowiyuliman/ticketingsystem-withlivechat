@extends('adminlte::page')

@section('title','Detail Ticket')

@section('content_header')
<h1>Detail Ticket</h1>
@stop

@section('content')

<div class="row">
    {{-- LEFT: INFORMASI --}}
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    Ticket: {{ $ticket->ticket_code }}
                </h3>
            </div>
            <div class="card-body">
                <p><b>Nama              :</b>  {{ $ticket->nama }}</p>
                <p><b>Nomor Meja        :</b>  {{ $ticket->nomor_meja }}</p>
                <p><b>Nomor Ruangan     :</b>  {{ $ticket->nomor_ruangan }}</p>
                <p>
                    <b>Nomor Whatsapp    :</b>  {{ $ticket->no_whatsapp }}
                        @php
                            // nomor WA
                            $wa = preg_replace('/[^0-9]/', '', $ticket->no_whatsapp);
                            // Ubah dari 08 menjadi 62
                            if(substr($wa,0,1) == '0'){
                                $wa = '62' . substr($wa,1);
                            }
                            // template pesan
                            $message  = "Halo *{$ticket->nama}*,\n\n";
                            $message .= "Kami ingin melakukan followup terkait ticket anda dengan kode :\n";
                            $message .= "*{$ticket->ticket_code}* \n\n";
                            $message .= "━━━━━━━━━━━━━━━\n";
                            $message .= "Kendala yang dilaporkan : \n";
                            $message .= "*{$ticket->deskripsi}* \n";
                            $message .= "━━━━━━━━━━━━━━━\n\n";
                            $message .= "Apakah kendala tersebut masih terjadi hingga saat ini ? \n\n";
                            $message .= "Tim IT \n";
                            $message .= "Terima kasih.";
                            $waLink   = "https://wa.me/".$wa."?text=".urlencode($message);
                        @endphp
                            <a href="{{ $waLink }}"
                                    target="_blank"
                                    title="Follow Up WhatsApp"
                                    style="
                                            color:#25D366;
                                            font-size:20px;
                                            margin-left:8px;
                                            text-decoration:none;
                                    onmouseover="this.style.opacity='0.7'"
                                    onmouseout="this.style.opacity='1'"
                            ">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                </p>

                <p><b>IP Address        :</b>   {{ $ticket->ip_address }}</p>
                <p><b>Keterangan        :</b>   <span class="text-muted">{{ $ticket->deskripsi }}</span> </p>
            </div>
        </div>
    </div>

    {{-- RIGHT: STATUS & INFO --}}
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Informasi Tiket</h3>
            </div>
            <div class="card-body">
                <p>
                    <b>Status :</b><br>
                    @if($ticket->status == 'open')
                        <span class="badge bg-info">Open</span>
                    @elseif($ticket->status == 'on_progress')
                        <span class="badge bg-primary">On Progress</span>
                    @elseif($ticket->status == 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($ticket->status == 'merged')
                        <span class="badge bg-dark">Merged</span>
                    @elseif($ticket->status == 'closed')
                        <span class="badge bg-success">Closed</span>
                    @elseif($ticket->status == 'merged')
                        <span class="badge bg-dark">
                            Merged
                        </span>
                    @endif
                </p>
                <p><b>Kategori :</b><br>{{ ucfirst($ticket->kategori) }}</p>
                <p><b>Dikerjakan Oleh :</b><br>{{ $ticket->technician->name ?? '-' }}</p>
                <p><b>Dibuat :</b><br>{{ $ticket->created_at->format('d-m-Y H:i') }}</p>
                <p><b>Durasi :</b><br>{{ $ticket->durasi_menit ?? '-'  }}</p>
            </div>
            @if($ticket->status == 'merged' && $ticket->mergedTicket)
            <div class="alert alert-secondary">
                <i class="fas fa-code-branch"></i>
                Ticket ini telah di merge ke:
                <a href="/admin/ticket/{{ $ticket->mergedTicket->id }}">
                    <b>{{ $ticket->mergedTicket->ticket_code }}</b>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

    {{--KOMENTAR --}}
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Komentar / Progress</h3>
        </div>
        <div class="card-body">
            @foreach($ticket->comments as $comment)
            <div class="card mb-2">
                <div class="card-body">
                    <b>{{ $comment->user->name }}</b>
                    <small class="text-muted">
                        {{ $comment->created_at->format('d-m-Y H:i') }}
                    </small>
                    <p class="mb-0">{{ $comment->comment }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Waktu</h3>
    </div>
    <div class="card-body">

        {{-- START --}}
        <p>
            <b>Mulai Dikerjakan :</b>
            <br>
            @if($ticket->started_at instanceof \Carbon\Carbon)
                {{ $ticket->started_at->format('d-m-Y H:i') }}
            @else
                <span class="text-danger">Belum dimulai</span>
            @endif
        </p>
        
        {{-- END --}}
        <p>
            <b>Selesai :</b><br>
            @if($ticket->resolved_at)
                {{ $ticket->resolved_at->format('d-m-Y H:i') }}
            @else
                <span class="text-warning">Belum selesai</span>
            @endif
        </p>

        <hr>

        {{-- DURASI --}}
      <p>
        <b>Durasi Pengerjaan :</b><br>

        @if($ticket->started_at)
            <span class="badge bg-primary">
                @if(!$ticket->resolved_at)
                    Sedang berjalan: 
                @endif
                {{ $ticket->durasi_menit }}
            </span>
        @else
            <span class="badge bg-secondary">
                Belum ada aktivitas
            </span>
        @endif
    </p>
    </div>
</div>
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <b>Histori Aktivitas</b>
            </h3>
        </div>
        <div class="card-body">
            @foreach(
                $ticket->timelines
                ->sortByDesc('created_at')
                as $timeline
            )
            <div class="timeline-item mb-3">
                <small class="text-muted">
                    {{ $timeline->created_at->format('d-m-Y H:i') }}
                </small>
                <br>
                {{ $timeline->description }}
            </div>
            <hr>
            @endforeach
        </div>
    </div>

    {{-- MERGED TICKETS --}}
@if($ticket->mergedTickets->count())
<div class="card mt-3">
    <div class="card-header bg-warning">
        <h3 class="card-title">
            Merged Tickets
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>User</th>
                    <th>Alasan Merge</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ticket->mergedTickets as $merge)
                <tr>
                    <td>
                        {{ $merge->ticket_code }}
                    </td>
                    <td>
                        {{ $merge->nama }}
                    </td>
                    <td>
                        {{ $merge->merge_reason }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>
</div>
@stop