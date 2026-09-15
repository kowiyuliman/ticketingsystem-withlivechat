@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right') 

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif

        {{-- Notifikasi Navbar Admin --}}
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Notifikasi Sistem">
                <i class="far fa-bell"></i>
                @php
                    $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
                    $totalCount = auth()->user() ? auth()->user()->notifications()->count() : 0;
                    $notifications = auth()->user() ? auth()->user()->notifications()->latest()->take(5)->get() : collect();
                @endphp
                @if($unreadCount > 0)
                    <span class="badge badge-danger navbar-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @elseif($totalCount > 0)
                    <span class="badge badge-info navbar-badge">{{ $totalCount > 99 ? '99+' : $totalCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-lg border-0 p-0 overflow-hidden" style="min-width: 310px; border-radius: 14px;">
                <div class="bg-light px-3 py-2.5 border-bottom d-flex align-items-center justify-content-between">
                    <span class="font-weight-bold text-dark text-xs uppercase" style="font-size: 11px;">
                        <i class="far fa-bell text-primary mr-1"></i> Notifikasi
                    </span>
                    <span class="badge badge-info px-2 py-0.5" style="font-size: 10px;">{{ $totalCount }} Total</span>
                </div>

                <div class="notification-list overflow-auto" style="max-height: 280px;">
                    @forelse($notifications as $notif)
                        @php
                            $notifData = $notif->data;
                            $msg = $notifData['message'] ?? ($notifData['keterangan'] ?? 'Notifikasi baru');
                            $url = $notifData['url'] ?? ('/notification/' . $notif->id);
                            $isUnread = is_null($notif->read_at);
                        @endphp
                        <a href="{{ url($url) }}" class="dropdown-item px-3 py-2.5 border-bottom d-flex align-items-start text-wrap {{ $isUnread ? 'bg-light font-weight-bold' : '' }}">
                            <div class="rounded-circle {{ $isUnread ? 'bg-primary' : 'bg-secondary' }} text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 mr-2 mt-1" style="width: 24px; height: 24px; font-size: 10px; line-height: 1;">
                                <i class="fas {{ $isUnread ? 'fa-envelope' : 'fa-check' }}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="text-xs text-dark" style="font-size: 12px; line-height: 1.3;">
                                    {{ $msg }}
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                    <i class="far fa-clock mr-1"></i> {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-4 px-3" style="font-size: 12px;">
                            <i class="far fa-bell-slash d-block text-lg mb-1 opacity-50"></i>
                            Tidak ada notifikasi
                        </div>
                    @endforelse
                </div>

                @if($totalCount > 0)
                    <div class="p-2 bg-light border-top text-center">
                        <form action="/notifications/clear" method="POST" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger btn-block font-weight-bold py-1.5 shadow-2xs">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Notifikasi
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </li>
    </ul>
</nav>


@section('CSS')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .notification-dropdown{
        width:400px;
        max-height:450px;
        overflow-y:auto;
    }

    .notification-item{
        white-space:normal;
        line-height:1.4;
    }
    .ticket-toast{
        position:fixed;
        top:20px;
        right:20px;
        width:320px;
        background:white;
        border-left:5px solid #28a745;
        border-radius:12px;
        padding:15px;
        box-shadow:
            0 10px 25px rgba(0,0,0,.15);
        z-index:999999;
        cursor:pointer;
        animation:slideIn .3s ease;
    }

    .ticket-toast-title{
        font-weight:bold;
        margin-bottom:5px;
    }

    .ticket-toast-body{
        font-size:13px;
    }

    .modal-body{
        max-height: 550px;
        overflow-y: auto;
    }
</style>

@stop

@section('JS')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    function showNotification(message, url) {
        const notif = document.createElement('div');
        notif.className = 'ticket-toast';
        notif.innerHTML = `
            <div class="ticket-toast-title">
                Ticket Baru
            </div>
            <div class="ticket-toast-body">
                ${message}
            </div>
        `;

        notif.onclick = () => {
            window.location = url;
        };

        document.body.appendChild(notif);

        setTimeout(() => {
            notif.remove();
        }, 5000);
    }
</script>

@stop
