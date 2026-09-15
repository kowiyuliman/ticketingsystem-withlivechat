<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PurgeOldAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:purge-old-attachments {--days=30 : Retensi hari sebelum foto lampiran dibersihkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan otomatis foto lampiran chat tiket lama untuk menghemat penyimpanan disk server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Menjalankan pembersihan lampiran foto yang lebih tua dari {$days} hari (sebelum {$cutoffDate->format('Y-m-d H:i:s')})...");

        $commentsWithAttachments = TicketComment::whereNotNull('attachment')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $count = 0;
        $freedBytes = 0;

        foreach ($commentsWithAttachments as $comment) {
            $filePath = $comment->attachment;
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                $freedBytes += Storage::disk('public')->size($filePath);
                Storage::disk('public')->delete($filePath);
            }

            $comment->update(['attachment' => null]);
            $count++;
        }

        $freedMb = number_format($freedBytes / 1024 / 1024, 2);
        $this->info("✅ Pembersihan selesai! {$count} file lampiran dihapus, menghemat {$freedMb} MB disk storage.");

        return Command::SUCCESS;
    }
}

