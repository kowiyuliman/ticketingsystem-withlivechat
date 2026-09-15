<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Lapor IT') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        @include('partials.floating_toast')
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <a href="https://wa.me/6281234567890" 
            class="wa-floating" 
            target="_blank">
                <i class="fab fa-whatsapp"></i>
                <span>Hubungi WA IT</span>
            </a>
            <div id="ticket-toast" class="ticket-toast onclick="openTicket()">
                    <div class="toast-header">
                        Ticket Baru
                    </div>
                    <div class="toast-body">
                        <b id="toast-ticket"></b><br>
                        <span id="toast-user"></span>
                    </div>
                </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Request izin notif browser
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    });
</script>

<audio id="notifSound">
    <source src="{{ asset('sounds/notification.mp3') }}" type="audio/mpeg">
</audio>

<script>
    let lastTicketId = 0;
    // aktifkan audio setelah user klik pertama
    document.addEventListener('click', function() {
        const audio = document.getElementById('notifSound');
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
        });
    }, { once: true });
    function checkNewTicket() {
        fetch('/admin/check-ticket')
        .then(res => res.json())
        .then(data => {

            if(data.ticket_id > lastTicketId){
                lastTicketId = data.ticket_id;
                // SOUND
                let audio = document.getElementById('notifSound');
                audio.play().catch(err => {
                    console.log('Audio blocked browser');
                });
                // POPUP BROWSER
                if(Notification.permission === "granted"){

                    new Notification("Ticket Baru Masuk", {
                        body: data.message,
                        icon: "/favicon.ico"
                    });
                }
                // TOAST HTML
                showFloatingNotif(data.message);
            }
        });
    }
    // polling tiap 5 detik
    setInterval(checkNewTicket, 5000);
</script>


<script>
function showFloatingNotif(message){
    let notif = document.createElement('div');
    notif.className = 'floating-ticket-notif';
    notif.innerHTML = `
        <b>Ticket Baru</b><br>
        ${message}
    `;
    document.body.appendChild(notif);
    setTimeout(() => {

        notif.remove();

    }, 5000);
}
</script>

<script>
    let lastTicketId = localStorage.getItem('lastTicketId') || 0;
    function checkNewTicket(){
        fetch('/admin/check-ticket')
        .then(res => res.json())
        .then(data => {
            if(!data.id) return;
            // ada ticket baru
            if(data.id > lastTicketId){
                localStorage.setItem('lastTicketId', data.id);
                lastTicketId = data.id;
                showTicketPopup(data);
            }
        })
        .catch(err => console.log(err));
    }
    function showTicketPopup(data){
        document.getElementById('toast-ticket').innerHTML =
            data.ticket_code;
        document.getElementById('toast-user').innerHTML =
            data.nama;
        let toast = document.getElementById('ticket-toast');
        toast.style.display = 'block';
        // play sound
        document.getElementById('notifSound').play();
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000);
    }

    // polling tiap 5 detik
    setInterval(checkNewTicket, 5000);
</script>       
@include('layouts.footer')    
    </body>
</html>


<script>
    function showNotification(message, url) {
        let notif = document.createElement('div');
        notif.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #343a40;
                color: white;
                padding: 15px;
                border-radius: 8px;
                z-index: 9999;
                cursor:pointer;
            ">
                ${message}
            </div>
        `;
        notif.onclick = () => window.location = url;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 5000);
    }
</script>

<script>
let lastTicketId = localStorage.getItem('lastTicketId') || 0;
function checkNewTicket(){
    fetch('/admin/check-ticket')
    .then(res => res.json())
    .then(data => {
        if(!data.id) return;
        // ada ticket baru
        if(data.id > lastTicketId){
            localStorage.setItem('lastTicketId', data.id);
            lastTicketId = data.id;
            showTicketPopup(data);
        }
    })
    .catch(err => console.log(err));
}
</script>

<script>
function showTicketPopup(data){
    document.getElementById('toast-ticket').innerHTML =
        data.ticket_code;
    document.getElementById('toast-user').innerHTML =
        data.nama;
    let toast = document.getElementById('ticket-toast');
    toast.style.display = 'block';
    // play sound
    document.getElementById('notifSound').play();
    setTimeout(() => {
        toast.style.display = 'none';
    }, 5000);
}
</script>


<script>
    let currentTicketId = null;
    function showTicketPopup(data){
        currentTicketId = data.id;
        ...
    }
</script>


<script>
    function openTicket(){
        if(currentTicketId){
            window.location.href =
                '/admin/ticket/show/' + currentTicketId;
        }
    }
    // polling tiap 5 detik
    setInterval(checkNewTicket, 10000);
</script>

<style>
.wa-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #25D366;
    color: white;
    padding: 12px 16px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: all 0.3s ease;
}

/* ICON */
.wa-floating i {
    font-size: 20px;
}

/* TEXT */
.wa-floating span {
    font-size: 14px;
}

/* HOVER EFFECT */
.wa-floating:hover {
    background: #1ebe5d;
    transform: scale(1.05);
}

.ticket-toast{
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    overflow: hidden;
    z-index: 99999;
    display: none;
    animation: slideUp 0.4s ease;
}

.toast-header{
    background: #007bff;
    color: white;
    padding: 12px;
    font-weight: bold;
}

.toast-body{
    padding: 15px;
    font-size: 14px;
}

@keyframes slideUp{
    from{
        transform: translateY(100%);
        opacity:0;
    }
    to{
        transform: translateY(0);
        opacity:1;
    }
}
</style>
