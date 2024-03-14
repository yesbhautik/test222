@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $item != null ? __('Edit Tool') : __('Add New Tool'))
@section('titlebar_actions', '')
@section('content')
    <div class="py-10">
        <form class="mx-auto flex w-full flex-col gap-5 lg:w-5/12" id="item_form"
            onsubmit="return toolsCreateOrUpdate({{ $item != null ? $item->id : null }});" enctype="multipart/form-data">
            <x-forms.input id="image" type="file" name="image" accept="image/*" label="{{ __('Image') }}"
                size="lg" required />

            <x-forms.input id="title" label="{{ __('Title') }}" name="title" size="lg" required
                value="{{ $item != null ? $item->title : null }}" />

            <x-forms.input id="description" name="description" type="textarea" label="{{ __('Description') }}"
                rows="10" required>{{ $item != null ? $item->description : null }}</x-forms.input>

            <x-button id="item_button" size="lg" type="submit">
                {{ __('Save') }}
            </x-button>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
@endpush
