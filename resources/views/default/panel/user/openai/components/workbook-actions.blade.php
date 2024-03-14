<div class="flex w-full flex-wrap items-center gap-2 text-2xs">
    @if ($type !== 'code' && $type !== 'image')
        <button
            class="size-7 inline-flex items-center justify-center rounded-sm transition-colors hover:bg-foreground/5"
            id="workbook_undo"
            title="{{ __('Undo') }}"
        >
            <x-tabler-arrow-back-up class="size-5" />
        </button>
        <button
            class="size-7 inline-flex items-center justify-center rounded-sm transition-colors hover:bg-foreground/5"
            id="workbook_redo"
            title="{{ __('Redo') }}"
        >
            <x-tabler-arrow-forward-up class="size-5" />
        </button>
    @endif
    @if ($type !== 'image')
        <button
            class="size-7 inline-flex items-center justify-center rounded-sm transition-colors hover:bg-foreground/5"
            id="workbook_copy"
            title="{{ __('Copy to clipboard') }}"
        >
            <x-tabler-copy class="size-5" />
        </button>
    @endif
    @if ($type !== 'code')
        @if ($type === 'image')
            <a
                class="size-7 inline-flex items-center justify-center rounded-sm transition-colors hover:bg-foreground/5"
                href="{{ $output }}"
                download
                title="{{ __('Download') }}"
            >
                <x-tabler-download class="size-5" />
            </a>
        @elseif ($type === 'voiceover')
            <a
                class="size-7 inline-flex items-center justify-center rounded-sm transition-colors hover:bg-foreground/5"
                href="/uploads/{{ $output }}"
                download
                target="_blank"
                title="{{ __('Download') }}"
            >
                <x-tabler-download class="size-5" />
            </a>
        @else
            <x-dropdown.dropdown offsetY="1rem">
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
                        data-doc-name="{{ $title }}"
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
                        data-doc-name="{{ $title }}"
                    >
                        <x-tabler-brand-html5
                            class="size-6"
                            stroke-width="1.5"
                        />
                        HTML
                    </button>
                </x-slot:dropdown>
            </x-dropdown.dropdown>
        @endif
    @endif
    <a
        class="size-7 inline-flex items-center justify-center rounded-sm text-2xs text-red-600 transition-colors hover:bg-foreground/5"
        id="workbook_delete"
        href="{{ isset($delete_handeled) ? 'javascript:void(0);' : LaravelLocalization::localizeUrl(route('dashboard.user.openai.documents.delete', $slug)) }}"
        @if (!isset($delete_handeled)) onclick="return confirm('Are you sure?')" @endif
        title="{{ __('Delete') }}"
    >
        <x-tabler-circle-minus class="size-5" />
    </a>
</div>
