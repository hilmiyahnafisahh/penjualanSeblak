<?php

namespace App\Filament\Resources\BukuBesarResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\JurnalDetail;

class BukuBesar extends Widget
{
    protected static string $view = 'filament.resources.buku-besar-resource.widgets.buku-besar';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $details = JurnalDetail::with(['akun', 'jurnal'])
            ->get()
            ->sortBy(fn ($detail) => [$detail->akun->kode_akun ?? '', $detail->jurnal->tgl ?? '']);

        $groupedDetails = $details->groupBy(fn ($detail) => $detail->akun_id);

        return [
            'groupedDetails' => $groupedDetails,
        ];
    }
}
