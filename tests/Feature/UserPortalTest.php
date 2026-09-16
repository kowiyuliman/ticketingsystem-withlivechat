<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_portal_renders_successfully()
    {
        $response = $this->get('/portal');

        $response->assertStatus(200);
        $response->assertSee('Lapor IT');
        $response->assertSee('Buat Laporan');
    }

    public function test_instant_ticket_submission()
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->post('/create-ticket', [
            'deskripsi' => 'Printer Epson di meja LAP-0253 error paper jam',
            'kategori' => 'hardware',
            'nomor_laptop' => 'LAP-0253',
            'ip_address' => '127.0.0.1',
            'nama' => 'Andika Pratama',
        ]);

        $this->assertDatabaseHas('tickets', [
            'nomor_laptop' => 'LAP-0253',
            'kategori' => 'hardware',
            'deskripsi' => 'Printer Epson di meja LAP-0253 error paper jam',
        ]);

        $ticket = Ticket::where('nomor_laptop', 'LAP-0253')->first();
        $response->assertRedirect(route('ticket.show', $ticket->id));
    }

    public function test_live_chat_comment_on_ticket()
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $ticket = Ticket::create([
            'ticket_code' => 'MPTB-TEST-001',
            'user_id' => $user->id,
            'nama' => 'Budi Santoso',
            'nomor_laptop' => 'LAP-0253',
            'ip_address' => '127.0.0.1',
            'no_whatsapp' => '08123456789',
            'deskripsi' => 'Koneksi LAN terputus',
            'status' => 'open',
            'kategori' => 'network',
        ]);

        $response = $this->post("/ticket/comment/{$ticket->id}", [
            'comment' => 'Mohon bantuan segera ya tim IT',
        ]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'comment' => 'Mohon bantuan segera ya tim IT',
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('success');
    }

    public function test_live_chat_comment_with_base64_attachment_and_purge()
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $ticket = Ticket::create([
            'ticket_code' => 'MPTB-TEST-002',
            'user_id' => $user->id,
            'nama' => 'Budi Santoso',
            'nomor_laptop' => 'LAP-0253',
            'ip_address' => '127.0.0.1',
            'deskripsi' => 'Error aplikasi SAP',
            'status' => 'open',
            'kategori' => 'software',
        ]);

        // Small 1x1 red pixel PNG base64
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $response = $this->post("/ticket/comment/{$ticket->id}", [
            'comment' => 'Ini screenshot errornya',
            'attachment_base64' => $base64Image,
        ]);

        $response->assertStatus(302);

        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($comment->attachment);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($comment->attachment));

        // Test Purge Command
        // Backdate the comment created_at date to 40 days ago
        $comment->created_at = \Carbon\Carbon::now()->subDays(40);
        $comment->save();

        $this->artisan('tickets:purge-old-attachments --days=30')
            ->assertExitCode(0);

        $comment->refresh();
        $this->assertNull($comment->attachment);
    }

    public function test_admin_can_send_live_chat_comment()
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'IT Support']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-ADMIN-CHAT-01',
            'user_id'      => $admin->id,
            'assigned_to'  => $admin->id,
            'nama'         => 'Pelapor Test',
            'nomor_laptop' => 'LAP-0099',
            'deskripsi'    => 'Chat test',
            'status'       => 'open',
            'kategori'     => 'software',
        ]);

        $response = $this->actingAs($admin)->post("/admin/ticket/comment/{$ticket->id}", [
            'comment' => 'Halo user, kami sedang memeriksa kendala Anda.',
        ]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'is_admin'  => true,
            'comment'   => 'Halo user, kami sedang memeriksa kendala Anda.',
        ]);

        $response->assertStatus(302);
    }

    public function test_admin_update_status_non_closed_stays_on_live_chat()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-TEST-003',
            'user_id'      => $admin->id,
            'assigned_to'  => $admin->id,
            'nama'         => 'Test User',
            'nomor_laptop' => 'LAP-001',
            'deskripsi'    => 'Kendala software',
            'status'       => 'open',
            'kategori'     => 'software',
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/ticket/show/{$ticket->id}")
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status' => 'on_progress',
                'kategori' => 'software',
            ]);

        $response->assertRedirect("/admin/ticket/show/{$ticket->id}");
        $ticket->refresh();
        $this->assertEquals('on_progress', $ticket->status);
    }

    public function test_admin_update_status_closed_redirects_to_tickets_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-TEST-004',
            'user_id'      => $admin->id,
            'assigned_to'  => $admin->id,
            'nama'         => 'Test User',
            'nomor_laptop' => 'LAP-002',
            'deskripsi'    => 'Kendala hardware',
            'status'       => 'on_progress',
            'kategori'     => 'hardware',
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/ticket/show/{$ticket->id}")
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status' => 'closed',
                'kategori' => 'hardware',
            ]);

        $response->assertRedirect('/admin/tickets');
        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
    }

    public function test_admin_update_status_pending_with_keterangan()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-TEST-005',
            'user_id'      => $admin->id,
            'assigned_to'  => $admin->id,
            'nama'         => 'Test User',
            'nomor_laptop' => 'LAP-005',
            'deskripsi'    => 'Printer rusak',
            'status'       => 'on_progress',
            'kategori'     => 'hardware',
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/ticket/show/{$ticket->id}")
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status' => 'pending',
                'kategori' => 'hardware',
                'keterangan' => 'Menunggu sparepart cartridge pengganti dari vendor',
            ]);

        $response->assertRedirect("/admin/ticket/show/{$ticket->id}");
        $ticket->refresh();
        $this->assertEquals('pending', $ticket->status);

        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status'    => 'pending',
        ]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'comment'   => '📌 Status tiket diubah menjadi [Pending]. Keterangan: Menunggu sparepart cartridge pengganti dari vendor',
        ]);
    }

    public function test_closed_ticket_blocks_user_comments_and_shows_notification_banner()
    {
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-CLOSED-001',
            'user_id'      => User::factory()->create(['role' => 'user'])->id,
            'nama'         => 'Closed Ticket User',
            'nomor_laptop' => 'LAP-CLOSED-1',
            'deskripsi'    => 'Tiket sudah selesai',
            'status'       => 'closed',
            'kategori'     => 'software',
        ]);

        $response = $this->get("/ticket/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('Tiket ini sudah ditutup. Silakan buat tiket lagi');
        $response->assertDontSee('id="chat-form"', false);

        $commentResponse = $this->post("/ticket/comment/{$ticket->id}", [
            'comment' => 'Pesan balasan dari user setelah closed',
        ]);

        $commentResponse->assertSessionHas('error', 'Tiket ini sudah ditutup. Silakan buat tiket baru.');
        $this->assertEquals(0, \App\Models\TicketComment::where('ticket_id', $ticket->id)->count());
    }

    public function test_tickets_are_strictly_isolated_by_laptop_sn()
    {
        $user = User::factory()->create();

        $ticket1 = Ticket::create([
            'ticket_code'  => 'MPTB-LAP-001-A',
            'user_id'      => $user->id,
            'nama'         => 'User LAP 1',
            'nomor_laptop' => 'LAP-0001',
            'deskripsi'    => 'Kendala di laptop 1',
            'status'       => 'open',
            'kategori'     => 'software',
        ]);

        $ticket2 = Ticket::create([
            'ticket_code'  => 'MPTB-LAP-002-B',
            'user_id'      => $user->id,
            'nama'         => 'User LAP 2',
            'nomor_laptop' => 'LAP-0002',
            'deskripsi'    => 'Kendala di laptop 2',
            'status'       => 'open',
            'kategori'     => 'hardware',
        ]);

        // Access portal as LAP-0001 via cookie
        $response1 = $this->withCookie('mptb_laptop_sn', 'LAP-0001')
            ->get('/portal');
        $response1->assertStatus(200);
        $response1->assertSee('MPTB-LAP-001-A');
        $response1->assertDontSee('MPTB-LAP-002-B');

        // Access portal as LAP-0002 via cookie
        $response2 = $this->withCookie('mptb_laptop_sn', 'LAP-0002')
            ->get('/portal');
        $response2->assertStatus(200);
        $response2->assertSee('MPTB-LAP-002-B');
        $response2->assertDontSee('MPTB-LAP-001-A');
    }

    public function test_set_laptop_switches_session_and_cookie()
    {
        $response = $this->post('/set-laptop', [
            'nomor_laptop' => 'LAP-0888',
        ]);

        $response->assertRedirect(route('portal', ['tab' => 'history']));
        $response->assertCookie('mptb_laptop_sn', 'LAP-0888');
        $response->assertSessionHas('mptb_laptop_sn', 'LAP-0888');
    }

    public function test_fetch_comments_includes_realtime_status_and_metadata()
    {
        $tech = User::factory()->create(['name' => 'Teknisi Handal', 'role' => 'admin']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-RT-001',
            'user_id'      => $tech->id,
            'assigned_to'  => $tech->id,
            'nama'         => 'User Realtime',
            'nomor_laptop' => 'LAP-0011',
            'deskripsi'    => 'Test status realtime',
            'status'       => 'pending',
            'status_reason' => 'Menunggu konfirmasi sparepart',
            'kategori'     => 'hardware',
        ]);

        $response = $this->get("/ticket/comments/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'status'          => 'pending',
            'status_label'    => 'pending',
            'status_reason'   => 'Menunggu konfirmasi sparepart',
            'kategori'        => 'hardware',
            'technician_name' => 'Teknisi Handal',
            'is_closed'       => false,
        ]);
    }

    public function test_delivery_and_read_receipts_workflow()
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'IT Specialist']);
        $user = User::factory()->create(['role' => 'user', 'name' => 'Pelapor Office']);

        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-RECEIPT-01',
            'user_id'      => $user->id,
            'assigned_to'  => $admin->id,
            'nama'         => 'Pelapor Office',
            'nomor_laptop' => 'LAP-0077',
            'deskripsi'    => 'Test Centang Chat',
            'status'       => 'open',
            'kategori'     => 'software',
        ]);

        // 1. User sends comment -> status 'sent' (1 checkmark)
        $this->post("/ticket/comment/{$ticket->id}", [
            'comment' => 'Halo apakah ada teknisi?',
        ]);

        $comment = \App\Models\TicketComment::where('ticket_id', $ticket->id)->first();
        $this->assertEquals('sent', $comment->read_status);

        // 2. User polls before admin opens -> still 'sent' & is_admin_active is false
        $response1 = $this->get("/ticket/comments/{$ticket->id}");
        $response1->assertJsonPath('comments.0.read_status', 'sent');
        $response1->assertJsonPath('is_admin_active', false);

        // 3. Admin opens the ticket detail page -> user comments become 'read' (2 blue checkmarks)
        $this->actingAs($admin)->get("/admin/ticket/show/{$ticket->id}");

        $comment->refresh();
        $this->assertNotNull($comment->read_at);
        $this->assertEquals('read', $comment->read_status);

        // 4. User polls again -> comment is 'read' & is_admin_active is true
        $response2 = $this->get("/ticket/comments/{$ticket->id}");
        $response2->assertJsonPath('comments.0.read_status', 'read');
        $response2->assertJsonPath('is_admin_active', true);
    }

    public function test_ticket_submission_requires_category()
    {
        $response = $this->post('/create-ticket', [
            'deskripsi' => 'Printer tidak bisa mencetak dokumen sama sekali',
            'nomor_laptop' => 'LAP-0012',
            'nama' => 'User Test',
        ]);

        $response->assertSessionHasErrors(['kategori']);
        $this->assertDatabaseMissing('tickets', [
            'nomor_laptop' => 'LAP-0012',
            'deskripsi' => 'Printer tidak bisa mencetak dokumen sama sekali',
        ]);
    }

    public function test_user_ticket_show_displays_unconnected_presence_before_admin_opens()
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'User Test']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-PRESENCE-01',
            'user_id'      => $user->id,
            'nama'         => 'User Test',
            'nomor_laptop' => 'LAP-0099',
            'deskripsi'    => 'Test tampilan kehadiran admin',
            'status'       => 'open',
            'kategori'     => 'hardware',
        ]);

        // When user opens ticket and admin has NEVER opened it
        $response = $this->get("/ticket/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('Admin Belum Terhubung');
        $response->assertSee('Menunggu admin / teknisi membuka chat ini...');
    }

    public function test_ajax_live_chat_comment_returns_json_and_comment_payload()
    {
        $user = User::factory()->create(['name' => 'User Seamless']);
        $ticket = Ticket::create([
            'ticket_code'  => 'MPTB-AJAX-01',
            'user_id'      => $user->id,
            'nama'         => 'User Seamless',
            'nomor_laptop' => 'LAP-0033',
            'deskripsi'    => 'Test AJAX comment',
            'status'       => 'open',
            'kategori'     => 'software',
        ]);

        $response = $this->postJson("/ticket/comment/{$ticket->id}", [
            'comment' => 'Pesan tanpa reload layar',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'comment' => [
                'comment'     => 'Pesan tanpa reload layar',
                'is_user'     => true,
                'read_status' => 'sent',
            ],
        ]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'comment'   => 'Pesan tanpa reload layar',
        ]);
    }

    public function test_ticket_code_generation_format_and_daily_sequence()
    {
        $today = date('Ymd');

        // HW Category sequence (3-digit padding: 001, 002)
        $codeHw1 = Ticket::generateTicketCode('hardware');
        $this->assertEquals("MPTB-IT-{$today}-HW-001", $codeHw1);

        $user = User::factory()->create();
        Ticket::create([
            'ticket_code'  => $codeHw1,
            'user_id'      => $user->id,
            'nama'         => 'User HW',
            'deskripsi'    => 'Kendala HW 1',
            'kategori'     => 'hardware',
        ]);

        $codeHw2 = Ticket::generateTicketCode('hardware');
        $this->assertEquals("MPTB-IT-{$today}-HW-002", $codeHw2);

        // SW Category sequence
        $codeSw1 = Ticket::generateTicketCode('software');
        $this->assertEquals("MPTB-IT-{$today}-SW-001", $codeSw1);

        // NTW Category sequence
        $codeNtw1 = Ticket::generateTicketCode('network');
        $this->assertEquals("MPTB-IT-{$today}-NTW-001", $codeNtw1);

        // OTH Category sequence
        $codeOth1 = Ticket::generateTicketCode('other');
        $this->assertEquals("MPTB-IT-{$today}-OTH-001", $codeOth1);
    }

    public function test_admin_updating_category_regenerates_ticket_code_and_syncs()
    {
        $today = date('Ymd');
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Test']);
        $user = User::factory()->create(['role' => 'user', 'name' => 'Karyawan Test']);

        // 1. Initial creation with hardware -> MPTB-IT-YYYYMMDD-HW-001
        $codeHw = Ticket::generateTicketCode('hardware');
        $ticket = Ticket::create([
            'ticket_code' => $codeHw,
            'user_id'     => $user->id,
            'nama'        => 'Karyawan Test',
            'deskripsi'   => 'Layar bergaris',
            'kategori'    => 'hardware',
            'status'      => 'open',
        ]);

        $this->assertEquals("MPTB-IT-{$today}-HW-001", $ticket->ticket_code);

        // 2. Admin updates category from 'hardware' to 'software'
        $response = $this->actingAs($admin)->post("/admin/ticket/update/{$ticket->id}", [
            'status'   => 'on_progress',
            'kategori' => 'software',
        ]);

        $ticket->refresh();
        $this->assertEquals('software', $ticket->kategori);
        $this->assertEquals("MPTB-IT-{$today}-SW-001", $ticket->ticket_code);

        // Verify history and live chat comments logged
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'keterangan' => "Admin Admin Test mengubah kategori dari Hardware ke Software. Nomor tiket diperbarui: #MPTB-IT-{$today}-SW-001",
        ]);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'comment'   => "🔄 Kategori kendala diubah dari [Hardware] menjadi [Software]. Nomor tiket diperbarui menjadi #MPTB-IT-{$today}-SW-001",
        ]);

        // 3. Verify fetchComments returns updated ticket_code and kategori
        $fetchRes = $this->actingAs($user)->getJson("/ticket/comments/{$ticket->id}");
        $fetchRes->assertStatus(200);
        $fetchRes->assertJson([
            'ticket_code' => "MPTB-IT-{$today}-SW-001",
            'kategori'    => 'software',
            'status'      => 'on_progress',
        ]);
    }

    public function test_user_portal_loads_all_registered_assets_including_peripherals()
    {
        $employeeName = 'Ahmad Fathoni Test';
        $laptopSn = 'LAP-TEST-99';

        \App\Models\Inventory::where('sn', 'LIKE', 'LAP-TEST%')
            ->orWhere('pengguna', $employeeName)
            ->orWhere('sn', 'LAP-9999')
            ->delete();

        // Create multiple inventory assets for this employee
        \App\Models\Inventory::create([
            'jenis'       => 'Laptop',
            'merk'        => 'Lenovo ThinkPad',
            'sn'          => $laptopSn,
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        \App\Models\Inventory::create([
            'jenis'       => 'Mouse',
            'merk'        => 'Logitech B100',
            'sn'          => 'MOS-TEST-88',
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        \App\Models\Inventory::create([
            'jenis'       => 'Headset',
            'merk'        => 'Jabra Evolve 20',
            'sn'          => 'HED-TEST-77',
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        \App\Models\Inventory::create([
            'jenis'       => 'LAN Adapter',
            'merk'        => 'TP-Link UE300',
            'sn'          => 'LAN-TEST-66',
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        \App\Models\Inventory::create([
            'jenis'       => 'USB Audio',
            'merk'        => 'Vention USB Sound',
            'sn'          => 'AUD-TEST-55',
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        \App\Models\Inventory::create([
            'jenis'       => 'HP Root',
            'merk'        => 'Xiaomi Redmi 9A Root',
            'sn'          => 'HPR-TEST-44',
            'pengguna'    => $employeeName,
            'kepemilikan' => 'PTMPTB',
            'lokasi'      => 'Ruang Kerja',
            'kondisi'     => 'Baik',
            'status'      => 'Aktif',
        ]);

        // Request portal with laptop SN
        $response = $this->withSession(['mptb_laptop_sn' => $laptopSn])
            ->get(route('portal', ['tab' => 'assets']));

        $response->assertStatus(200);
        $response->assertSee('Perangkat &amp; Aset IT Terdaftar', false);
        $response->assertSee('LAP-TEST-99');
        $response->assertSee('MOS-TEST-88');
        $response->assertSee('HED-TEST-77');
        $response->assertSee('LAN-TEST-66');
        $response->assertSee('AUD-TEST-55');
        $response->assertSee('HPR-TEST-44');
        $response->assertSee('Logitech B100');
        $response->assertSee('Jabra Evolve 20');
        $response->assertSee('TP-Link UE300');
        $response->assertSee('Vention USB Sound');
        $response->assertSee('Xiaomi Redmi 9A Root');

        // Cleanup
        \App\Models\Inventory::where('pengguna', $employeeName)->delete();
    }
}

