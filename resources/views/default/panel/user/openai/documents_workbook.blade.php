@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Workbook'))
@section('titlebar_pretitle', __('Edit your generations.'))
@section('titlebar_title', $workbook->title)
@section('titlebar_actions_after')


    @if(!empty($integrations) && $checkIntegration)
        <div class="lqd-dropdown ml-3 flex relative group/dropdown [--dropdown-offset:0px]"
             style="--dropdown-offset: 20px;" x-data="dropdown({ triggerType: 'hover' })" x-bind="parent"
             x-ref="parent">
            <x-button variant="success" data-bs-toggle="dropdown">Publish</x-button>
            <div class="lqd-dropdown-dropdown absolute top-full opacity-0 invisible z-50 translate-y-1 pointer-events-none transition-all mt-[--dropdown-offset] before:absolute before:bottom-full before:-top-[--dropdown-offset] before:inset-x-0 group-[&amp;.lqd-is-active]/dropdown:opacity-100 group-[&amp;.lqd-is-active]/dropdown:visible group-[&amp;.lqd-is-active]/dropdown:translate-y-0 group-[&amp;.lqd-is-active]/dropdown:pointer-events-auto end-0"
                 x-bind="dropdown">
                <div class="lqd-dropdown-dropdown-content border rounded-lg bg-background shadow-lg shadow-black/5 dark:bg-surface min-w-52">
                    <div class="px-3 pt-3 text-foreground/70">
                        <p class="text-3xs">@lang('Integrations')</p>
                    </div>
                    <hr>
                    <div class="pb-2 text-2xs">
                        @foreach($integrations as $integration)
                            <a
                                    class="flex w-full items-center px-3 py-2 hover:bg-foreground/5"
                                    href="{{ route('dashboard.user.integration.share.workbook',[$integration->id, $workbook->id ]) }}"
                            >
                                {{ $integration?->integration?->app }}
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div class="py-10">
        <div class="mx-auto w-full lg:w-3/5">
            @include('panel.user.openai.documents_workbook_textarea')
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
