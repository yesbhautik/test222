@extends('panel.layout.app')
@section('title', __('Who is script for section'))
@section('titlebar_actions', '')
@section('content')
    <div class="py-10">
        <form class="mx-auto flex w-full flex-col gap-5 lg:w-5/12" id="item_form"
            onsubmit="return whoisCreateOrUpdate({{ $item != null ? $item->id : null }});">

            <x-forms.input id="title" name="title" size="lg" label="{{ __('Title') }}"
                value="{{ $item != null ? $item->title : null }}" required />

            <x-forms.input id="color" name="color" size="lg" label="{{ __('Color Name') }}"
                value="{{ $item != null ? $item->color : null }}" required />

            <x-button id="item_button" type="submit" size="lg">
                {{ __('Save') }}
            </x-button>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
@endpush
