<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Lokasi;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 2;

    protected function getStats(): array
    {
        $totalUsers    = User::count();
        $totalLokasi   = Lokasi::count();
        $avgPerUser    = $totalUsers > 0 ? round($totalLokasi / $totalUsers, 1) : 0;

        // Top user: yang punya lokasi terbanyak
        $topUserRow = DB::table('lokasis')
            ->select('user_id', DB::raw('count(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        $topUserName = '-';
        $topUserCount = 0;
        if ($topUserRow) {
            $topUser = User::find($topUserRow->user_id);
            $topUserName  = $topUser?->name ?? 'Unknown';
            $topUserCount = $topUserRow->total;
        }

        // Kategori terpopuler
        $topKategori = DB::table('lokasis')
            ->select('kategori', DB::raw('count(*) as total'))
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->value('kategori') ?? '-';

        return [
            Stat::make('Total Pengguna', $totalUsers)
                ->description('User terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([1, 2, 3, $totalUsers]),

            Stat::make('Total Lokasi Tersimpan', $totalLokasi)
                ->description('Di seluruh akun')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('success')
                ->chart([1, 2, 4, $totalLokasi]),

            Stat::make('Rata-rata Lokasi / User', $avgPerUser)
                ->description('Lokasi per pengguna')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Pengguna Paling Aktif', $topUserName)
                ->description("{$topUserCount} lokasi tersimpan")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('danger'),

            Stat::make('Kategori Terpopuler', $topKategori)
                ->description('Kategori dengan lokasi terbanyak')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
        ];
    }
}
