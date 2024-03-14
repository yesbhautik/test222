@extends('panel.layout.app')
@section('title', __('TTS Settings'))
@section('titlebar_actions', '')
@section('additional_css')
    <link
        href="{{ custom_theme_url('/assets/libs/select2/select2.min.css') }}"
        rel="stylesheet"
    />
@endsection
@section('content')
    <!-- Page body -->
    <div class="py-10">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-5 mx-auto">
                    <form
                        id="settings_form"
                        onsubmit="return ttsSettingsSave();"
                        enctype="multipart/form-data"
                    >
                        <h3 class="mb-[25px] text-[20px]">{{ __('TTS Settings') }}</h3>
                        <div class="row">

                            @if ($app_is_demo)
                                <div class="col-md-12">
                                    {{-- <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="feature_tts_"
                                                {{ $setting->feature_ai_writer ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ __('Google TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="feature_tts_openai"
                                                {{ $setting->feature_ai_writer ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ __('OpenAI TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="feature_tts_elevenlabs"
                                                {{ $setting->feature_ai_writer ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ __('Elevenlabs TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('ElevenLabs API Key') }}</label>
                                        <input type="text" class="form-control" id="elevenlabs_api_key" name="elevenlabs_api_key"
                                            placeholder="*********************" value="*********************">
                                    </div> --}}
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('GCS File (JSON) path') }}</label>
                                        <input
                                            class="form-control"
                                            id="gcs_file"
                                            type="text"
                                            name="gcs_file"
                                            placeholder="*********************"
                                            value="*********************"
                                        >
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('GCS Project Name') }}</label>
                                        <input
                                            class="form-control"
                                            id="gcs_name"
                                            type="text"
                                            name="gcs_name"
                                            placeholder="{{ __('my-project-123') }}"
                                            value="*********************"
                                        >
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                id="feature_tts_google"
                                                type="checkbox"
                                                {{ $settings_two->feature_tts_google ? 'checked' : '' }}
                                            >
                                            <span class="form-check-label">{{ __('Google TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                id="feature_tts_openai"
                                                type="checkbox"
                                                {{ $settings_two->feature_tts_openai ? 'checked' : '' }}
                                            >
                                            <span class="form-check-label">{{ __('OpenAI TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                id="feature_tts_elevenlabs"
                                                type="checkbox"
                                                {{ $settings_two->feature_tts_elevenlabs ? 'checked' : '' }}
                                            >
                                            <span class="form-check-label">{{ __('Elevenlabs TTS') }}</span>
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('ElevenLabs API Key') }}</label>
                                        <input
                                            class="form-control"
                                            id="elevenlabs_api_key"
                                            type="text"
                                            name="elevenlabs_api_key"
                                            placeholder="ElevenLabs API Key"
                                            value="{{ $settings_two->elevenlabs_api_key }}"
                                        >
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('GCS File (JSON) path') }}</label>
                                        <input
                                            class="form-control"
                                            id="gcs_file"
                                            type="text"
                                            name="gcs_file"
                                            placeholder="googlefile.json"
                                            value="{{ $setting->gcs_file }}"
                                        >
                                        <x-alert
                                            class="mt-2"
                                            variant="warn"
                                        >
                                            <p>
                                                {{ __('Please upload your file to the /public_html/storage folder within your project and provide the file name in the space provided.') }}
                                            </p>
                                        </x-alert>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('GCS Project Name') }}</label>
                                        <input
                                            class="form-control"
                                            id="gcs_name"
                                            type="text"
                                            name="gcs_name"
                                            placeholder="{{ __('my-project-123') }}"
                                            value="{{ $setting->gcs_name }}"
                                        >
                                    </div>
                                </div>
                            @endif

                        </div>
                        <button
                            class="btn btn-primary w-full"
                            id="settings_button"
                            form="settings_form"
                        >
                            {{ __('Save') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
