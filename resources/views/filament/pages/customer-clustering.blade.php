<x-filament-panels::page>
    <x-filament::section>

        @php
            $hasData = !empty($chart1['datasets']) &&
                       collect($chart1['datasets'])->flatMap(fn($d) => $d['data'])->isNotEmpty();
        @endphp

        @if(!$hasData)
            {{-- ── EMPTY STATE ── --}}
            <div style="text-align:center; padding:60px 20px; color:#6b7280;">
                <div style="font-size:3.5rem; margin-bottom:12px;">📊</div>
                <div style="font-size:1.1rem; font-weight:700; color:#374151; margin-bottom:6px;">
                    Data Belum Cukup untuk Clustering
                </div>
                <div style="font-size:.875rem; max-width:380px; margin:0 auto; line-height:1.6;">
                    K-Means membutuhkan minimal <strong>3 pelanggan</strong> yang sudah memiliki riwayat pemesanan.
                    Saat ini belum cukup data transaksi untuk menjalankan analisis clustering.
                </div>
                <div style="margin-top:20px; padding:12px 20px; background:#fef9c3; border:1px solid #fde047; border-radius:8px; display:inline-block; font-size:.82rem; color:#854d0e;">
                    💡 Tambahkan pemesanan dari beberapa pelanggan berbeda untuk melihat hasil clustering.
                </div>
            </div>
        @else
            {{-- ── CHARTS ── --}}
            <div style="margin-bottom:12px; padding:12px 16px; background:#eff6ff; border-radius:8px; font-size:.85rem; color:#1e40af;">
                <strong>K-Means Clustering</strong> — Pelanggan dikelompokkan berdasarkan pola belanja (total tagihan, jumlah item, estimasi laba).
            </div>

            <div class="space-y-6">
                <div style="background:#fff; padding:16px; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.08);">
                    <div style="font-size:.8rem; font-weight:600; color:#6b7280; margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                        Chart 1 — Total Tagihan vs Total Qty
                    </div>
                    <canvas id="chart1" style="max-height:300px;"></canvas>
                </div>

                <div style="background:#fff; padding:16px; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.08);">
                    <div style="font-size:.8rem; font-weight:600; color:#6b7280; margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                        Chart 2 — Total Tagihan vs Estimasi Laba
                    </div>
                    <canvas id="chart2" style="max-height:300px;"></canvas>
                </div>

                <div style="background:#fff; padding:16px; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.08);">
                    <div style="font-size:.8rem; font-weight:600; color:#6b7280; margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                        Chart 3 — Total Qty vs Estimasi Laba
                    </div>
                    <canvas id="chart3" style="max-height:300px;"></canvas>
                </div>
            </div>
        @endif

    </x-filament::section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @if($hasData)
    <script>
    function createChart(id, data, labelX, labelY) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'scatter',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const x = new Intl.NumberFormat('id-ID').format(ctx.raw.x);
                                const y = new Intl.NumberFormat('id-ID').format(ctx.raw.y);
                                return `${ctx.raw.label ?? ''} (${labelX}: ${x}, ${labelY}: ${y})`;
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: labelX } },
                    y: { title: { display: true, text: labelY } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        createChart('chart1', @json($chart1), 'Total Tagihan', 'Total Qty');
        createChart('chart2', @json($chart2), 'Total Tagihan', 'Estimasi Laba');
        createChart('chart3', @json($chart3), 'Total Qty', 'Estimasi Laba');
    });
    </script>
    @endif

</x-filament-panels::page>
