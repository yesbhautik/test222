@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Workbook'))
@section('titlebar_pretitle', __('Share post your integrations.'))
@section('titlebar_title', $title)
@section('titlebar_actions', '')

@section('content')
    <div class="py-10">
        <div class="mx-auto w-full lg:w-3/5">
            <div class="mt-4  pt-4 [&_.tox-edit-area__iframe]:!bg-transparent">
                @if ($workbook->generator->type === 'code')
                    <input
                            id="code_lang"
                            type="hidden"
                            value="{{ substr($workbook->input, strrpos($workbook->input, 'in') + 3) }}"
                    >
                    <div class="mt-4 min-h-full border-t pt-6">
            <pre
                    class="line-numbers min-h-full resize [direction:ltr]"
                    id="code-pre"
            ><code id="code-output">{{ $workbook->output }}</code></pre>
                    </div>
                @elseif($workbook->generator->type === 'image')
                    <figure>
                        <a href="{{ $workbook->output }}">
                            <img
                                    class="rounded-xl shadow-xl"
                                    src="{{ custom_theme_url($workbook->output) }}"
                                    alt="{{ __($workbook->generator->title) }}"
                            />
                        </a>
                    </figure>
                @elseif(in_array($workbook->generator->type, ['text', 'youtube', 'rss', 'audio']))
                    <form
                            class="workbook-form group/form flex flex-col gap-6"
                            method="POST"
                            action="{{ route('dashboard.user.integration.share.workbook',[$userIntegration->id, $workbook->id]) }}"
                    >
                        @csrf
                        <x-forms.input
                                name="title"
                                class="border-transparent font-serif text-2xl"
                                id="workbook_title"
                                placeholder="{{ __('Untitled Document...') }}"
                                value="{{ $workbook->title }}"
                        />
                        <x-forms.input
                                name="workbook_text"
                                class="tinymce font-body border-0"
                                id="content"
                                type="textarea"
                                rows="25"
                        >{!! $workbook->output !!}</x-forms.input>
                        <x-button
                                class="w-full"
                                tag="button"
                                type="submit"
                                variant="primary"
                                size="lg"
                                id="share"
                        >
                            <span class="group-[&.loading]/form:hidden">{{ __('Share') }}</span>
                            <span class="hidden group-[&.loading]/form:inline-block">{{ __('Please wait...') }}</span>
                        </x-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/tinymce-theme-handler.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/workbook.js') }}"></script>

    @if ($openai->type == 'code')
        <link
                rel="stylesheet"
                href="{{ custom_theme_url('/assets/libs/prism/prism.css') }}"
        />
        <script src="{{ custom_theme_url('/assets/libs/prism/prism.js') }}"></script>
        <script>
            window.Prism = window.Prism || {};
            window.Prism.manual = true;
            document.addEventListener('DOMContentLoaded', (event) => {
                "use strict";

                const codeLang = document.querySelector('#code_lang');
                const codePre = document.querySelector('#code-pre');
                const codeOutput = codePre?.querySelector('#code-output');

                if (!codeOutput) return;

                codePre.classList.add(`language-${codeLang && codeLang.value !== '' ? codeLang.value : 'javascript'}`);

                // saving for copy
                window.codeRaw = codeOutput.innerText;

                Prism.highlightElement(codeOutput);
            });
        </script>
    @endif
@endpush
