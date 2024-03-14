@include('panel.user.openai.components.workbook-actions', [
    'type' => $workbook->generator->type,
    'title' => $workbook->title,
    'slug' => $workbook->slug,
    'output' => $workbook->output,
])

<div class="mt-4 border-t pt-8 [&_.tox-edit-area__iframe]:!bg-transparent">
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
            onsubmit="editWorkbook('{{ $workbook->slug }}'); return false;"
            method="POST"
        >
            @csrf
            <x-forms.input
                class="border-transparent font-serif text-2xl"
                id="workbook_title"
                placeholder="{{ __('Untitled Document...') }}"
                value="{{ $workbook->title }}"
            />
            <x-forms.input
                class="tinymce font-body border-0"
                id="workbook_text"
                type="textarea"
                rows="25"
            >{!! $workbook->output !!}</x-forms.input>
            <x-button
                class="w-full"
                id="workbook_button"
                tag="button"
                type="submit"
                variant="primary"
                size="lg"
            >
                <span class="group-[&.loading]/form:hidden">{{ __('Save') }}</span>
                <span class="hidden group-[&.loading]/form:inline-block">{{ __('Please wait...') }}</span>
            </x-button>
        </form>
    @endif
</div>
