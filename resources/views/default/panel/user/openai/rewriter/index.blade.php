@php
    $voice_tones = ['Professional', 'Funny', 'Casual', 'Excited', 'Witty', 'Sarcastic', 'Feminine', 'Masculine', 'Bold', 'Dramatic', 'Grumpy', 'Secretive'];
@endphp

@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('AI ReWriter'))
@section('titlebar_subtitle', __('Effortlessly reshape and elevate your pre-existing content with a single click.'))

@section('content')
    <div class="py-10">
        <div class="lqd-generator-wrap grid grid-flow-row lg:grid-flow-col lg:[grid-template-columns:41%_1fr]">
            <div class="flex w-full flex-col gap-6 lg:pe-14">
                <x-card class="lqd-generator-remaining-credits">
                    <h5 class="mb-3 text-xs font-normal">
                        {{ __('Remaining Credits') }}
                    </h5>

                    <x-remaining-credit
                        class="flex-col-reverse text-xs"
                        style="inline"
                    />
                </x-card>

                <x-card
                    variant="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.variant', 'solid') }}"
                    size="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.size', 'md') }}"
                    roundness="{{ Theme::getSetting('defaultVariations.card.roundness', 'default') === 'default' ? 'none' : Theme::getSetting('defaultVariations.card.roundness', 'default') }}"
                >
                    <form
                        class="flex flex-col gap-y-5"
                        id="rewrite_content_form"
                        onsubmit="return sendOpenaiGeneratorForm();"
                        enctype="multipart/form-data"
                    >
                        <x-forms.input
                            id="content_rewrite"
                            size="lg"
                            type="textarea"
                            label="{{ __('Description') }}"
                            name="content_rewrite"
                            rows="10"
                            required
                        />

                        <x-forms.input
                            id="rewrite_mode"
                            size="lg"
                            type="select"
                            label="{{ __('Mode') }}"
                            name="rewrite_mode"
                            required
                        >
                            @foreach ($voice_tones as $tone)
                                <option
                                    value="{{ $tone }}"
                                    @selected($setting->openai_default_tone_of_voice == $tone)
                                >
                                    {{ __($tone) }}
                                </option>
                            @endforeach
                        </x-forms.input>

                        <x-forms.input
                            id="language"
                            size="lg"
                            type="select"
                            label="{{ __('Output Language') }}"
                            name="language"
                            required
                        >
                            @include('panel.user.openai.components.countries')
                        </x-forms.input>

                        <x-button
                            class="mt-2 w-full"
                            id="openai_generator_button"
                            tag="button"
                            size="lg"
                            type="submit"
                        >
                            {{ __('Generate') }}
                        </x-button>
                    </form>
                </x-card>
            </div>

            <x-card
                id="workbook_textarea"
                @class([
                    'w-full [&_.tox-edit-area__iframe]:!bg-transparent',
                    'lg:border-s lg:ps-16' =>
                        Theme::getSetting('defaultVariations.card.variant', 'outline') ===
                        'outline',
                ])
                variant="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.variant', 'solid') }}"
                size="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.size', 'md') }}"
                roundness="{{ Theme::getSetting('defaultVariations.card.roundness', 'default') === 'default' ? 'none' : Theme::getSetting('defaultVariations.card.roundness', 'default') }}"
            >
                <div class="flex flex-wrap items-center justify-between text-[13px]">
                    <button
                        class="flex items-center gap-2 border-none shadow-none"
                        id="btn_regenerate"
                        type="submit"
                        form="rewrite_content_form"
                    >
                        <x-tabler-arrows-right-left class="size-4" />
                        {{ __('Regenerate') }}
                    </button>
                    <div class="flex grow items-center justify-end">
                        <div class="flex rtl:flex-row-reverse">
                            <button
                                class="size-8 inline-flex items-center justify-center rounded-sm text-2xs transition-colors hover:bg-foreground/5"
                                id="workbook_undo"
                                title="{{ __('Undo') }}"
                            >
                                <x-tabler-arrow-back-up class="size-5" />
                                <span class="sr-only">{{ __('Undo') }}</span>
                            </button>
                            <button
                                class="size-8 inline-flex items-center justify-center rounded-sm text-2xs transition-colors hover:bg-foreground/5"
                                id="workbook_redo"
                                title="{{ __('Redo') }}"
                            >
                                <x-tabler-arrow-forward-up class="size-5" />
                                <span class="sr-only">{{ __('Redo') }}</span>
                            </button>
                        </div>
                        <button
                            class="size-8 inline-flex items-center justify-center rounded-sm text-2xs transition-colors hover:bg-foreground/5"
                            id="workbook_copy"
                            title="{{ __('Copy to clipboard') }}"
                        >
                            <x-tabler-copy class="size-5" />
                            <span class="sr-only">{{ __('Copy to clipboard') }}</span>
                        </button>
                        <x-dropdown.dropdown
                            offsetY="1rem"
                            anchor="end"
                        >
                            <x-slot:trigger
                                class="px-2 py-1"
                                variant="link"
                                size="xs"
                                title="{{ __('Download') }}"
                            >
                                <x-tabler-download class="size-5" />
                            </x-slot:trigger>
                            <x-slot:dropdown
                                class="overflow-hidden"
                            >
                                <button
                                    class="workbook_download flex w-full items-center gap-1 rounded-md p-2 font-medium hover:bg-foreground/5"
                                    data-doc-type="doc"
                                    data-doc-name="{{ __('AI ReWriter') }}"
                                >
                                    <x-tabler-brand-office
                                        class="size-6"
                                        stroke-width="1.5"
                                    />
                                    MS Word
                                </button>
                                <button
                                    class="workbook_download flex w-full items-center gap-1 rounded-md p-2 text-2xs font-medium hover:bg-foreground/5"
                                    data-doc-type="html"
                                    data-doc-name="{{ __('AI ReWriter') }}"
                                >
                                    <x-tabler-brand-html5
                                        class="size-6"
                                        stroke-width="1.5"
                                    />
                                    HTML
                                </button>
                            </x-slot:dropdown>
                        </x-dropdown.dropdown>
                        <a
                            class="size-8 -mr-1 inline-flex items-center justify-center rounded-sm text-2xs transition-colors hover:bg-foreground/5"
                            id="workbook_delete"
                            href="javascript:void(0);"
                            title="{{ __('Delete') }}"
                        >
                            <x-tabler-circle-minus class="size-5 stroke-red-600" />
                            <span class="sr-only">{{ __('Delete') }}</span>
                        </a>
                        <div
                            class="hidden items-end text-end"
                            id="savedDiv"
                        >
                            <a
                                class="text-nowrap flex items-center gap-1.5 rounded-md bg-green-600/10 px-2.5 py-1 text-[12px] font-medium leading-none text-green-700 transition-all hover:scale-105 hover:shadow-lg hover:shadow-green-600/5 dark:text-green-400"
                                href="{{ route('dashboard.user.openai.documents.all') }}"
                            >
                                <x-tabler-folder-check class="size-5" />
                                {{ __('Saved to') }}
                                <span class="underline">{{ __('Drafts') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="mt-4 min-h-full w-full border-t pt-6">
                        <form class="workbook-form flex flex-col gap-4 [&_.tox-editor-header]:!shadow-none">
                            <x-forms.input
                                class="border-transparent px-0 font-serif text-2xl"
                                id="workbook_title"
                                placeholder="{{ __('Untitled Document...') }}"
                            />
                            <x-forms.input
                                class="tinymce border-0 font-body"
                                id="default"
                                type="textarea"
                                rows="25"
                            />
                        </form>
                    </div>
                </div>
            </x-card>
        </div>
        <input
            id="guest_id"
            type="hidden"
            value="{{ $apiUrl }}"
        >
        <input
            id="guest_event_id"
            type="hidden"
            value="{{ $apikeyPart1 }}"
        >
        <input
            id="guest_look_id"
            type="hidden"
            value="{{ $apikeyPart2 }}"
        >
        <input
            id="guest_product_id"
            type="hidden"
            value="{{ $apikeyPart3 }}"
        >
    </div>
@endsection

@push('script')
    <script
        src="{{ custom_theme_url('/assets/libs/tinymce/tinymce.min.js') }}"
        defer
    ></script>
    <script src="{{ custom_theme_url('/assets/js/panel/tinymce-theme-handler.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/openai_generator_workbook.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/wavesurfer/wavesurfer.js') }}"></script>
    <script>
        const stream_type = '{!! $settings_two->openai_default_stream_server !!}';
        const openai_model = '{{ $setting->openai_default_model }}';

        function sendOpenaiGeneratorForm(ev) {
            "use strict";
            $('#savedDiv').addClass('hidden');

            tinyMCE?.activeEditor?.setContent('');

            ev?.preventDefault();
            ev?.stopPropagation();
            const submitBtn = document.getElementById("openai_generator_button");
            const editArea = document.querySelector('.tox-edit-area');
            const typingTemplate = document.querySelector('#typing-template').content.cloneNode(true);
            const typingEl = typingTemplate.firstElementChild;
            Alpine.store('appLoadingIndicator').show();
            submitBtn.classList.add('lqd-form-submitting');
            submitBtn.disabled = true;

            if (editArea) {
                if (!editArea.querySelector('.lqd-typing')) {
                    editArea.appendChild(typingEl);
                } else {
                    editArea.querySelector('.lqd-typing')?.classList?.remove('lqd-is-hidden');
                }
            }

            var formData = new FormData();
            formData.append('post_type', 'ai_rewriter');
            formData.append('content_rewrite', $('#content_rewrite').val());

            formData.append('rewrite_mode', $("#rewrite_mode").val());
            formData.append('language', $("#language").val());

            $.ajax({
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                url: "/dashboard/user/openai/generate",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    const typingEl = document.querySelector('.tox-edit-area > .lqd-typing');

                    const message_no = data.message_id;
                    const creativity = data.creativity;
                    const maximum_length = parseInt(data.maximum_length);
                    const number_of_results = data.number_of_results;
                    const prompt = data.inputPrompt;
                    return generate(message_no, creativity, maximum_length, number_of_results, prompt);
                    setTimeout(function() {
                        $('#savedDiv').removeClass('hidden');
                    }, 1000);
                },
                error: function(data) {
                    if (data.responseJSON.errors) {
                        toastr.error(data.responseJSON.errors);
                    } else if (data.responseJSON.message) {
                        toastr.error(data.responseJSON.message);
                    }
                    submitBtn.classList.remove('lqd-form-submitting');
                    Alpine.store('appLoadingIndicator').hide();
                    document.querySelector('#workbook_regenerate')?.classList?.add('hidden');
                    submitBtn.disabled = false;
                    const editArea = document.querySelector('.tox-edit-area');
                    editArea.querySelector('.lqd-typing')?.classList?.add('lqd-is-hidden');
                }
            });
            return false;
        };

        $("#btn_regenerate").on('click', function() {
            $("#openai_generator_button").click();
        })

        const deleteButton = document.getElementById("workbook_delete");
        deleteButton.addEventListener("click", clearWorkbookContent);

        function clearWorkbookContent() {
            const editor = tinyMCE.activeEditor;
            if (editor) {
                editor.setContent("");
            }
        }
    </script>
@endpush
