@php
    $base_class = 'lqd-alert [&_:first-child]:mt-0 [&_:last-child]:mb-0';
    $icon_base_class = 'lqd-alert-icon size-5 shrink-0';

    $variations = [
        'variant' => [
            'info' => 'lqd-alert-info border-s border-s-4 border-blue-500 bg-blue-500/5 shadow text-blue-600',
            'warn' => 'lqd-alert-warn border-s border-s-4 border-orange-500 bg-orange-500/5 shadow text-orange-600',
            'danger' => 'lqd-alert-danger border-s border-s-4 border-red-500 bg-red-500/5 shadow text-red-600',
        ],
        'size' => [
            'none' => 'lqd-alert-size-none',
            'sm' => 'lqd-alert-sm p-2 rounded-md',
            'md' => 'lqd-alert-md p-4 rounded-lg',
            'lg' => 'lqd-alert-lg p-6 rounded-xl',
        ],
    ];

    $variant = isset($variations['variant'][$variant]) ? $variations['variant'][$variant] : $variations['variant']['info'];
    $size = isset($variations['size'][$size]) ? $variations['size'][$size] : $variations['size']['md'];
@endphp

<div
    {{ $attributes->withoutTwMergeClasses()->twMerge($base_class, $variant, $size) }}
    {{ $attributes }}
>
    <div class="flex gap-2">
        @if (filled($icon))
            <x-dynamic-component
                :component="$icon"
                {{ $attributes->withoutTwMergeClasses()->twMergeFor('icon', $icon_base_class) }}
            />
        @endif
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
