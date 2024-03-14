@php
    $creativity_levels = [
        '0.25' => 'Economic',
        '0.5' => 'Average',
        '0.75' => 'Good',
        '1' => 'Premium',
    ];

    $voice_tones = ['Professional', 'Funny', 'Casual', 'Excited', 'Witty', 'Sarcastic', 'Feminine', 'Masculine', 'Bold', 'Dramatic', 'Grumpy', 'Secretive'];

    $youtube_actions = [
        'blog' => 'Prepare a Blog Post',
        'short' => 'Explain the Main Idea',
        'list' => 'Create a List',
        'tldr' => 'Create TLDR',
        'prons_cons' => 'Prepare Pros and Cons',
    ];
@endphp

<div
    class="lqd-generator-options px-5 pb-8"
    id="lqd-generator-options"
>
    <x-card class="mb-5 text-2xs">
        <x-remaining-credit style="inline" />
    </x-card>

    <form
        class="flex flex-wrap justify-between gap-y-5"
        id="openai_generator_form"
    >
        @foreach (json_decode($openai->questions) as $question)
            <div class="w-full">
                @php
                    $placeholder = isset($question->description) && !empty($question->description) ? __($question->description) : __($question->question);
                @endphp
                @if ($question->type == 'text')
                    <x-forms.input
                        id="{{ $question->name }}"
                        label="{{ __($question->question) }}"
                        type="{{ $question->type }}"
                        name="{{ $question->name }}"
                        size="lg"
                        maxlength="{{ $setting->openai_max_input_length }}"
                        placeholder="{{ __($placeholder) }}"
                        required
                    />
                @elseif($question->type == 'textarea')
                    <x-forms.input
                        id="{{ $question->name }}"
                        label="{{ __($question->question) }}"
                        type="textarea"
                        name="{{ $question->name }}"
                        size="lg"
                        rows="12"
                        placeholder="{{ __($placeholder) }}"
                        maxlength="{{ $setting->openai_max_input_length }}"
                        required
                    />
                @elseif($question->type == 'select')
                    <x-forms.input
                        id="{{ $question->name }}"
                        label="{{ __($question->question) }}"
                        type="select"
                        name="{{ $question->name }}"
                        size="lg"
                        required
                    >
                        {!! $question->select !!}
                    </x-forms.input>
                @elseif($question->type == 'rss_feed')
                    <x-forms.input
                        id="{{ $question->name }}"
                        label="{{ __($question->question) }}"
                        type="{{ $question->type }}"
                        name="{{ $question->name }}"
                        size="lg"
                        maxlength="{{ $setting->openai_max_input_length }}"
                        placeholder="{{ __($placeholder) }}"
                        required
                    >
                        <x-slot:action>
                            <button
                                class="fetch-rss flex h-full items-center gap-2 rounded-e-input px-3 text-2xs font-medium transition-colors hover:bg-secondary hover:text-secondary-foreground"
                                type="button"
                            >
                                <x-tabler-refresh class="size-4" />
                                {{ __('Fetch RSS') }}
                            </button>
                        </x-slot:action>
                    </x-forms.input>
                @elseif($question->type == 'url')
                    <x-forms.input
                        id="{{ $question->name }}"
                        label="{{ __($question->question) }}"
                        type="{{ $question->type }}"
                        name="{{ $question->name }}"
                        maxlength="{{ $setting->openai_max_input_length }}"
                        placeholder="{{ __($placeholder) }}"
                        size="lg"
                        required
                    />
                @endif
            </div>
        @endforeach

        @if ($openai->type == 'youtube')
            <x-forms.input
                id="youtube_action"
                label="{{ __('Action') }}"
                type="select"
                name="youtube_action"
                size="lg"
                required
            >
                @foreach ($youtube_actions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-forms.input>

            <x-forms.input
                id="language"
                label="{{ __('Language') }}"
                name="language"
                type="select"
                size="lg"
                required
            >
                @include('panel.user.openai.components.countries')
            </x-forms.input>
        @endif

        @if ($openai->type == 'text' || $openai->type == 'rss')
            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="language"
                label="{{ __('Language') }}"
                type="select"
                name="language"
                size="lg"
                required
            >
                @include('panel.user.openai.components.countries')
            </x-forms.input>

            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="maximum_length"
                label="{{ __('Maximum Length') }}"
                type="number"
                name="maximum_length"
                max="{{ $setting->openai_max_output_length }}"
                value="{{ $setting->openai_max_output_length }}"
                placeholder="{{ __('Maximum character length of text') }}"
                required
                size="lg"
            />

            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="number_of_results"
                label="{{ __('Number of Results') }}"
                type="number"
                name="number_of_results"
                value="1"
                size="lg"
                placeholder="{{ __('Number of results') }}"
                required
            />

            <x-forms.input
                class:container="w-full md:w-[48%]"
                id="creativity"
                size="lg"
                type="select"
                label="{{ __('Creativity') }}"
                containerClass="w-full md:w-[48%]"
                name="creativity"
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
                class:container="w-full md:w-[48%]"
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
        @endif
        <input
            id="openai_type"
            hidden
            value="{{ $openai->type }}"
        >
        <input
            id="openai_slug"
            hidden
            value="{{ $openai->slug }}"
        >
        <input
            id="openai_id"
            hidden
            value="{{ $openai->id }}"
        >
        <input
            id="openai_questions"
            hidden
            value="{{ $openai->questions }}"
        >

        <x-button
            class="w-full"
            id="openai_generator_button"
            size="lg"
            type="button"
        >
            <span class="hidden group-[.lqd-form-submitting]:inline-flex">{{ __('Please wait...') }}</span>
            <span class="group-[.lqd-form-submitting]:hidden">{{ __('Generate') }}</span>
        </x-button>

    </form>
</div>
