<x-filament-panels::page>
    <x-filament::section>

        <div class="space-y-6">

            <div class="bg-white p-4 rounded-xl shadow">
                <canvas id="chart1" style="height:100px"></canvas>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <canvas id="chart2" style="height:100px"></canvas>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <canvas id="chart3" style="height:100px"></canvas>
            </div>

        </div>

    </x-filament::section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    function createChart(id, data, labelX, labelY) {
        new Chart(document.getElementById(id), {
            type: 'scatter',
            data: data,
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const x = new Intl.NumberFormat('id-ID').format(ctx.raw.x);
                                const y = new Intl.NumberFormat('id-ID').format(ctx.raw.y);

                                return `${ctx.raw.label} (X: ${x}, Y: ${y})`;
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: labelX }},
                    y: { title: { display: true, text: labelY }}
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {

        createChart('chart1', @json($chart1), 'Total Tagihan', 'Total Qty');
        createChart('chart2', @json($chart2), 'Total Tagihan', 'Total Laba');
        createChart('chart3', @json($chart3), 'Total Qty', 'Total Laba');

    });
    </script>

</x-filament-panels::page>