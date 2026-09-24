<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketComment;

class AdminTicketNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure role gates are initialized
        \Illuminate\Support\Facades\Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });
    }

    public function test_unread_count_endpoint_returns_false_when_clean(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'has_unread'         => false,
                'total_unread'       => 0,
                'open_tickets_count' => 0,
                'unread_chats_count' => 0,
            ]);
    }

    public function test_unread_count_triggers_when_open_ticket_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'   => 'HD-260917-001',
            'user_id'       => $user->id,
            'nama'          => 'Budi',
            'nomor_laptop'  => 'LAP-0073',
            'ip_address'    => '192.168.200.30',
            'nomor_meja'    => '-',
            'nomor_ruangan' => '-',
            'no_whatsapp'   => '-',
            'deskripsi'     => 'Keyboard tidak merespon saat ditekan',
            'kategori'      => 'hardware',
            'status'        => 'open',
            'created_by'    => $user->id,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'has_unread'         => true,
                'total_unread'       => 1,
                'open_tickets_count' => 1,
                'unread_chats_count' => 0,
            ]);
    }

    public function test_unread_count_triggers_when_user_replies_in_livechat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'   => 'NW-260917-002',
            'user_id'       => $user->id,
            'nama'          => 'Siti',
            'nomor_laptop'  => 'LAP-0009',
            'ip_address'    => '192.168.200.23',
            'nomor_meja'    => '-',
            'nomor_ruangan' => '-',
            'no_whatsapp'   => '-',
            'deskripsi'     => 'Koneksi LAN putus-nyambung',
            'kategori'      => 'network',
            'status'        => 'on_progress',
            'assigned_to'   => $admin->id,
            'created_by'    => $user->id,
        ]);

        // User posts a live chat reply
        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Pak apakah teknisi sudah jalan ke meja saya?',
            'read_at'   => null,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'has_unread'         => true,
                'total_unread'       => 1,
                'open_tickets_count' => 0,
                'unread_chats_count' => 1,
            ]);
    }

    public function test_admin_viewing_ticket_marks_user_comments_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'   => 'SW-260917-003',
            'user_id'       => $user->id,
            'nama'          => 'Andi',
            'nomor_laptop'  => 'LAP-0229',
            'ip_address'    => '192.168.200.29',
            'nomor_meja'    => '-',
            'nomor_ruangan' => '-',
            'no_whatsapp'   => '-',
            'deskripsi'     => 'Aplikasi error 500',
            'kategori'      => 'software',
            'status'        => 'on_progress',
            'assigned_to'   => $admin->id,
            'created_by'    => $user->id,
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Sudah saya kirim screenshot ya mas',
            'read_at'   => null,
        ]);

        $this->assertNull($comment->read_at);

        // Admin opens the ticket detail page
        $showResponse = $this->actingAs($admin)->get("/admin/ticket/show/{$ticket->id}");
        $showResponse->assertStatus(200);

        // Comment should now have read_at timestamp filled
        $comment->refresh();
        $this->assertNotNull($comment->read_at);

        // Check unread count should now be 0
        $unreadResponse = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');
        $unreadResponse->assertJson([
            'has_unread'         => false,
            'unread_chats_count' => 0,
        ]);
    }

    public function test_fetch_tickets_returns_all_realtime_status_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        Ticket::create([
            'ticket_code'  => 'HD-OPEN-001',
            'user_id'      => $user->id,
            'nama'         => 'Open User',
            'nomor_laptop' => 'LAP-001',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Open issue',
            'status'       => 'open',
            'created_by'   => $user->id,
        ]);

        Ticket::create([
            'ticket_code'  => 'SW-PROG-002',
            'user_id'      => $user->id,
            'nama'         => 'Progress User',
            'nomor_laptop' => 'LAP-002',
            'kategori'     => 'software',
            'deskripsi'    => 'Progress issue',
            'status'       => 'on_progress',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        Ticket::create([
            'ticket_code'  => 'NW-PEND-003',
            'user_id'      => $user->id,
            'nama'         => 'Pending User',
            'nomor_laptop' => 'LAP-003',
            'kategori'     => 'network',
            'deskripsi'    => 'Pending issue',
            'status'       => 'pending',
            'reason_text'  => 'Menunggu sparepart',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        Ticket::create([
            'ticket_code'  => 'HD-CANC-004',
            'user_id'      => $user->id,
            'nama'         => 'Cancel User',
            'nomor_laptop' => 'LAP-004',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Cancelled issue',
            'status'       => 'cancelled',
            'reason_text'  => 'Dibatalkan user',
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/ticket/fetch');

        $response->assertStatus(200)
            ->assertJson([
                'counts' => [
                    'open'     => 1,
                    'progress' => 1,
                    'pending'  => 1,
                    'closed'   => 0,
                    'cancel'   => 1,
                ],
            ]);

        $data = $response->json();
        $this->assertCount(1, $data['tickets']['open']);
        $this->assertCount(1, $data['tickets']['progress']);
        $this->assertCount(1, $data['tickets']['pending']);
        $this->assertCount(1, $data['tickets']['cancel']);
        $this->assertEquals('HD-OPEN-001', $data['tickets']['open'][0]['ticket_code']);
        $this->assertEquals('SW-PROG-002', $data['tickets']['progress'][0]['ticket_code']);
        $this->assertEquals('NW-PEND-003', $data['tickets']['pending'][0]['ticket_code']);
        $this->assertEquals('HD-CANC-004', $data['tickets']['cancel'][0]['ticket_code']);
    }

    public function test_admin_cannot_change_status_of_closed_ticket_to_open(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-CLOSED-001',
            'user_id'      => $user->id,
            'nama'         => 'Closed User',
            'nomor_laptop' => 'LAP-001',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Closed hardware issue',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/ticket/show/{$ticket->id}")
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status'   => 'open',
                'kategori' => 'hardware',
            ]);

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
    }

    public function test_admin_can_update_category_of_closed_ticket(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-CLOSED-002',
            'user_id'      => $user->id,
            'nama'         => 'Closed User 2',
            'nomor_laptop' => 'LAP-002',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Hardware turned out to be software issue',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/ticket/show/{$ticket->id}")
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status'   => 'closed',
                'kategori' => 'software',
            ]);

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertEquals('software', $ticket->kategori);
        $this->assertStringContainsString('-SW-', $ticket->ticket_code);
    }

    public function test_admin_cannot_send_chat_message_on_closed_ticket(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-CLOSED-003',
            'user_id'      => $user->id,
            'nama'         => 'Closed User 3',
            'nomor_laptop' => 'LAP-003',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Closed chat test',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/ticket/comment/{$ticket->id}", [
                'comment' => 'Admin trying to chat on closed ticket',
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Tiket telah ditutup. Percakapan telah diarsipkan.']);

        $this->assertEquals(0, TicketComment::where('ticket_id', $ticket->id)->count());
    }

    public function test_admin_closed_ticket_view_shows_disabled_status_and_archived_chat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-CLOSED-004',
            'user_id'      => $user->id,
            'nama'         => 'Closed User 4',
            'nomor_laptop' => 'LAP-004',
            'kategori'     => 'hardware',
            'deskripsi'    => 'View closed test',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/ticket/show/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertSee('disabled', false);
        $response->assertSee('Tiket telah ditutup (Closed). Percakapan telah diarsipkan.');
        $response->assertDontSee('id="admin-chat-form"', false);
    }

    public function test_admin_can_delete_ticket_and_cascade_related_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-DEL-001',
            'user_id'      => $user->id,
            'nama'         => 'Delete User',
            'nomor_laptop' => 'LAP-0099',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Delete me',
            'status'       => 'open',
            'created_by'   => $user->id,
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Sample comment to be deleted',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/ticket/delete/{$ticket->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('ticket_comments', ['id' => $comment->id]);
    }

    public function test_admin_tickets_index_shows_delete_action_only_for_closed_tickets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $openTicket = Ticket::create([
            'ticket_code'  => 'HD-OPEN-TEST',
            'user_id'      => $user->id,
            'nama'         => 'Open User',
            'nomor_laptop' => 'LAP-0100',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Open action test',
            'status'       => 'open',
            'created_by'   => $user->id,
        ]);

        $closedTicket = Ticket::create([
            'ticket_code'  => 'HD-CLOSED-TEST',
            'user_id'      => $user->id,
            'nama'         => 'Closed User',
            'nomor_laptop' => 'LAP-0101',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Closed action test',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/tickets');

        $response->assertStatus(200);
        // Closed ticket has delete form
        $response->assertSee('/admin/ticket/delete/' . $closedTicket->id, false);
        // Open ticket does NOT have delete form
        $response->assertDontSee('/admin/ticket/delete/' . $openTicket->id, false);
    }

    public function test_admin_can_download_vnc_config_for_remote_desktop(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-VNC-001',
            'user_id'      => $user->id,
            'nama'         => 'VNC User',
            'nomor_laptop' => 'LAP-0077',
            'ip_address'   => '192.168.200.55',
            'kategori'     => 'hardware',
            'deskripsi'    => 'VNC link test',
            'status'       => 'open',
            'created_by'   => $user->id,
        ]);

        // Check vnc download endpoint
        $downloadResponse = $this->actingAs($admin)->get("/admin/ticket/{$ticket->id}/vnc");
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Type', 'application/x-vnc');
        $this->assertStringContainsString('Host=192.168.200.55', $downloadResponse->getContent());
        $this->assertStringContainsString('Port=5900', $downloadResponse->getContent());

        // Check show page
        $showResponse = $this->actingAs($admin)->get("/admin/ticket/show/{$ticket->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee("vnc://192.168.200.55", false);
        $showResponse->assertSee('TightVNC', false);

        // Check direct launch-vnc endpoint
        $launchResponse = $this->actingAs($admin)->postJson("/admin/ticket/{$ticket->id}/launch-vnc");
        $launchResponse->assertStatus(200);
        $launchResponse->assertJson([
            'success' => true,
            'ip'      => '192.168.200.55',
        ]);
    }

    public function test_admin_can_view_category_tickets_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $swTicket = Ticket::create([
            'ticket_code'  => 'SW-CAT-001',
            'user_id'      => $user->id,
            'nama'         => 'Software User',
            'nomor_laptop' => 'LAP-SW-01',
            'ip_address'   => '192.168.200.11',
            'kategori'     => 'software',
            'deskripsi'    => 'Kendala software excel crash',
            'status'       => 'open',
            'created_by'   => $user->id,
        ]);

        $hwTicket = Ticket::create([
            'ticket_code'  => 'HD-CAT-002',
            'user_id'      => $user->id,
            'nama'         => 'Hardware User',
            'nomor_laptop' => 'LAP-HW-02',
            'ip_address'   => '192.168.200.12',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Kendala hardware mouse rusak',
            'status'       => 'on_progress',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/tickets/category/software');

        $response->assertStatus(200);
        $response->assertSee('Tiket Kategori: Software');
        $response->assertSee('SW-CAT-001');
        $response->assertSee('Kendala software excel crash');
        $response->assertDontSee('HD-CAT-002');
    }

    public function test_admin_can_download_vnc_setup_registry_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/vnc/setup');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="setup_tightvnc_protocol.reg"');
        $this->assertStringContainsString('URL:TightVNC Protocol', $response->getContent());
        $this->assertStringContainsString('tvnviewer.exe', $response->getContent());
    }

    public function test_admin_can_download_vnc_installer_zip(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/vnc/installer');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=TightVNC_Setup_Laptop.zip');
    }

    public function test_unread_count_excludes_closed_and_cancelled_tickets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $closedTicket = Ticket::create([
            'ticket_code'  => 'HD-CLS-UNREAD',
            'user_id'      => $user->id,
            'nama'         => 'Closed User',
            'nomor_laptop' => 'LAP-CLS',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Closed ticket with unread message',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        TicketComment::create([
            'ticket_id' => $closedTicket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Unread comment on closed ticket',
            'read_at'   => null,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'has_unread'         => false,
                'total_unread'       => 0,
                'unread_chats_count' => 0,
            ]);
    }

    public function test_closing_ticket_marks_unread_user_comments_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'SW-PROG-CLS',
            'user_id'      => $user->id,
            'nama'         => 'Prog User',
            'nomor_laptop' => 'LAP-PROG',
            'kategori'     => 'software',
            'deskripsi'    => 'Prog ticket to be closed',
            'status'       => 'on_progress',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Unread user reply before closing',
            'read_at'   => null,
        ]);

        $this->assertNull($comment->read_at);

        $response = $this->actingAs($admin)
            ->post("/admin/ticket/update/{$ticket->id}", [
                'status'   => 'closed',
                'kategori' => 'software',
            ]);

        $response->assertRedirect('/admin/tickets');

        $comment->refresh();
        $this->assertNotNull($comment->read_at);
        $this->assertEquals('read', $comment->read_status);

        $unreadResponse = $this->actingAs($admin)->getJson('/admin/notifications/unread-count');
        $unreadResponse->assertJson([
            'has_unread'         => false,
            'total_unread'       => 0,
            'unread_chats_count' => 0,
        ]);
    }

    public function test_closed_tickets_tab_shows_unread_comments_badge_and_fetch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $closedTicket = Ticket::create([
            'ticket_code'  => 'HD-CLS-BADGE',
            'user_id'      => $user->id,
            'nama'         => 'Closed Badge User',
            'nomor_laptop' => 'LAP-CLS-01',
            'kategori'     => 'hardware',
            'deskripsi'    => 'Closed badge test',
            'status'       => 'closed',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        TicketComment::create([
            'ticket_id' => $closedTicket->id,
            'user_id'   => $user->id,
            'is_admin'  => false,
            'comment'   => 'Unread comment on closed ticket badge',
            'read_at'   => null,
        ]);

        // Check Blade view
        $indexResponse = $this->actingAs($admin)->get('/admin/tickets?tab=closed');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('HD-CLS-BADGE');
        $indexResponse->assertSee('1 pesan baru');

        // Check fetch API
        $fetchResponse = $this->actingAs($admin)->getJson('/admin/ticket/fetch');
        $fetchResponse->assertStatus(200);
        $fetchData = $fetchResponse->json();
        $this->assertEquals(1, $fetchData['tickets']['closed'][0]['unread_comments_count']);
    }
}



