@php
    $creativity_levels = [
        '0.25' => 'Economic',
        '0.5' => 'Average',
        '0.75' => 'Good',
        '1' => 'Premium',
    ];

    $voice_tones = ['Professional', 'Funny', 'Casual', 'Excited', 'Witty', 'Sarcastic', 'Feminine', 'Masculine', 'Bold', 'Dramatic', 'Grumpy', 'Secretive'];
@endphp

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

        @if ($openai->type != 'image')
            <x-card
                variant="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.variant', 'solid') }}"
                size="{{ Theme::getSetting('defaultVariations.card.variant', 'outline') === 'outline' ? 'none' : Theme::getSetting('defaultVariations.card.size', 'md') }}"
                roundness="{{ Theme::getSetting('defaultVariations.card.roundness', 'default') === 'default' ? 'none' : Theme::getSetting('defaultVariations.card.roundness', 'default') }}"
            >
                <form
                    class="lqd-generator-form flex flex-col gap-5"
                    id="openai_generator_form"
                    onsubmit="return sendOpenaiGeneratorForm();"
                    enctype="multipart/form-data"
                >
                    @foreach (json_decode($openai->questions) as $question)
                        <div class="mb-[20px]">
                            @if ($question->type == 'text')
                                <x-forms.input
                                    id="{{ $question->name }}"
                                    size="lg"
                                    label="{{ __($question->question) }}"
                                    type="{{ $question->type }}"
                                    name="{{ $question->name }}"
                                    placeholder="{{ __($question->question) }}"
                                    required
                                />
                            @elseif($question->type == 'textarea')
                                <x-forms.input
                                    id="{{ $question->name }}"
                                    size="lg"
                                    label="{{ __($question->question) }}"
                                    name="{{ $question->name }}"
                                    type="textarea"
                                    rows="8"
                                    placeholder="{{ __($question->question) }}"
                                    required
                                />
                            @elseif($question->type == 'select')
                                <x-forms.input
                                    id="{{ $question->name }}"
                                    size="lg"
                                    label="{{ __($question->question) }}"
                                    name="{{ $question->name }}"
                                    type="select"
                                    required
                                >
                                    {!! $question->select !!}
                                </x-forms.input>
                            @elseif($question->type == 'file')
                                <x-forms.input
                                    id="{{ $question->name }}"
                                    size="lg"
                                    label="{{ __($question->question) }}"
                                    name="{{ $question->name }}"
                                    type="file"
                                    placeholder="{{ __($question->question) }}"
                                    required
                                />
                            @endif
                        </div>
                    @endforeach

                    @if ($openai->type == 'text')
                        <x-forms.input
                            id="maximum_length"
                            size="lg"
                            label="{{ __('Maximum Length') }}"
                            name="maximum_length"
                            type="number"
                            max="{{ Auth::user()->remaining_words }}"
                            placeholder="{{ __('Maximum character length of text') }}"
                            required
                        />

                        <x-forms.input
                            id="creativity"
                            size="lg"
                            label="{{ __('Creativity') }}"
                            name="creativity"
                            type="select"
                            required
                        >
                            @foreach ($creativity_levels as $creativity => $label)
                                <option
                                    value="{{ $creativity }}"
                                    @selected($setting->openai_default_creativity == $creativity)
                                >
                                    {{ __($label) }}
                                </option>
                            @endforeach
                        </x-forms.input>

                        <x-forms.input
                            id="language"
                            size="lg"
                            label="{{ __('Language') }}"
                            name="language"
                            type="select"
                            required
                        >
                            @include('panel.user.openai.components.countries')
                        </x-forms.input>

                        <x-forms.input
                            id="language"
                            size="lg"
                            label="{{ __('Language') }}"
                            name="language"
                            type="select"
                            required
                        >
                            @include('panel.user.openai.components.countries')
                        </x-forms.input>

                        <x-forms.input
                            id="tone_of_voice"
                            size="lg"
                            type="select"
                            label="{{ __('Tone of Voice') }}"
                            containerClass="w-full md:w-[48%]"
                            name="tone_of_voice"
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
                            id="number_of_results"
                            size="lg"
                            type="number"
                            label="{{ __('Number of Results') }}"
                            name="number_of_results"
                            value="1"
                            placeholder="{{ __('Maximum character length of text') }}"
                            required
                        />
                    @endif

                    <x-button
                        class="w-full"
                        id="openai_generator_button"
                        size="lg"
                        tag="button"
                        type="submit"
                        form="openai_generator_form"
                    >
                        {{ __('Generate') }}
                    </x-button>
                </form>
            </x-card>
        @endif
    </div>

    <x-card
        id="generator_sidebar_table"
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
        @include('panel.user.openai.components.generator_sidebar_table')
    </x-card>
</div>

@push('script')
    @if ($openai->type == 'code')
        <link
            rel="stylesheet"
            href="{{ custom_theme_url('/assets/libs/prism/prism.css') }}"
        >
        <script src="{{ custom_theme_url('/assets/libs/prism/prism.js') }}"></script>
        <script>
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
