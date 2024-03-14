@php
    $base_class =
        'lqd-icon flex items-center justify-center shrink-0 relative shadow-xs rounded-full transition-all bg-primary text-primary-foreground [&_svg]:max-w-full [&_svg]:h-auto';

    $variations = [
        'size' => [
            'none' => 'lqd-icon-size-none',
            'md' => 'lqd-icon-md w-9 h-9',
            'lg' => 'lqd-icon-lg w-10 h-10',
            'xl' => 'lqd-icon-xl w-11 h-11',
        ],
    ];

    $size = isset($variations['size'][$size]) ? $variations['size'][$size] : $variations['size']['md'];
@endphp

<span {{ $attributes->withoutTwMergeClasses()->twMerge($base_class, $size, $attributes->get('class')) }}>
    {{ $slot }}

    @if ($activeBadge)
        <span @class([
            'absolute bottom-0 end-0 inline-block size-3 rounded-full border-2 border-background',
            'bg-green-500' => $activeBadgeCondition,
            'bg-red-500' => !$activeBadgeCondition,
        ])></span>
    @endif
</span>
