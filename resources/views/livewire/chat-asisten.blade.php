<div>
    <button
        type="button"
        wire:click="toggle"
        style="position:fixed; bottom:20px; right:20px; z-index:9999; width:56px; height:56px; border-radius:9999px; background:#DC2626; color:#fff; box-shadow:0 8px 24px rgba(0,0,0,.25); border:none; cursor:pointer; font-size:22px;"
        title="Asisten AI"
    >💬</button>

    @if ($terbuka)
        <div style="position:fixed; bottom:88px; right:20px; z-index:9999; width:340px; max-width:90vw; background:#fff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 12px 32px rgba(0,0,0,.25); overflow:hidden; display:flex; flex-direction:column;">
            <div style="display:flex; align-items:center; justify-content:space-between; background:#DC2626; color:#fff; padding:12px 16px;">
                <span style="font-weight:600;">Asisten Keuangan AI</span>
                <button type="button" wire:click="toggle" style="background:none; border:none; color:#fff; cursor:pointer; font-size:18px;">&times;</button>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px; max-height:320px; overflow-y:auto; padding:12px;">
                @forelse ($messages as $msg)
                    <div style="display:flex; justify-content: <?php echo $msg['role'] === 'user' ? 'flex-end' : 'flex-start'; ?>;">
                        <div style="max-width:85%; white-space:pre-line; border-radius:14px; padding:8px 12px; font-size:14px; <?php echo $msg['role'] === 'user' ? 'background:#DC2626; color:#fff;' : 'background:#f3f4f6; color:#1f2937;'; ?>">
                            <?php echo e($msg['text']); ?>
                        </div>
                    </div>
                @empty
                    <p style="text-align:center; color:#9ca3af; font-size:14px; padding:16px 0;">Tanya apa aja soal toko & keuanganmu 👋</p>
                @endforelse

                <div wire:loading wire:target="kirim" style="font-size:12px; color:#9ca3af;">Asisten sedang mengetik…</div>
            </div>

            <form wire:submit="kirim" style="display:flex; gap:8px; border-top:1px solid #e5e7eb; padding:8px;">
                <input
                    type="text"
                    wire:model="pertanyaan"
                    placeholder="Tulis pertanyaan…"
                    autocomplete="off"
                    style="flex:1; border:1px solid #d1d5db; border-radius:8px; padding:8px; font-size:14px;"
                />
                <button type="submit" wire:loading.attr="disabled" wire:target="kirim" style="background:#DC2626; color:#fff; border:none; border-radius:8px; padding:8px 14px; font-size:14px; cursor:pointer;">Kirim</button>
            </form>
        </div>
    @endif
</div>