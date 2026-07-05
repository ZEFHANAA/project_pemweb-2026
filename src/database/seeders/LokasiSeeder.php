<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan kita mengambil user admin yang sudah dibuat oleh UserSeeder
        $user = User::where('email', 'admin@admin.com')->first();

        if (!$user) {
            return;
        }

        $dummyLokasis = [
            [
                'nama_lokasi' => 'Candi Borobudur',
                'latitude' => -7.6078738,
                'longitude' => 110.2037513,
                'kategori' => 'Budaya',
                'deskripsi' => 'Candi Buddha terbesar di dunia yang terletak di Magelang, Jawa Tengah.',
            ],
            [
                'nama_lokasi' => 'Pantai Kuta',
                'latitude' => -8.718465,
                'longitude' => 115.168641,
                'kategori' => 'Pantai',
                'deskripsi' => 'Pantai ikonik di Bali yang terkenal dengan sunset dan ombak selancarnya.',
            ],
            [
                'nama_lokasi' => 'Gunung Bromo',
                'latitude' => -7.942493,
                'longitude' => 112.953012,
                'kategori' => 'Gunung',
                'deskripsi' => 'Gunung berapi aktif di Jawa Timur dengan pemandangan sunrise yang luar biasa.',
            ],
            [
                'nama_lokasi' => 'Monas (Monumen Nasional)',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'kategori' => 'Kota',
                'deskripsi' => 'Ikon kota Jakarta yang dibangun untuk mengenang perlawanan dan perjuangan rakyat Indonesia.',
            ],
            [
                'nama_lokasi' => 'Raja Ampat',
                'latitude' => -0.233333,
                'longitude' => 130.516667,
                'kategori' => 'Alam',
                'deskripsi' => 'Gugusan pulau pari di Papua Barat dengan kekayaan biota laut terlengkap di bumi.',
            ],
            [
                'nama_lokasi' => 'Danau Toba',
                'latitude' => 2.6166700,
                'longitude' => 98.6666700,
                'kategori' => 'Alam',
                'deskripsi' => 'Danau vulkanik terbesar di dunia yang terletak di Sumatera Utara.',
            ],
            [
                'nama_lokasi' => 'Jalan Malioboro',
                'latitude' => -7.792569,
                'longitude' => 110.365825,
                'kategori' => 'Kuliner',
                'deskripsi' => 'Kawasan belanja dan kuliner legendaris di jantung kota Yogyakarta.',
            ]
        ];

        foreach ($dummyLokasis as $lokasi) {
            Lokasi::firstOrCreate(
                ['nama_lokasi' => $lokasi['nama_lokasi']], 
                [
                    'user_id'   => $user->id,
                    'latitude'  => $lokasi['latitude'],
                    'longitude' => $lokasi['longitude'],
                    'kategori'  => $lokasi['kategori'],
                    'deskripsi' => $lokasi['deskripsi'],
                ]
            );
        }
    }
}
