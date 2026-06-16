<x-filament-panels::page>

    <div style="display:flex; justify-content:center;">
        <div style="width:100%; max-width:440px;">

            <div id="struk-cetak" style="background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.12); overflow:hidden; border:1px solid #e5e7eb; font-family:'Nunito',sans-serif;">

                {{-- ── HEADER ── --}}
                <div style="background:linear-gradient(135deg,#059669,#0d9488); padding:24px 24px 20px; text-align:center; color:#fff;">
                    <div style="font-size:20px; font-weight:800; letter-spacing:.5px; margin-top:6px;">Seblak Nusantara</div>
                    <div style="font-size:12px; opacity:.8; margin-top:2px;">Slip Gaji Karyawan</div>
                    <div style="margin-top:12px; display:inline-block; background:rgba(255,255,255,.2); border-radius:999px; padding:4px 16px; font-size:13px; font-weight:700; letter-spacing:.5px;">
                        {{ $this->record->id_penggajian }}
                    </div>
                </div>

                {{-- ── INFO KARYAWAN ── --}}
                <div style="padding:16px 24px; background:#f9fafb; border-bottom:1px dashed #d1d5db; display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px;">Karyawan</div>
                        <div style="font-size:18px; font-weight:800; color:#111827; margin-top:2px;">{{ $this->record->karyawan->nama ?? '-' }}</div>
                        <div style="font-size:12px; color:#6b7280;">ID: {{ $this->record->id_karyawan }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px;">Periode</div>
                        <div style="font-size:16px; font-weight:700; color:#111827; margin-top:2px;">{{ $this->record->periode }}</div>
                        <div style="font-size:12px; color:#6b7280;">
                            {{ \Carbon\Carbon::parse($this->record->tanggal_penggajian)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>

                {{-- ── RINCIAN ── --}}
                <div style="padding:16px 24px;">
                    <div style="font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px;">Rincian Perhitungan</div>

                    @php
                        $rows = [
                            ['label' => 'Jam Kerja / Hari',  'value' => $this->record->jam_kerja . ' jam'],
                            ['label' => 'Upah / Jam',        'value' => 'Rp ' . number_format($this->record->upah_per_jam, 0, ',', '.')],
                            ['label' => 'Gaji / Hari',       'value' => 'Rp ' . number_format($this->record->gaji_per_hari, 0, ',', '.')],
                            ['label' => 'Kehadiran',         'value' => $this->record->kehadiran . ' hari'],
                        ];
                    @endphp

                    @foreach($rows as $row)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:14px; color:#6b7280;">{{ $row['label'] }}</span>
                        <span style="font-size:14px; font-weight:700; color:#111827;">{{ $row['value'] }}</span>
                    </div>
                    @endforeach

                    {{-- Formula --}}
                    <div style="margin-top:12px; padding:10px; background:#eff6ff; border-radius:8px; text-align:center; font-size:12px; color:#3b82f6;">
                        {{ $this->record->jam_kerja }} jam
                        &times; Rp {{ number_format($this->record->upah_per_jam, 0, ',', '.') }}
                        &times; {{ $this->record->kehadiran }} hari
                    </div>
                </div>

                {{-- ── TOTAL ── --}}
                <div style="margin:0 24px 16px; border-radius:12px; background:linear-gradient(135deg,#059669,#0d9488); padding:16px 20px; color:#fff;">
                    <div style="font-size:11px; opacity:.8; text-transform:uppercase; letter-spacing:.5px;">Total Gaji Diterima</div>
                    <div style="font-size:28px; font-weight:900; margin-top:4px;">
                        Rp {{ number_format($this->record->nominal, 0, ',', '.') }}
                    </div>
                </div>

                {{-- ── STATUS ── --}}
                <div style="padding:0 24px 20px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:#9ca3af;">Status Pembayaran</span>
                    @php
                        $bg   = match($this->record->status) { 'Dibayarkan' => '#dcfce7', 'Ditangguhkan' => '#fef9c3', default => '#f3f4f6' };
                        $fg   = match($this->record->status) { 'Dibayarkan' => '#15803d', 'Ditangguhkan' => '#a16207', default => '#6b7280' };
                        $icon = match($this->record->status) { 'Dibayarkan' => '✅', 'Ditangguhkan' => '⏳', default => '❓' };
                    @endphp
                    <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; background:{{ $bg }}; color:{{ $fg }}; font-size:13px; font-weight:700;">
                        {{ $icon }} {{ $this->record->status }}
                    </span>
                </div>

                {{-- ── FOOTER ── --}}
                <div style="background:#f9fafb; border-top:1px dashed #d1d5db; padding:12px 24px; text-align:center; font-size:11px; color:#9ca3af;">
                    Dicetak pada {{ now()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB<br>
                    Seblak Nusantara — Sistem Penggajian
                </div>

            </div>{{-- end struk --}}

        </div>
    </div>

</x-filament-panels::page>
