<?php

namespace Tests\Feature;

use App\Models\ChatTemplate;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_chat_templates_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        ChatTemplate::create([
            'title'     => 'On Progress Remote Check',
            'category'  => 'on_progress',
            'message'   => 'Halo {user_name}, kami akan remote {nomor_laptop}.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/chat-templates');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Template Chat');
        $response->assertSee('On Progress Remote Check');
    }

    public function test_non_admin_cannot_access_chat_templates()
    {
        $user = User::factory()->create(['role' => 'user']);
        $management = User::factory()->create(['role' => 'management']);

        $userResponse = $this->actingAs($user)->get('/admin/chat-templates');
        $userResponse->assertStatus(403);

        $mgmtResponse = $this->actingAs($management)->get('/admin/chat-templates');
        $mgmtResponse->assertStatus(403);
    }

    public function test_admin_can_store_new_chat_template()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/chat-templates', [
            'title'       => 'Menunggu Konfirmasi User',
            'category'    => 'pending',
            'message'     => 'Halo {user_name}, mohon konfirmasi kendala pada {nomor_laptop}.',
            'order_index' => 2,
            'is_active'   => 1,
        ]);

        $response->assertRedirect(route('admin.chat-templates.index', ['tab' => 'pending']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('chat_templates', [
            'title'       => 'Menunggu Konfirmasi User',
            'category'    => 'pending',
            'order_index' => 2,
            'is_active'   => true,
            'created_by'  => $admin->id,
        ]);
    }

    public function test_admin_can_update_chat_template()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $template = ChatTemplate::create([
            'title'       => 'Pemberitahuan Closed',
            'category'    => 'closed',
            'message'     => 'Tiket #{ticket_code} selesai.',
            'order_index' => 1,
            'is_active'   => true,
            'created_by'  => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put('/admin/chat-templates/' . $template->id, [
            'title'       => 'Pemberitahuan Closed Updated',
            'category'    => 'closed',
            'message'     => 'Tiket #{ticket_code} telah selesai dikerjakan oleh {admin_name}.',
            'order_index' => 5,
            'is_active'   => 1,
        ]);

        $response->assertRedirect(route('admin.chat-templates.index', ['tab' => 'closed']));
        $response->assertSessionHas('success');

        $template->refresh();
        $this->assertEquals('Pemberitahuan Closed Updated', $template->title);
        $this->assertEquals(5, $template->order_index);
    }

    public function test_admin_can_delete_chat_template()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $template = ChatTemplate::create([
            'title'     => 'Template to Delete',
            'category'  => 'general',
            'message'   => 'Sample message to delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete('/admin/chat-templates/' . $template->id);

        $response->assertRedirect(route('admin.chat-templates.index', ['tab' => 'general']));
        $this->assertDatabaseMissing('chat_templates', ['id' => $template->id]);
    }

    public function test_admin_can_toggle_chat_template_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $template = ChatTemplate::create([
            'title'     => 'Toggle Template',
            'category'  => 'general',
            'message'   => 'Toggle sample',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/chat-templates/' . $template->id . '/toggle');

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_active' => false]);

        $template->refresh();
        $this->assertFalse($template->is_active);
    }

    public function test_admin_ticket_show_renders_active_chat_templates()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'ticket_code'  => 'HD-TPL-001',
            'user_id'      => $user->id,
            'nama'         => 'Rian Firmansyah',
            'nomor_laptop' => 'LAP-0777',
            'kategori'     => 'software',
            'deskripsi'    => 'Need template test',
            'status'       => 'on_progress',
            'assigned_to'  => $admin->id,
            'created_by'   => $user->id,
        ]);

        ChatTemplate::create([
            'title'     => 'Quick Remote Pill',
            'category'  => 'on_progress',
            'message'   => 'Halo {user_name}, pengerjaan tiket #{ticket_code} pada {nomor_laptop} dimulai.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/ticket/show/' . $ticket->id);

        $response->assertStatus(200);
        $response->assertSee('⚡ Template:');
        $response->assertSee('Quick Remote Pill');
        $response->assertSee('Daftar Semua Template Chat');
    }

    public function test_chat_templates_active_cache_is_managed()
    {
        Cache::forget('chat_templates_active');

        $tpl = ChatTemplate::create([
            'title'     => 'Cache Test Template',
            'category'  => 'general',
            'message'   => 'Cache test message',
            'is_active' => true,
        ]);

        $templates = ChatTemplate::getActiveTemplates();
        $this->assertCount(1, $templates);
        $this->assertTrue(Cache::has('chat_templates_active'));

        // Updating or creating should invalidate cache
        $tpl->update(['title' => 'Cache Test Updated']);
        $this->assertFalse(Cache::has('chat_templates_active'));
    }
}

