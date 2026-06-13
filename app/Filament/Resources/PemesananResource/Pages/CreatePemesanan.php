<?php

namespace App\Filament\Resources\PemesananResource\Pages;

use App\Filament\Resources\PemesananResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Menu;
use App\Models\Pembayaran;

class CreatePemesanan extends CreateRecord
{
    protected static string $resource = PemesananResource::class;

    protected bool $redirectToPayment = false;

    // hitung subtotal
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        foreach ($data['detail_pesanan'] ?? [] as $item) {
            $total += $item['subtotal'] ?? 0;

            // Pastikan harga dan subtotal topping terisi dari database
            if (!empty($item['topping']) && is_array($item['topping'])) {
                foreach ($item['topping'] as $toppingKey => $topping) {
                    $barang = Barang::where('id_barang', $topping['id_barang'] ?? null)->first();
                    $hargaTopping = (float) ($barang?->harga_barang ?? $topping['harga'] ?? 0);
                    $qtyTopping = (int) ($topping['qty'] ?? 1);
                    $subtotalTopping = $hargaTopping * $qtyTopping;

                    $data['DetailPesanan'][$detailKey]['topping'][$toppingKey]['harga'] = $hargaTopping;
                    $data['DetailPesanan'][$detailKey]['topping'][$toppingKey]['subtotal'] = $subtotalTopping;

                    $total += $subtotalTopping;
                }
            }
        }

        $data['subtotal'] = $total;

        return $data;
    }
protected function afterCreate(): void
    {
        $detailItems = $this->data['DetailPesanan'] ?? [];
        $this->decreaseStockFromPesanan($detailItems);

        // Jika user memilih tombol "Bayar Sekarang", buat record pembayaran
        // otomatis dan arahkan ke Midtrans untuk proses pembayaran.
        if ($this->redirectToPayment && $this->record) {
            $pemesananId = $this->record->id;

            // Buat pembayaran default (metode QRIS, status pending)
            $pembayaran = Pembayaran::create([
                'id_pemesanan' => $pemesananId,
                'total_pembayaran' => $this->record->subtotal ?? 0,
                'metode_pembayaran' => 'qris',
                'tanggal_pembayaran' => now('Asia/Jakarta'),
                'status_pembayaran' => 'pending',
            ]);

            // Redirect ke daftar admin Pembayaran agar admin dapat melihat
            // transaksi yang baru dibuat.
            $this->redirectUrl = url('/admin/pembayaran');
        }
    }

    protected function decreaseStockFromPesanan(array $detailItems): void
    {
        DB::transaction(function () use ($detailItems) {
            foreach ($detailItems as $detail) {
                $toppingItems = $detail['topping'] ?? [];

                foreach ($toppingItems as $toppingItem) {
                    $barang = Barang::find($toppingItem['id_barang'] ?? null);
                    $qtyTopping = (int) ($toppingItem['qty'] ?? 0);
                    if ($barang && $qtyTopping > 0) {
                        $barang->decrement('stok', $qtyTopping);
                    }
                }
            }
        });
    }
    // redirect setelah create
    protected function getRedirectUrl(): string
    {
        // Jika sebuah redirect khusus sudah ditentukan di afterCreate(), pakai itu.
        if (!empty($this->redirectUrl)) {
            return $this->redirectUrl;
        }

        if ($this->redirectToPayment && $this->record) {
            return route('pembayaran.show', ['id' => $this->record->id]);
        }

        return $this->getResource()::getUrl('index');
    }

    // tombol tambahan
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Pesanan'),

            Action::make('bayarSekarang')
                ->label('Bayar Sekarang')
                ->color('success')
                ->action('bayarSekarang'),

            $this->getCancelFormAction(),
        ];
    }

    public function bayarSekarang(): void
    {
        $this->redirectToPayment = true;

        $this->create();
    }
}