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
}
