@extends('panel.layout.app', ['layout_wide' => true, 'disable_tblr' => true])
@section('title', __('AI Writer'))
@section('titlebar_subtitle', __('Text Generator & AI Copywriting Assistant'))

@section('titlebar_after')
    @php
        $filter_check = [];
        foreach ($list as $item) {
            if ($item->active != 1) {
                continue;
            }
            if ($item->filters) {
                foreach (explode(',', $item->filters) as $filter) {
                    $filter_check[] = $filter;
                }
            }
        }
        $filter_check = array_unique($filter_check);
    @endphp
    <ul class="lqd-filter-list mt-2 flex scroll-mt-6 flex-wrap items-center gap-x-4 gap-y-2 text-heading-foreground max-sm:gap-3"
        id="lqd-generators-filter-list">
        <li>
            <x-button
                class="lqd-filter-btn inline-flex rounded-full px-2.5 py-0.5 text-2xs leading-tight transition-colors hover:translate-y-0 hover:bg-foreground/5 [&.active]:bg-foreground/5"
                tag="button" type="button" name="filter" variant="ghost" x-data ::class="$store.generatorsFilter.filter === 'all' && 'active'"
                @click="$store.generatorsFilter.changeFilter('all')">
                {{ __('All') }}
            </x-button>
        </li>
        <li>
            <x-button
                class="lqd-filter-btn inline-flex rounded-full px-2.5 py-0.5 text-2xs leading-tight transition-colors hover:translate-y-0 hover:bg-foreground/5 [&.active]:bg-foreground/5"
                tag="button" type="button" name="filter" variant="ghost" x-data ::class="$store.generatorsFilter.filter === 'favorite' && 'active'"
                @click="$store.generatorsFilter.changeFilter('favorite')">
                {{ __('Favorite') }}
            </x-button>
        </li>

        @foreach ($filters as $filter)
            @if (in_array($filter->name, $filter_check))
                <li>
                    <x-button
                        class="lqd-filter-btn inline-flex rounded-full px-2.5 py-0.5 text-2xs leading-tight transition-colors hover:translate-y-0 hover:bg-foreground/5 [&.active]:bg-foreground/5"
                        tag="button" type="button" name="filter" variant="ghost" x-data ::class="$store.generatorsFilter.filter === '{{ $filter->name }}' && 'active'"
                        @click="$store.generatorsFilter.changeFilter('{{ $filter->name }}')">
                        {{ __(str()->ucfirst($filter->name)) }}
                    </x-button>
                </li>
            @endif
        @endforeach
    </ul>
@endsection

@section('content')
    <div class="lqd-generators-container" id="lqd-generators-container">
        <div class="lqd-generators-list grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" id="lqd-generators-list">
            @foreach ($list as $item)
                @if ($item->active != 1 || str()->startsWith($item->slug, 'ai_'))
                    @continue
                @endif
                <x-generator-item :$item />
            @endforeach
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/openai_list.js') }}"></script>
@endpush
