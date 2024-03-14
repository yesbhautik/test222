@php
    $current_url = url()->current();

    $base_class = 'lqd-titlebar pt-6 pb-7 border-b transition-colors';
    $container_base_class = 'lqd-titlebar-container container flex flex-wrap items-center justify-between gap-y-4';
    $before_base_class = 'lqd-titlebar-before w-full';
    $after_base_class = 'lqd-titlebar-after w-full';
    $pretitle_base_class = 'lqd-titlebar-pretitle text-xs text-foreground/70 m-0';
    $title_base_class = 'lqd-titlebar-title m-0';
    $subtitle_base_class = 'lqd-titlebar-subtitle mt-1 text-2xs opacity-80';
    $actions_base_class = 'lqd-titlebar-actions flex flex-wrap items-center gap-2';

    $generator_link = route('dashboard.user.openai.list') === $current_url ? '#lqd-generators-filter-list' : LaravelLocalization::localizeUrl(route('dashboard.user.openai.list'));
@endphp
<div
    id="lqd-titlebar"
    {{ $attributes->withoutTwMergeClasses()->twMerge($base_class, $attributes->get('class')) }}
>
    <div {{ $attributes->twMergeFor('container', $container_base_class) }}>
        @if (view()->hasSection('titlebar_before') || !empty($before))
            <div {{ $attributes->twMergeFor('before', $before_base_class) }}>
                @if (view()->hasSection('titlebar_before'))
                    @yield('titlebar_before')
                @elseif (!empty($before))
                    {{ $before }}
                @endif
            </div>
        @endif

        <div class="lqd-titlebar-col lqd-titlebar-col-nav flex w-full flex-col gap-2 lg:w-1/2">
            <p {{ $attributes->twMergeFor('pretitle', $pretitle_base_class) }}>
                @if (view()->hasSection('titlebar_pretitle'))
                    @yield('titlebar_pretitle')
                @elseif (view()->hasSection('pretitle'))
                    @yield('pretitle')
                @else
                    @if (route('dashboard.user.index') === $current_url || route('dashboard.admin.index') === $current_url)
                        {{ __('Dashboard') }}
                    @else
                        <x-button
                            class="text-inherit hover:text-foreground"
                            variant="link"
                            href="{{ LaravelLocalization::localizeUrl(route('dashboard.index')) }}"
                        >
                            <x-tabler-chevron-left
                                class="size-4"
                                stroke-width="1.5"
                            />
                            {{ __('Back to dashboard') }}
                        </x-button>
                    @endif
                @endif
            </p>
            <h1 {{ $attributes->twMergeFor('title', $title_base_class) }}>
                @if (view()->hasSection('titlebar_title'))
                    @yield('titlebar_title')
                @elseif (view()->hasSection('title'))
                    @yield('title')
                @else
                    {{ __('Welcome') }}, {{ auth()->user()->name }}.
                @endif
            </h1>
            @hasSection('titlebar_subtitle')
                <p {{ $attributes->twMergeFor('subtitle', $subtitle_base_class) }}>
                    @yield('titlebar_subtitle')
                </p>
            @endif
        </div>

        <div class="lqd-titlebar-col lqd-titlebar-col-actions flex w-full flex-wrap lg:w-1/2 lg:justify-end">
            @hasSection('titlebar_actions_before')
                @yield('titlebar_actions_before')
            @endif

            @if (view()->hasSection('titlebar_actions'))
                @yield('titlebar_actions')
            @elseif (!empty($actions))
                <div {{ $attributes->twMergeFor('actions', $actions_base_class, $actions->attributes->get('class')) }}>
                    {{ $actions }}
                </div>
            @else
                <div {{ $attributes->twMergeFor('actions', $actions_base_class) }}>
                    @if (request()->routeIs('dashboard.user.openai.documents.all') && !isset($currfolder))
                        <x-modal
                            title="{{ __('New Folder') }}"
                            disable-modal="{{ $app_is_demo }}"
                            disable-modal-message="{{ __('This feature is disabled in Demo version.') }}"
                        >
                            <x-slot:trigger
                                variant="ghost-shadow"
                            >
                                <x-tabler-plus class="size-4" />
                                {{ __('New Folder') }}
                            </x-slot:trigger>
                            <x-slot:modal>
                                @includeIf('panel.user.openai.components.modals.create-new-folder')
                            </x-slot:modal>
                        </x-modal>
                    @else
                        <x-button
                            variant="ghost-shadow"
                            href="{{ LaravelLocalization::localizeUrl(route('dashboard.user.openai.documents.all')) }}"
                        >
                            {{ __('My Documents') }}
                        </x-button>
                    @endif
                    <x-button href="{{ $generator_link }}">
                        <x-tabler-plus class="size-4" />
                        {{ __('New') }}
                    </x-button>
                </div>
            @endif

            @hasSection('titlebar_actions_after')
                @yield('titlebar_actions_after')
            @endif
        </div>

        @if (view()->hasSection('titlebar_after') || !empty($after))
            <div {{ $attributes->twMergeFor('after', $after_base_class) }}>
                @if (view()->hasSection('titlebar_after'))
                    @yield('titlebar_after')
                @elseif (!empty($after))
                    {{ $after }}
                @endif
            </div>
        @endif
    </div>
</div>
