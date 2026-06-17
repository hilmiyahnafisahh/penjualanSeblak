<?php

namespace App\Livewire;

use App\Services\CashflowAi;
use Livewire\Component;

class ChatAsisten extends Component
{
    protected $listeners = [
        'openChatAssistant' => 'open',
    ];

    public bool $terbuka = false;

    /** @var array<int, array{role: string, text: string}> */
    public array $messages = [];

    public string $pertanyaan = '';

    public function toggle(): void
    {
        $this->terbuka = ! $this->terbuka;
    }

    public function open(): void
    {
        $this->terbuka = true;
    }

    public function kirim(): void
    {
        $tanya = trim($this->pertanyaan);

        if ($tanya === '') {
            return;
        }

        $this->messages[] = ['role' => 'user', 'text' => $tanya];
        $this->pertanyaan = '';

        $riwayat = $this->messages;
        array_pop($riwayat);

        $jawaban = app(CashflowAi::class)->chat($tanya, $riwayat);

        $this->messages[] = ['role' => 'model', 'text' => $jawaban];
    }

    public function bersihkan(): void
    {
        $this->messages = [];
    }

    public function render()
    {
        return view('livewire.chat-asisten');
    }
}