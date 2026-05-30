<?php

namespace Database\Seeders;

use App\Models\StudyTool;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use HasFactory, WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        StudyTool::create([
            'id' => Str::uuid(),
            'user_id' => '019df165-a29c-71dc-88a2-2b71e0b7f57c',
            'note_id' => '019df166-769b-71df-bde4-cd473413a98e',
            'type' => 'flashcard',
            'status' => 'completed',
            'content' => [
                'title' => 'Kuis Dasar Laravel',
                'questions' => [
                    ['q' => 'Versi terbaru Laravel?', 'a' => '12'],
                    ['q' => 'Bahasa yang digunakan?', 'a' => 'PHP'],
                ],
            ],
        ]);
    }
}
