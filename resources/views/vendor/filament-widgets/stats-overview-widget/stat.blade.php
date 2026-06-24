@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Facades\FilamentView;

    $chartColor = $getChartColor() ?? 'gray';
    $descriptionColor = $getDescriptionColor() ?? 'gray';
    $descriptionIcon = $getDescriptionIcon();
    $descriptionIconPosition = $getDescriptionIconPosition();
    $url = $getUrl();
    $tag = $url ? 'a' : 'div';
    $dataChecksum = $generateDataChecksum();

    $descriptionIconClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-wi-stats-overview-stat-description-icon h-4 w-4',
        match ($descriptionColor) {
            'gray'    => 'text-gray-400',
            'success' => 'text-emerald-500',
            'danger'  => 'text-red-500',
            'warning' => 'text-amber-500',
            'info'    => 'text-blue-500',
            default   => 'text-orange-500',
        },
    ]);
@endphp

<{!! $tag !!}
    @if ($url)
        {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab()) }}
    @endif
    {{
        $getExtraAttributeBag()
            ->class([
                'fi-wi-stats-overview-stat-custom relative overflow-hidden rounded-2xl bg-white p-6',
            ])
            ->style(['border: 1px solid #EDE0DC; box-shadow: 0 1px 4px rgba(0,0,0,.04), 0 4px 16px -4px rgba(0,0,0,.06);'])
    }}
>
    {{-- Top accent bar --}}
    <div class="absolute inset-x-0 top-0 h-1 rounded-t-2xl"
         style="background: linear-gradient(90deg, #7F1D1D, #DC2626, #EA580C);"></div>

    <div class="relative z-10 grid gap-y-2 mt-1">
        {{-- Label --}}
        <span class="fi-wi-stats-overview-stat-label text-xs font-semibold uppercase tracking-widest"
              style="color: #78716C; letter-spacing: .1em;">
            {{ $getLabel() }}
        </span>

        {{-- Value --}}
        <div class="fi-wi-stats-overview-stat-value font-bold tracking-tight leading-none"
             style="color: #5C1010; font-size: 2rem; font-family: 'Playfair Display', Georgia, serif;">
            {{ $getValue() }}
        </div>

        {{-- Description --}}
        @if ($description = $getDescription())
            <div class="flex items-center gap-x-1 mt-1">
                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::Before, 'before']))
                    <x-filament::icon :icon="$descriptionIcon" :class="$descriptionIconClasses" />
                @endif
                <span class="text-xs font-medium" style="color: #A8A29E;">
                    {{ $description }}
                </span>
                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::After, 'after']))
                    <x-filament::icon :icon="$descriptionIcon" :class="$descriptionIconClasses" />
                @endif
            </div>
        @endif
    </div>

    {{-- Chart — tipis di bawah, semi-transparan --}}
    @if ($chart = $getChart())
        <div x-data="{ statsOverviewStatChart: function () {} }">
            <div
                @if (FilamentView::hasSpaMode())
                    x-load="visible"
                @else
                    x-load
                @endif
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('stats-overview/stat/chart', 'filament/widgets') }}"
                x-data="statsOverviewStatChart({
                            dataChecksum: @js($dataChecksum),
                            labels: @js(array_keys($chart)),
                            values: @js(array_values($chart)),
                        })"
                class="fi-wi-stats-overview-stat-chart absolute inset-x-0 bottom-0 overflow-hidden rounded-b-2xl"
                style="opacity: 0.25; pointer-events: none; z-index: 0;"
            >
                <canvas x-ref="canvas" class="h-8"></canvas>

                {{-- Force light background color for chart fill --}}
                <span x-ref="backgroundColorElement" class="text-orange-100"></span>
                <span x-ref="borderColorElement" class="text-orange-400"></span>
            </div>
        </div>
    @endif
</{!! $tag !!}>
