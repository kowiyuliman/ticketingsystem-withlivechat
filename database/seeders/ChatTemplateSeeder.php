<?php

namespace Database\Seeders;

use App\Models\ChatTemplate;
use Illuminate\Database\Seeder;

class ChatTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'title'       => 'Mulai Pengerjaan',
                'category'    => 'on_progress',
                'message'     => 'Halo {user_name}, laporan kendala pada tiket #{ticket_code} sedang kami tangani. Mohon pastikan perangkat {nomor_laptop} tetap menyala dan terhubung ke jaringan.',
                'order_index' => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Konfirmasi Remote VNC',
                'category'    => 'on_progress',
                'message'     => 'Halo {user_name}, kami akan melakukan pengecekan secara remote via VNC/AnyDesk pada perangkat {nomor_laptop}. Mohon standby di depan laptop.',
                'order_index' => 2,
                'is_active'   => true,
            ],
            [
                'title'       => 'Menunggu Konfirmasi User',
                'category'    => 'pending',
                'message'     => 'Halo {user_name}, mohon konfirmasi kembali apakah kendala pada {nomor_laptop} masih terjadi atau apakah kami bisa melakukan pengecekan langsung sekarang?',
                'order_index' => 3,
                'is_active'   => true,
            ],
            [
                'title'       => 'Menunggu Sparepart / Vendor',
                'category'    => 'pending',
                'message'     => 'Tiket #{ticket_code} saat ini kami pending sementara karena menunggu ketersediaan sparepart/koordinasi vendor. Kami akan informasikan kembali segera setelah siap.',
                'order_index' => 4,
                'is_active'   => true,
            ],
            [
                'title'       => 'Pemberitahuan Selesai',
                'category'    => 'closed',
                'message'     => 'Halo {user_name}, pengerjaan tiket #{ticket_code} telah berhasil diselesaikan. Silakan dicek kembali pada perangkat {nomor_laptop}. Terima kasih telah menghubungi IT Support!',
                'order_index' => 5,
                'is_active'   => true,
            ],
            [
                'title'       => 'Bantuan Tambahan',
                'category'    => 'general',
                'message'     => 'Halo {user_name}, apakah ada kendala lain atau hal yang ingin ditanyakan terkait tiket #{ticket_code}?',
                'order_index' => 6,
                'is_active'   => true,
            ],
        ];

        foreach ($templates as $data) {
            ChatTemplate::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
