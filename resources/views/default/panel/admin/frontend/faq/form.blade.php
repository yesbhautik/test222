@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', $faq != null ? __('Edit F.A.Q') : __('Add New F.A.Q'))
@section('titlebar_actions', '')
@section('content')
    <div class="py-10">
        <form class="mx-auto flex w-full flex-col gap-5 lg:w-5/12" id="faq_form"
            onsubmit="return faqCreateOrUpdate({{ $faq != null ? $faq->id : null }});">
            <x-forms.input id="question" label="{{ __('Question') }}" name="question" size="lg" required
                value="{{ $faq != null ? $faq->question : null }}" />

            <x-forms.input id="answer" label="{{ __('Answer') }}" name="answer" size="lg" type="textarea"
                rows="10" required>{{ $faq != null ? $faq->answer : null }}</x-forms.input>

            <x-button id="faq_button" size="lg" type="submit">
                {{ __('Save') }}
            </x-button>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
@endpush
