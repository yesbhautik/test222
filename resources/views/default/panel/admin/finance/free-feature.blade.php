@extends('panel.layout.app')

@section('title', __('Trial Feature'))

@section('content')
    @php
        $selectedAiList = $selectedAiList ?: old('openaiItems', []);
    @endphp
    <div class="py-10">
        <form
            class="mx-auto flex w-full flex-col gap-5 lg:w-5/12"
            action="{{ route('dashboard.admin.finance.free.feature') }}"
            method="POST"
        >
            @csrf
            @foreach ($openAiList->groupBy('filters') as $key => $items)
                <x-forms.input
                    class:container="mb-4"
                    id="{{ $key }}"
                    data-filter="check"
                    type="checkbox"
                    label="{{ ucfirst($key) }}"
                    name="display_word"
                    switcher
                />
                <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3">
                    @foreach ($items as $keyItem => $item)
                        <x-forms.input
                            class:container="h-full"
                            class:label="w-full border h-full rounded px-3 py-4 hover:bg-foreground/5 transition-colors"
                            class="checked-item"
                            id="flex_check_{{ $item->id }}"
                            data-filter="{{ $key }}"
                            :checked="in_array($item->slug, $selectedAiList)"
                            type="checkbox"
                            name="openaiItems[]"
                            value="{{ $item->slug }}"
                            label="{{ $item->title }}"
                            custom
                        />
                    @endforeach
                </div>
            @endforeach
            <x-button
                size="lg"
                type="submit"
            >
                {{ __('Save') }}
            </x-button>
        </form>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            $('[data-filter="check"]').on('change', function() {

                if ($(this).is(':checked')) {
                    $('[data-filter="' + $(this).attr('id') + '"]').prop('checked', true);
                } else {
                    $('[data-filter="' + $(this).attr('id') + '"]').prop('checked', false);
                }
            });
        });
    </script>
@endpush
