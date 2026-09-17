<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LegalCase;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

class ConversationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $riko = User::where('email', 'klien@sahabathukum.test')->first();
        $advokat = User::where('email', 'advokat@sahabathukum.test')->first();
        $perkara1 = LegalCase::where('case_number', '023/Pdt.G/2025/PN.Sbg')->first();

        if (!$riko || !$advokat) {
            return;
        }

        $conversation = Conversation::firstOrCreate(
            [
                'client_id' => $riko->id,
                'lawyer_id' => $advokat->id,
                'case_id'   => $perkara1?->id,
            ],
            [
                'title'           => '023/Pdt.G/2025/PN.Sbg — Sengketa Tanah Warisan',
                'status'          => 'active',
                'last_message_at' => Carbon::parse('2025-08-12 09:34:00'),
            ]
        );

        // Seed messages matching Figma screenshot if empty
        if ($conversation->messages()->count() === 0) {
            $conversation->messages()->create([
                'sender_id'  => $advokat->id,
                'body'       => 'Selamat pagi, Bapak Riko. Saya ingin mengonfirmasi jadwal konsultasi besok pukul 10.00 di kantor. Apakah Bapak dapat hadir?',
                'is_read'    => true,
                'read_at'    => Carbon::parse('2025-08-12 09:20:00'),
                'created_at' => Carbon::parse('2025-08-12 09:15:00'),
            ]);

            $conversation->messages()->create([
                'sender_id'  => $riko->id,
                'body'       => 'Selamat pagi, Bu Dewi. Ya, saya dapat hadir. Apakah ada dokumen yang perlu saya bawa?',
                'is_read'    => true,
                'read_at'    => Carbon::parse('2025-08-12 09:33:00'),
                'created_at' => Carbon::parse('2025-08-12 09:32:00'),
            ]);

            $conversation->messages()->create([
                'sender_id'  => $advokat->id,
                'body'       => 'Tolong bawa KTP asli, Kartu Keluarga, dan dokumen kepemilikan tanah yang Bapak miliki. Jika ada surat-surat terkait tanah tersebut, harap dibawa semuanya.',
                'is_read'    => true,
                'read_at'    => Carbon::parse('2025-08-12 09:35:00'),
                'created_at' => Carbon::parse('2025-08-12 09:34:00'),
            ]);
        }
    }
}
