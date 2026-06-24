@php $items = $getState() ?? []; @endphp
@if(empty($items))
    <p class="text-sm text-gray-400">Tidak ada data topping.</p>
@else
    <ol class="list-decimal list-inside space-y-2 text-sm">
        @foreach($items as $item)
            <li>
                <span class="font-semibold">{{ $item['nama'] }}</span>
                <span class="text-gray-500 ml-2">{{ $item['jumlah'] }} item • Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
            </li>
        @endforeach
    </ol>
@endif
