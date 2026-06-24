<?php

namespace App\Filament\Resources\BukuBesarResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\JurnalDetail;
use Carbon\Carbon;

class BukuBesar extends Widget
{
    protected static string $view = 'filament.resources.buku-besar-resource.widgets.buku-besar';

    protected int | string | array $columnSpan = 'full';

    public string $periode = '';

    public function mount(): void
    {
        $this->periode = now()->format('Y-m');
    }

    public function filter(): void
    {
        // Livewire re-render otomatis karena $periode sudah berubah via wire:model
    }

    public function getViewData(): array
    {
        $periode = $this->periode ?: now()->format('Y-m');
        $start   = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $end     = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tgl', [$start->toDateString(), $end->toDateString()]);
            })
            ->get()
            ->sortBy(fn ($d) => [$d->akun->kode_akun ?? '', $d->jurnal->tgl ?? '']);

        $groupedDetails = $details->groupBy(fn ($d) => $d->akun_id);

        return [
            'groupedDetails' => $groupedDetails,
            'periode'        => $periode,
            'isEmpty'        => $groupedDetails->isEmpty(),
        ];
    }
}
