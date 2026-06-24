@props([
    'disabled' => false,
    'prefix' => null,
    'required' => false,
    'suffix' => null,
])

<label
    {{ $attributes->class(['fi-fo-field-wrp-label inline-flex items-center gap-x-3']) }}
>
    {{ $prefix }}

    <span class="text-sm font-medium leading-6 text-gray-800">
        {{-- Deliberately poor formatting to ensure that the asterisk sticks to the final word in the label. --}}
        {{ $slot }}@if ($required && (! $disabled))<sup class="text-danger-600 font-medium">*</sup>
        @endif
    </span>

    {{ $suffix }}
</label>
