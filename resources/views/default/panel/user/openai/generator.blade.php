@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __($openai->title))
@section('titlebar_subtitle', __($openai->description))

@section('content')
    <div class="py-10">
        @if ($openai->type == 'image')
            @include('panel.user.openai.components.generator_image')
        @elseif ($openai->type == 'video')
            @include('panel.user.openai.components.generator_video')
        @elseif($openai->type == 'voiceover')
            @include('panel.user.openai.components.generator_voiceover')
        @else
            @include('panel.user.openai.components.generator_others')
        @endif
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/openai_generator.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/fslightbox/fslightbox.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/wavesurfer/wavesurfer.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/voiceover.js') }}"></script>

    @if ($openai->type == 'voiceover')
        <script>
            function generateSpeech() {
                "use strict";

                document.getElementById("generate_speech_button").disabled = true;
                document.getElementById("generate_speech_button").innerHTML = magicai_localize.please_wait;

                var formData = new FormData();
                var speechData = [];
                formData.append('workbook_title', $('#workbook_title').val());

                let aContent = "";

                $('.speeches .speech').each(function() {
                    var data = {
                        voice: $(this).find('textarea').attr('data-voice'),
                        lang: $(this).find('textarea').attr('data-lang'),
                        language: $(this).find('textarea').attr('data-language'),
                        pace: $(this).find('textarea').attr('data-pace'),
                        platform: $(this).find('textarea').attr('data-platform'),
                        break: $(this).find('textarea').attr('data-break'),
                        voice_openai: $(this).find('textarea').attr('data-voiceopenai'),
                        model_openai: $(this).find('textarea').attr('data-modelopenai'),
                        content: $(this).find('textarea').val(),
                        name: $(this).find('textarea').attr('name')
                    };
                    // console.log(data);
                    speechData.push(data);
                    aContent += data.content + "";
                });
                var jsonData = JSON.stringify(speechData);
                formData.append('speeches', jsonData);

                $.ajax({
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    url: "/dashboard/user/openai/generate-speech",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        toastr.success("{{ __('Generated') }}");
                        document.getElementById("generate_speech_button").disabled = false;
                        document.getElementById("generate_speech_button").innerHTML = "{{ __('Generate') }}";
                        $("#generator_sidebar_table").html(data.html2);

                        var audioElements = document.querySelectorAll('.data-audio');
                        audioElements.forEach(generateWaveForm);
                    },
                    error: function(data) {
                        var err = data.responseJSON.errors;
                        $.each(err, function(index, value) {
                            toastr.error(value);
                        });
                        document.getElementById("generate_speech_button").disabled = false;
                        // document.getElementById("generate_speech_button").innerHTML = "{{ __('Save') }}";
                    }
                });
                return false;
            }

            const openaiVoiceData = [{
                value: "alloy",
                label: "Alloy (Male)"
            }, {
                value: "echo",
                label: "Echo (Male)"
            }, {
                value: "fable",
                label: "Fable (Male)"
            }, {
                value: "onyx",
                label: "Onyx (Male)"
            }, {
                value: "nova",
                label: "Nova (Female)"
            }, {
                value: "shimmer",
                label: "Shimmer (Female)"
            }];

            const allowedOpenAIList = [
                "af-ZA",
                "ar-XA",
                "eu-ES",
                "bg-BG",
                "ca-ES",
                "cs-CZ",
                "da-DK",
                "nl-BE",
                "en-US",
                "fi-FI",
                "fr-FR",
                "gl-ES",
                "de-DE",
                "el-GR",
                "he-IL",
                "hi-IN",
                "hu-HU",
                "is-IS",
                "id-ID",
                "it-IT",
                "ja-JP",
                "kn-IN",
                "ko-KR",
                "lv-LV",
                "ms-MY",
                "ml-IN",
                "nb-NO",
                "pl-PL",
                "pt-PT",
                "ro-RO",
                "ru-RU",
                "sr-RS",
                "sk-SK",
                "es-ES",
                "ta-IN",
                "th-TH",
                "tr-TR",
                "uk-UA",
                "vi-VN"
            ];

            const allowedElevenLabsList = [
                "en-US",
                "en-AU",
                "en-IN",
                "en-GB",
                "hi-IN",
                "pt-PT",
                "es-ES",
                "fr-FR",
                "de-DE",
                "ja-JP",
                "ar-XA",
                "ru-RU",
                "ko-KR",
                "id-ID",
                "it-IT",
                "nl-NL",
                "tr-TR",
                "pl-PL",
                "sv-SE",
                "fil-PH",
                "ms-MY",
                "ro-RO",
                "uk-UA",
                "el-GR",
                "cs-CZ",
                "da-DK",
                "fi-FI",
                "bg-BG",
                "sk-SK",
                "ta-IN",
            ];

            const elevenLabsVoices = [
                @if ($elevenlabs)
                    @foreach ($elevenlabs as $voice)
                        {
                            "voice_id": "{{ $voice->voice_id }}",
                            "name": "{{ $voice->name }}",
                        },
                    @endforeach
                @endif {
                    "voice_id": "21m00Tcm4TlvDq8ikWAM",
                    "name": "Rachel",
                },
                {
                    "voice_id": "29vD33N1CtxCmqQRPOHJ",
                    "name": "Drew",
                },
                {
                    "voice_id": "2EiwWnXFnvU5JabPnv8n",
                    "name": "Clyde",
                },
                {
                    "voice_id": "5Q0t7uMcjvnagumLfvZi",
                    "name": "Paul",
                },
                {
                    "voice_id": "AZnzlk1XvdvUeBnXmlld",
                    "name": "Domi",
                },
                {
                    "voice_id": "CYw3kZ02Hs0563khs1Fj",
                    "name": "Dave",
                },
                {
                    "voice_id": "D38z5RcWu1voky8WS1ja",
                    "name": "Fin",
                },
                {
                    "voice_id": "EXAVITQu4vr4xnSDxMaL",
                    "name": "Sarah",
                },
                {
                    "voice_id": "ErXwobaYiN019PkySvjV",
                    "name": "Antoni",
                },
                {
                    "voice_id": "GBv7mTt0atIp3Br8iCZE",
                    "name": "Thomas",
                },
                {
                    "voice_id": "IKne3meq5aSn9XLyUdCD",
                    "name": "Charlie",
                },
                {
                    "voice_id": "JBFqnCBsd6RMkjVDRZzb",
                    "name": "George",
                },
                {
                    "voice_id": "LcfcDJNUP1GQjkzn1xUU",
                    "name": "Emily",
                },
                {
                    "voice_id": "MF3mGyEYCl7XYWbV9V6O",
                    "name": "Elli",
                },
                {
                    "voice_id": "N2lVS1w4EtoT3dr4eOWO",
                    "name": "Callum",
                },
                {
                    "voice_id": "ODq5zmih8GrVes37Dizd",
                    "name": "Patrick",
                },
                {
                    "voice_id": "SOYHLrjzK2X1ezoPC6cr",
                    "name": "Harry",
                },
                {
                    "voice_id": "TX3LPaxmHKxFdv7VOQHJ",
                    "name": "Liam",
                },
                {
                    "voice_id": "ThT5KcBeYPX3keUQqHPh",
                    "name": "Dorothy",
                },
                {
                    "voice_id": "TxGEqnHWrfWFTfGW9XjX",
                    "name": "Josh",
                },
                {
                    "voice_id": "VR6AewLTigWG4xSOukaG",
                    "name": "Arnold",
                },
                {
                    "voice_id": "XB0fDUnXU5powFXDhCwa",
                    "name": "Charlotte",
                },
                {
                    "voice_id": "XrExE9yKIg1WjnnlVkGX",
                    "name": "Matilda",
                },
                {
                    "voice_id": "Yko7PKHZNXotIFUBG7I9",
                    "name": "Matthew",
                },
                {
                    "voice_id": "ZQe5CZNOzWyzPSCn5a3c",
                    "name": "James",
                },
                {
                    "voice_id": "Zlb1dXrM653N07WRdFW3",
                    "name": "Joseph",
                },
                {
                    "voice_id": "bVMeCyTHy58xNoL34h3p",
                    "name": "Jeremy",
                },
                {
                    "voice_id": "flq6f7yk4E4fJM5XTYuZ",
                    "name": "Michael",
                },
                {
                    "voice_id": "g5CIjZEefAph4nQFvHAz",
                    "name": "Ethan",
                },
                {
                    "voice_id": "jBpfuIE2acCO8z3wKNLl",
                    "name": "Gigi",
                },
                {
                    "voice_id": "jsCqWAovK2LkecY7zXl4",
                    "name": "Freya",
                },
                {
                    "voice_id": "knrPHWnBmmDHMoiMeP3l",
                    "name": "🎅 Santa",
                },
                {
                    "voice_id": "oWAxZDx7w5VEj9dCyTzz",
                    "name": "Grace",
                },
                {
                    "voice_id": "onwK4e9ZLuTAKqWW03F9",
                    "name": "Daniel",
                },
                {
                    "voice_id": "pFZP5JQG7iQjIQuC4Bku",
                    "name": "Lily",
                },
                {
                    "voice_id": "pMsXgVXv3BLzUgSXRplE",
                    "name": "Serena",
                },
                {
                    "voice_id": "pNInz6obpgDQGcFmaJgB",
                    "name": "Adam",
                },
                {
                    "voice_id": "piTKgcLEGmPE4e6mEKli",
                    "name": "Nicole",
                },
                {
                    "voice_id": "pqHfZKP75CvOlQylNhV4",
                    "name": "Bill",
                },
                {
                    "voice_id": "t0jbNlBVZ17f02VDIeMI",
                    "name": "Jessie",
                },
                {
                    "voice_id": "yoZ06aMxZJJ28mfd3POQ",
                    "name": "Sam",
                },
                {
                    "voice_id": "z9fAnlkpzviPz146aGWa",
                    "name": "Glinda",
                },
                {
                    "voice_id": "zcAOhNBS3c14rBihAFp1",
                    "name": "Giovanni",
                },
                {
                    "voice_id": "zrHiDhphv9ZnVXBqCLjz",
                    "name": "Mimi",
                }
            ];

            const voicesData = {
                "af-ZA": [{
                    value: "af-ZA-Standard-A",
                    label: "Ayanda ({{ __('Female') }})"
                }],
                "ar-XA": [{
                        value: "ar-XA-Standard-A",
                        label: "Fatima ({{ __('Female') }})"
                    },
                    {
                        value: "ar-XA-Standard-B",
                        label: "Ahmed ({{ __('Male') }})"
                    },
                    {
                        value: "ar-XA-Standard-C",
                        label: "Mohammed ({{ __('Male') }})"
                    },
                    {
                        value: "ar-XA-Standard-D",
                        label: "Aisha ({{ __('Female') }})"
                    },
                    {
                        value: "ar-XA-Wavenet-A",
                        label: "Layla ({{ __('Female') }})"
                    },
                    {
                        value: "ar-XA-Wavenet-B",
                        label: "Ali ({{ __('Male') }})"
                    },
                    {
                        value: "ar-XA-Wavenet-C",
                        label: "Omar ({{ __('Male') }})"
                    },
                    {
                        value: "ar-XA-Wavenet-D",
                        label: "Zahra ({{ __('Female') }})"
                    }
                ],
                "eu-ES": [{
                    value: "eu-ES-Standard-A",
                    label: "Ane ({{ __('Female') }})"
                }],
                "bn-IN": [{
                        value: "bn-IN-Standard-A",
                        label: "Ananya ({{ __('Female') }})"
                    },
                    {
                        value: "bn-IN-Standard-B",
                        label: "Aryan ({{ __('Male') }})"
                    },
                    {
                        value: "bn-IN-Wavenet-A",
                        label: "Ishita ({{ __('Female') }})"
                    },
                    {
                        value: "bn-IN-Wavenet-B",
                        label: "Arry ({{ __('Male') }})"
                    }
                ],
                "bg-BG": [{
                    value: "bg-BG-Standard-A",
                    label: "Elena ({{ __('Female') }})"
                }],
                "ca-ES": [{
                    value: "ca-ES-Standard-A",
                    label: "Laia ({{ __('Female') }})"
                }],
                "yue-HK": [{
                        value: "yue-HK-Standard-A",
                        label: "Wing ({{ __('Female') }})"
                    },
                    {
                        value: "yue-HK-Standard-B",
                        label: "Ho ({{ __('Male') }})"
                    },
                    {
                        value: "yue-HK-Standard-C",
                        label: "Siu ({{ __('Female') }})"
                    },
                    {
                        value: "yue-HK-Standard-D",
                        label: "Lau ({{ __('Male') }})"
                    }
                ],
                "cs-CZ": [{
                        value: "cs-CZ-Standard-A",
                        label: "Tereza ({{ __('Female') }})"
                    },
                    {
                        value: "cs-CZ-Wavenet-A",
                        label: "Karolína ({{ __('Female') }})"
                    }
                ],
                "da-DK": [
                    //{value:"da-DK-Neural2-D", label: "Neural2 - FEMALE"},
                    //{value:"da-DK-Neural2-F", label: "Neural2 - MALE"},
                    {
                        value: "da-DK-Standard-A",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Standard-A",
                        label: "Freja ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Standard-A",
                        label: "Ida ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Standard-C",
                        label: "Noah ({{ __('Male') }})"
                    },
                    {
                        value: "da-DK-Standard-D",
                        label: "Mathilde ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Standard-E",
                        label: "Clara ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Wavenet-A",
                        label: "Isabella ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Wavenet-C",
                        label: "Lucas ({{ __('Male') }})"
                    },
                    {
                        value: "da-DK-Wavenet-D",
                        label: "Olivia ({{ __('Female') }})"
                    },
                    {
                        value: "da-DK-Wavenet-E",
                        label: "Emily ({{ __('Female') }})"
                    }
                ],
                "nl-BE": [{
                        value: "nl-BE-Standard-A",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "nl-BE-Standard-B",
                        label: "Thomas ({{ __('Male') }})"
                    },
                    {
                        value: "nl-BE-Wavenet-A",
                        label: "Sophie ({{ __('Female') }})"
                    },
                    {
                        value: "nl-BE-Wavenet-B",
                        label: "Lucas ({{ __('Male') }})"
                    }
                ],
                "nl-NL": [{
                        value: "nl-NL-Standard-A",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "nl-NL-Standard-B",
                        label: "Daan ({{ __('Male') }})"
                    },
                    {
                        value: "nl-NL-Standard-C",
                        label: "Luuk ({{ __('Male') }})"
                    },
                    {
                        value: "nl-NL-Standard-D",
                        label: "Lotte ({{ __('Female') }})"
                    },
                    {
                        value: "nl-NL-Standard-E",
                        label: "Sophie ({{ __('Female') }})"
                    },
                    {
                        value: "nl-NL-Wavenet-A",
                        label: "Mila ({{ __('Female') }})"
                    },
                    {
                        value: "nl-NL-Wavenet-B",
                        label: "Sem ({{ __('Male') }})"
                    },
                    {
                        value: "nl-NL-Wavenet-C",
                        label: "Stijn ({{ __('Male') }})"
                    },
                    {
                        value: "nl-NL-Wavenet-D",
                        label: "Fenna ({{ __('Female') }})"
                    },
                    {
                        value: "nl-NL-Wavenet-E",
                        label: "Eva ({{ __('Female') }})"
                    }
                ],
                "en-AU": [
                    //{value:"en-AU-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"en-AU-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"en-AU-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"en-AU-Neural2-D", label: "Neural2 - MALE"},
                    {
                        value: "en-AU-News-E",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-News-F",
                        label: "Olivia ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-News-G",
                        label: "Liam ({{ __('Male') }})"
                    },
                    //{value:"en-AU-Polyglot-1", label: "Noah ({{ __('Male') }})"},
                    {
                        value: "en-AU-Standard-A",
                        label: "Charlotte ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-Standard-B",
                        label: "Oliver ({{ __('Male') }})"
                    },
                    {
                        value: "en-AU-Standard-C",
                        label: "Ava ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-Standard-D",
                        label: "Jack ({{ __('Male') }})"
                    },
                    {
                        value: "en-AU-Wavenet-A",
                        label: "Sophie ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-Wavenet-B",
                        label: "William ({{ __('Male') }})"
                    },
                    {
                        value: "en-AU-Wavenet-C",
                        label: "Amelia ({{ __('Female') }})"
                    },
                    {
                        value: "en-AU-Wavenet-D",
                        label: "Thomas ({{ __('Male') }})"
                    }
                ],
                "en-IN": [{
                        value: "en-IN-Standard-A",
                        label: "Aditi ({{ __('Female') }})"
                    },
                    {
                        value: "en-IN-Standard-B",
                        label: "Arjun ({{ __('Male') }})"
                    },
                    {
                        value: "en-IN-Standard-C",
                        label: "Rohan ({{ __('Male') }})"
                    },
                    {
                        value: "en-IN-Standard-D",
                        label: "Ananya ({{ __('Female') }})"
                    },
                    {
                        value: "en-IN-Wavenet-A",
                        label: "Alisha ({{ __('Female') }})"
                    },
                    {
                        value: "en-IN-Wavenet-B",
                        label: "Aryan ({{ __('Male') }})"
                    },
                    {
                        value: "en-IN-Wavenet-C",
                        label: "Kabir ({{ __('Male') }})"
                    },
                    {
                        value: "en-IN-Wavenet-D",
                        label: "Diya ({{ __('Female') }})"
                    }
                ],
                "en-GB": [
                    //{value:"en-GB-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"en-GB-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"en-GB-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"en-GB-Neural2-D", label: "Neural2 - MALE"},
                    //{value:"en-GB-Neural2-F", label: "Neural2 - FEMALE"},
                    {
                        value: "en-GB-News-G",
                        label: "Amelia ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-News-H",
                        label: "Elise ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-News-I",
                        label: "Isabella ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-News-J",
                        label: "Jessica ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-News-K",
                        label: "Alexander ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-News-L",
                        label: "Benjamin ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-News-M",
                        label: "Charles ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-Standard-A",
                        label: "Emily ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-Standard-B",
                        label: "John ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-Standard-C",
                        label: "Mary ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-Standard-D",
                        label: "Peter ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-Standard-F",
                        label: "Sarah ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-Wavenet-A",
                        label: "Ava ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-Wavenet-B",
                        label: "David ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-Wavenet-C",
                        label: "Emily ({{ __('Female') }})"
                    },
                    {
                        value: "en-GB-Wavenet-D",
                        label: "James ({{ __('Male') }})"
                    },
                    {
                        value: "en-GB-Wavenet-F",
                        label: "Sophie ({{ __('Female') }})"
                    }
                ],
                "en-US": [
                    //{value:"en-US-Neural2-A", label: "Neural2 - MALE"},
                    //{value:"en-US-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"en-US-Neural2-D", label: "Neural2 - MALE"},
                    //{value:"en-US-Neural2-E", label: "Neural2 - FEMALE"},
                    //{value:"en-US-Neural2-F", label: "Neural2 - FEMALE"},
                    //{value:"en-US-Neural2-G", label: "Neural2 - FEMALE"},
                    //{value:"en-US-Neural2-H", label: "Neural2 - FEMALE"},
                    //{value:"en-US-Neural2-I", label: "Neural2 - MALE"},
                    //{value:"en-US-Neural2-J", label: "Neural2 - MALE"},
                    {
                        value: "en-US-News-K",
                        label: "Lily ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-News-L",
                        label: "Olivia ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-News-M",
                        label: "Noah ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-News-N",
                        label: "Oliver ({{ __('Male') }})"
                    },
                    //{value:"en-US-Polyglot-1", label:"John ({{ __('Male') }})"},
                    {
                        value: "en-US-Standard-A",
                        label: "Michael ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Standard-B",
                        label: "David ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Standard-C",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Standard-D",
                        label: "William ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Standard-E",
                        label: "Ava ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Standard-F",
                        label: "Sophia ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Standard-G",
                        label: "Isabella ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Standard-H",
                        label: "Charlotte ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Standard-I",
                        label: "James ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Standard-J",
                        label: "Lucas ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Studio-M",
                        label: "Benjamin ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Studio-O",
                        label: "Eleanor ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-A",
                        label: "Alexander ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Wavenet-B",
                        label: "Benjamin ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Wavenet-C",
                        label: "Emily ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-D",
                        label: "James ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Wavenet-E",
                        label: "Ava ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-F",
                        label: "Sophia ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-G",
                        label: "Isabella ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-H",
                        label: "Charlotte ({{ __('Female') }})"
                    },
                    {
                        value: "en-US-Wavenet-I",
                        label: "Alexander ({{ __('Male') }})"
                    },
                    {
                        value: "en-US-Wavenet-J",
                        label: "Lucas ({{ __('Male') }})"
                    }
                ],
                "fil-PH": [{
                        value: "fil-PH-Standard-A",
                        label: "Maria ({{ __('Female') }})"
                    },
                    {
                        value: "fil-PH-Standard-B",
                        label: "Juana ({{ __('Female') }})"
                    },
                    {
                        value: "fil-PH-Standard-C",
                        label: "Juan ({{ __('Male') }})"
                    },
                    {
                        value: "fil-PH-Standard-D",
                        label: "Pedro ({{ __('Male') }})"
                    },
                    {
                        value: "fil-PH-Wavenet-A",
                        label: "Maria ({{ __('Female') }})"
                    },
                    {
                        value: "fil-PH-Wavenet-B",
                        label: "Juana ({{ __('Female') }})"
                    },
                    {
                        value: "fil-PH-Wavenet-C",
                        label: "Juan ({{ __('Male') }})"
                    },
                    {
                        value: "fil-PH-Wavenet-D",
                        label: "Pedro ({{ __('Male') }})"
                    }
                    //{value:"fil-ph-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"fil-ph-Neural2-D", label: "Neural2 - MALE"}
                ],
                "fi-FI": [{
                        value: "fi-FI-Standard-A",
                        label: "Sofia ({{ __('Female') }})"
                    },
                    {
                        value: "fi-FI-Wavenet-A",
                        label: "Sofianna ({{ __('Female') }})"
                    }
                ],
                "fr-CA": [
                    //{value:"fr-CA-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"fr-CA-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"fr-CA-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"fr-CA-Neural2-D", label: "Neural2 - MALE"},
                    {
                        value: "fr-CA-Standard-A",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "fr-CA-Standard-B",
                        label: "Jean ({{ __('Male') }})"
                    },
                    {
                        value: "fr-CA-Standard-C",
                        label: "Gabrielle ({{ __('Female') }})"
                    },
                    {
                        value: "fr-CA-Standard-D",
                        label: "Thomas ({{ __('Male') }})"
                    },
                    {
                        value: "fr-CA-Wavenet-A",
                        label: "Amelie ({{ __('Female') }})"
                    },
                    {
                        value: "fr-CA-Wavenet-B",
                        label: "Antoine ({{ __('Male') }})"
                    },
                    {
                        value: "fr-CA-Wavenet-C",
                        label: "Gabrielle ({{ __('Female') }})"
                    },
                    {
                        value: "fr-CA-Wavenet-D",
                        label: "Thomas ({{ __('Male') }})"
                    }
                ],
                "fr-FR": [
                    //{value:"fr-FR-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"fr-FR-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"fr-FR-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"fr-FR-Neural2-D", label: "Neural2 - MALE"},
                    //{value:"fr-FR-Neural2-E", label: "Neural2 - FEMALE"},
                    //{value:"fr-FR-Polyglot-1", label:"Jean ({{ __('Male') }})"},
                    {
                        value: "fr-FR-Standard-A",
                        label: "Marie ({{ __('Female') }})"
                    },
                    {
                        value: "fr-FR-Standard-B",
                        label: "Pierre ({{ __('Male') }})"
                    },
                    {
                        value: "fr-FR-Standard-C",
                        label: "Sophie ({{ __('Female') }})"
                    },
                    {
                        value: "fr-FR-Standard-D",
                        label: "Paul ({{ __('Male') }})"
                    },
                    {
                        value: "fr-FR-Standard-E",
                        label: "Julie ({{ __('Female') }})"
                    },
                    {
                        value: "fr-FR-Wavenet-A",
                        label: "Elise ({{ __('Female') }})"
                    },
                    {
                        value: "fr-FR-Wavenet-B",
                        label: "Nicolas ({{ __('Male') }})"
                    },
                    {
                        value: "fr-FR-Wavenet-C",
                        label: "Clara ({{ __('Female') }})"
                    },
                    {
                        value: "fr-FR-Wavenet-D",
                        label: "Antoine ({{ __('Male') }})"
                    },
                    {
                        value: "fr-FR-Wavenet-E",
                        label: "Amelie ({{ __('Female') }})"
                    }
                ],
                "gl-ES": [{
                    value: "gl-ES-Standard-A",
                    label: "Ana ({{ __('Female') }})"
                }],
                "de-DE": [
                    //{value:"de-DE-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"de-DE-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"de-DE-Neural2-D", label: "Neural2 - MALE"},
                    //{value:"de-DE-Neural2-F", label: "Neural2 - FEMALE"},
                    //{value:"de-DE-Polyglot-1", label:"Johannes ({{ __('Male') }})"},
                    {
                        value: "de-DE-Standard-A",
                        label: "Anna ({{ __('Female') }})"
                    },
                    {
                        value: "de-DE-Standard-B",
                        label: "Max ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Standard-C",
                        label: "Sophia ({{ __('Female') }})"
                    },
                    {
                        value: "de-DE-Standard-D",
                        label: "Paul ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Standard-E",
                        label: "Erik ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Standard-F",
                        label: "Lina ({{ __('Female') }})"
                    },
                    {
                        value: "de-DE-Wavenet-A",
                        label: "Eva ({{ __('Female') }})"
                    },
                    {
                        value: "de-DE-Wavenet-B",
                        label: "Felix ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Wavenet-C",
                        label: "Emma ({{ __('Female') }})"
                    },
                    {
                        value: "de-DE-Wavenet-D",
                        label: "Lukas ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Wavenet-E",
                        label: "Nico ({{ __('Male') }})"
                    },
                    {
                        value: "de-DE-Wavenet-F",
                        label: "Mia ({{ __('Female') }})"
                    }
                ],
                "el-GR": [{
                        value: "el-GR-Standard-A",
                        label: "Ελένη ({{ __('Female') }})"
                    },
                    {
                        value: "el-GR-Wavenet-A",
                        label: "Ελένη ({{ __('Female') }})"
                    }
                ],
                "gu-IN": [{
                        value: "gu-IN-Standard-A",
                        label: "દિવ્યા ({{ __('Female') }})"
                    },
                    {
                        value: "gu-IN-Standard-B",
                        label: "કિશોર ({{ __('Male') }})"
                    },
                    {
                        value: "gu-IN-Wavenet-A",
                        label: "દિવ્યા ({{ __('Female') }})"
                    },
                    {
                        value: "gu-IN-Wavenet-B",
                        label: "કિશોર ({{ __('Male') }})"
                    }
                ],
                "he-IL": [{
                        value: "he-IL-Standard-A",
                        label: "Tamar ({{ __('Female') }})"
                    },
                    {
                        value: "he-IL-Standard-B",
                        label: "David ({{ __('Male') }})"
                    },
                    {
                        value: "he-IL-Standard-C",
                        label: "Michal ({{ __('Female') }})"
                    },
                    {
                        value: "he-IL-Standard-D",
                        label: "Jonathan ({{ __('Male') }})"
                    },
                    {
                        value: "he-IL-Wavenet-A",
                        label: "Yael ({{ __('Female') }})"
                    },
                    {
                        value: "he-IL-Wavenet-B",
                        label: "Eli ({{ __('Male') }})"
                    },
                    {
                        value: "he-IL-Wavenet-C",
                        label: "Abigail ({{ __('Female') }})"
                    },
                    {
                        value: "he-IL-Wavenet-D",
                        label: "Alex ({{ __('Male') }})"
                    }
                ],
                "hi-IN": [
                    //{value:"hi-IN-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"hi-IN-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"hi-IN-Neural2-C", label: "Neural2 - MALE"},
                    //{value:"hi-IN-Neural2-D", label: "Neural2 - FEMALE"},
                    {
                        value: "hi-IN-Standard-A",
                        label: "Aditi ({{ __('Female') }})"
                    },
                    {
                        value: "hi-IN-Standard-B",
                        label: "Abhishek ({{ __('Male') }})"
                    },
                    {
                        value: "hi-IN-Standard-C",
                        label: "Aditya ({{ __('Male') }})"
                    },
                    {
                        value: "hi-IN-Standard-D",
                        label: "Anjali ({{ __('Female') }})"
                    },
                    {
                        value: "hi-IN-Wavenet-A",
                        label: "Kiara ({{ __('Female') }})"
                    },
                    {
                        value: "hi-IN-Wavenet-B",
                        label: "Rohan ({{ __('Male') }})"
                    },
                    {
                        value: "hi-IN-Wavenet-C",
                        label: "Rishabh ({{ __('Male') }})"
                    },
                    {
                        value: "hi-IN-Wavenet-D",
                        label: "Srishti ({{ __('Female') }})"
                    }
                ],
                "hu-HU": [{
                        value: "hu-HU-Standard-A",
                        label: "Eszter ({{ __('Female') }})"
                    },
                    {
                        value: "hu-HU-Wavenet-A",
                        label: "Lilla ({{ __('Female') }})"
                    }
                ],
                "is-IS": [{
                    value: "is-IS-Standard-A",
                    label: "Guðrún ({{ __('Female') }})"
                }],
                "id-ID": [{
                        value: "id-ID-Standard-A",
                        label: "Amelia ({{ __('Female') }})"
                    },
                    {
                        value: "id-ID-Standard-B",
                        label: "Fajar ({{ __('Male') }})"
                    },
                    {
                        value: "id-ID-Standard-C",
                        label: "Galih ({{ __('Male') }})"
                    },
                    {
                        value: "id-ID-Standard-D",
                        label: "Kiara ({{ __('Female') }})"
                    },
                    {
                        value: "id-ID-Wavenet-A",
                        label: "Nadia ({{ __('Female') }})"
                    },
                    {
                        value: "id-ID-Wavenet-B",
                        label: "Reza ({{ __('Male') }})"
                    },
                    {
                        value: "id-ID-Wavenet-C",
                        label: "Satria ({{ __('Male') }})"
                    },
                    {
                        value: "id-ID-Wavenet-D",
                        label: "Vania ({{ __('Female') }})"
                    }
                ],
                "it-IT": [
                    //{value:"it-IT-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"it-IT-Neural2-C", label: "Neural2 - MALE"},
                    {
                        value: "it-IT-Standard-A",
                        label: "Chiara ({{ __('Female') }})"
                    },
                    {
                        value: "it-IT-Standard-B",
                        label: "Elisa ({{ __('Female') }})"
                    },
                    {
                        value: "it-IT-Standard-C",
                        label: "Matteo ({{ __('Male') }})"
                    },
                    {
                        value: "it-IT-Standard-D",
                        label: "Riccardo ({{ __('Male') }})"
                    },
                    {
                        value: "it-IT-Wavenet-A",
                        label: "Valentina ({{ __('Female') }})"
                    },
                    {
                        value: "it-IT-Wavenet-B",
                        label: "Vittoria ({{ __('Female') }})"
                    },
                    {
                        value: "it-IT-Wavenet-C",
                        label: "Andrea ({{ __('Male') }})"
                    },
                    {
                        value: "it-IT-Wavenet-D",
                        label: "Luca ({{ __('Male') }})"
                    }
                ],
                "ja-JP": [
                    //{value:"ja-JP-Neural2-B", label: "Neural2 - FEMALE"},
                    //{value:"ja-JP-Neural2-C", label: "Neural2 - MALE"},
                    //{value:"ja-JP-Neural2-D", label: "Neural2 - MALE"},
                    {
                        value: "ja-JP-Standard-A",
                        label: "Akane ({{ __('Female') }})"
                    },
                    {
                        value: "ja-JP-Standard-B",
                        label: "Emi ({{ __('Female') }})"
                    },
                    {
                        value: "ja-JP-Standard-C",
                        label: "Daisuke ({{ __('Male') }})"
                    },
                    {
                        value: "ja-JP-Standard-D",
                        label: "Kento ({{ __('Male') }})"
                    },
                    {
                        value: "ja-JP-Wavenet-A",
                        label: "Haruka ({{ __('Female') }})"
                    },
                    {
                        value: "ja-JP-Wavenet-B",
                        label: "Rin ({{ __('Female') }})"
                    },
                    {
                        value: "ja-JP-Wavenet-C",
                        label: "Shun ({{ __('Male') }})"
                    },
                    {
                        value: "ja-JP-Wavenet-D",
                        label: "Yuta ({{ __('Male') }})"
                    }
                ],
                "kn-IN": [{
                        value: "kn-IN-Standard-A",
                        label: "Dhanya ({{ __('Female') }})"
                    },
                    {
                        value: "kn-IN-Standard-B",
                        label: "Keerthi ({{ __('Male') }})"
                    },
                    {
                        value: "kn-IN-Wavenet-A",
                        label: "Meena ({{ __('Female') }})"
                    },
                    {
                        value: "kn-IN-Wavenet-B",
                        label: "Nandini ({{ __('Male') }})"
                    }
                ],
                "ko-KR": [
                    //{value:"ko-KR-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"ko-KR-Neural2-B", label: "Neural2 - FEMALE"},
                    //{value:"ko-KR-Neural2-C", label: "Neural2 - MALE"},
                    {
                        value: "ko-KR-Standard-A",
                        label: "So-young ({{ __('Female') }})"
                    },
                    {
                        value: "ko-KR-Standard-B",
                        label: "Se-yeon ({{ __('Female') }})"
                    },
                    {
                        value: "ko-KR-Standard-C",
                        label: "Min-soo ({{ __('Male') }})"
                    },
                    {
                        value: "ko-KR-Standard-D",
                        label: "Seung-woo ({{ __('Male') }})"
                    },
                    {
                        value: "ko-KR-Wavenet-A",
                        label: "Ji-soo ({{ __('Female') }})"
                    },
                    {
                        value: "ko-KR-Wavenet-B",
                        label: "Yoon-a ({{ __('Female') }})"
                    },
                    {
                        value: "ko-KR-Wavenet-C",
                        label: "Tae-hyun ({{ __('Male') }})"
                    },
                    {
                        value: "ko-KR-Wavenet-D",
                        label: "Jun-ho ({{ __('Male') }})"
                    }
                ],
                "lv-LV": [{
                    value: "lv-LV-Standard-A",
                    label: "Raivis ({{ __('Male') }})"
                }],
                "lv-LT": [{
                    value: "lv-LT-Standard-A",
                    label: "Raivis ({{ __('Male') }})"
                }],
                "ms-MY": [{
                        value: "ms-MY-Standard-A",
                        label: "Amira ({{ __('Female') }})"
                    },
                    {
                        value: "ms-MY-Standard-B",
                        label: "Danial ({{ __('Male') }})"
                    },
                    {
                        value: "ms-MY-Standard-C",
                        label: "Eira ({{ __('Female') }})"
                    },
                    {
                        value: "ms-MY-Standard-D",
                        label: "Farhan ({{ __('Male') }})"
                    },
                    {
                        value: "ms-MY-Wavenet-A",
                        label: "Hana ({{ __('Female') }})"
                    },
                    {
                        value: "ms-MY-Wavenet-B",
                        label: "Irfan ({{ __('Male') }})"
                    },
                    {
                        value: "ms-MY-Wavenet-C",
                        label: "Janna ({{ __('Female') }})"
                    },
                    {
                        value: "ms-MY-Wavenet-D",
                        label: "Khairul ({{ __('Male') }})"
                    }
                ],
                "ml-IN": [{
                        value: "ml-IN-Standard-A",
                        label: "Aishwarya ({{ __('Female') }})"
                    },
                    {
                        value: "ml-IN-Standard-B",
                        label: "Dhruv ({{ __('Male') }})"
                    },
                    {
                        value: "ml-IN-Wavenet-A",
                        label: "Deepthi ({{ __('Female') }})"
                    },
                    {
                        value: "ml-IN-Wavenet-B",
                        label: "Gautam ({{ __('Male') }})"
                    },
                    {
                        value: "ml-IN-Wavenet-C",
                        label: "Isha ({{ __('Female') }})"
                    },
                    {
                        value: "ml-IN-Wavenet-D",
                        label: "Kabir ({{ __('Male') }})"
                    }
                ],
                "cmn-CN": [{
                        value: "cmn-CN-Standard-A",
                        label: "Xiaomei ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-CN-Standard-B",
                        label: "Lijun ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-CN-Standard-C",
                        label: "Minghao ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-CN-Standard-D",
                        label: "Yingying ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-CN-Wavenet-A",
                        label: "Shanshan ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-CN-Wavenet-B",
                        label: "Chenchen ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-CN-Wavenet-C",
                        label: "Jiahao ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-CN-Wavenet-D",
                        label: "Yueyu ({{ __('Female') }})"
                    }
                ],
                "cmn-TW": [{
                        value: "cmn-TW-Standard-A",
                        label: "Jingwen ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-TW-Standard-B",
                        label: "Jinghao ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-TW-Standard-C",
                        label: "Tingting ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-TW-Wavenet-A",
                        label: "Yunyun ({{ __('Female') }})"
                    },
                    {
                        value: "cmn-TW-Wavenet-B",
                        label: "Zhenghao ({{ __('Male') }})"
                    },
                    {
                        value: "cmn-TW-Wavenet-C",
                        label: "Yuehan ({{ __('Female') }})"
                    }
                ],
                "mr-IN": [{
                        value: "mr-IN-Standard-A",
                        label: "Anjali ({{ __('Female') }})"
                    },
                    {
                        value: "mr-IN-Standard-B",
                        label: "Aditya ({{ __('Male') }})"
                    },
                    {
                        value: "mr-IN-Standard-C",
                        label: "Dipti ({{ __('Female') }})"
                    },
                    {
                        value: "mr-IN-Wavenet-A",
                        label: "Gauri ({{ __('Female') }})"
                    },
                    {
                        value: "mr-IN-Wavenet-B",
                        label: "Harsh ({{ __('Male') }})"
                    },
                    {
                        value: "mr-IN-Wavenet-C",
                        label: "Ishita ({{ __('Female') }})"
                    }
                ],
                "nb-NO": [{
                        value: "nb-NO-Standard-A",
                        label: "Ingrid ({{ __('Female') }})"
                    },
                    {
                        value: "nb-NO-Standard-B",
                        label: "Jonas ({{ __('Male') }})"
                    },
                    {
                        value: "nb-NO-Standard-C",
                        label: "Marit ({{ __('Female') }})"
                    },
                    {
                        value: "nb-NO-Standard-D",
                        label: "Olav ({{ __('Male') }})"
                    },
                    {
                        value: "nb-NO-Standard-E",
                        label: "Silje ({{ __('Female') }})"
                    },
                    {
                        value: "nb-NO-Wavenet-A",
                        label: "Astrid ({{ __('Female') }})"
                    },
                    {
                        value: "nb-NO-Wavenet-B",
                        label: "Eirik ({{ __('Male') }})"
                    },
                    {
                        value: "nb-NO-Wavenet-C",
                        label: "Inger ({{ __('Female') }})"
                    },
                    {
                        value: "nb-NO-Wavenet-D",
                        label: "Kristian ({{ __('Male') }})"
                    },
                    {
                        value: "nb-NO-Wavenet-E",
                        label: "Trine ({{ __('Female') }})"
                    }
                ],
                "pl-PL": [{
                        value: "pl-PL-Standard-A",
                        label: "Agata ({{ __('Female') }})"
                    },
                    {
                        value: "pl-PL-Standard-B",
                        label: "Bartosz ({{ __('Male') }})"
                    },
                    {
                        value: "pl-PL-Standard-C",
                        label: "Kamil ({{ __('Male') }})"
                    },
                    {
                        value: "pl-PL-Standard-D",
                        label: "Julia ({{ __('Female') }})"
                    },
                    {
                        value: "pl-PL-Standard-E",
                        label: "Magdalena ({{ __('Female') }})"
                    },
                    {
                        value: "pl-PL-Wavenet-A",
                        label: "Natalia ({{ __('Female') }})"
                    },
                    {
                        value: "pl-PL-Wavenet-B",
                        label: "Paweł ({{ __('Male') }})"
                    },
                    {
                        value: "pl-PL-Wavenet-C",
                        label: "Tomasz ({{ __('Male') }})"
                    },
                    {
                        value: "pl-PL-Wavenet-D",
                        label: "Zofia ({{ __('Female') }})"
                    },
                    {
                        value: "pl-PL-Wavenet-E",
                        label: "Wiktoria ({{ __('Female') }})"
                    }
                ],
                "pt-BR": [
                    //{value:"pt-BR-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"pt-BR-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"pt-BR-Neural2-C", label: "Neural2 - FEMALE"},
                    {
                        value: "pt-BR-Standard-A",
                        label: "Ana ({{ __('Female') }})"
                    },
                    {
                        value: "pt-BR-Standard-B",
                        label: "Carlos ({{ __('Male') }})"
                    },
                    {
                        value: "pt-BR-Standard-C",
                        label: "Maria ({{ __('Female') }})"
                    },
                    {
                        value: "pt-BR-Wavenet-A",
                        label: "Julia ({{ __('Female') }})"
                    },
                    {
                        value: "pt-BR-Wavenet-B",
                        label: "João ({{ __('Male') }})"
                    },
                    {
                        value: "pt-BR-Wavenet-C",
                        label: "Fernanda ({{ __('Female') }})"
                    }
                ],
                "pt-PT": [{
                        value: "pt-PT-Standard-A",
                        label: "Maria ({{ __('Female') }})"
                    },
                    {
                        value: "pt-PT-Standard-B",
                        label: "José ({{ __('Male') }})"
                    },
                    {
                        value: "pt-PT-Standard-C",
                        label: "Luís ({{ __('Male') }})"
                    },
                    {
                        value: "pt-PT-Standard-D",
                        label: "Ana ({{ __('Female') }})"
                    },
                    {
                        value: "pt-PT-Wavenet-A",
                        label: "Catarina ({{ __('Female') }})"
                    },
                    {
                        value: "pt-PT-Wavenet-B",
                        label: "Miguel ({{ __('Male') }})"
                    },
                    {
                        value: "pt-PT-Wavenet-C",
                        label: "João ({{ __('Male') }})"
                    },
                    {
                        value: "pt-PT-Wavenet-D",
                        label: "Marta ({{ __('Female') }})"
                    }
                ],
                "pa-IN": [{
                        value: "pa-IN-Standard-A",
                        label: "Harpreet ({{ __('Female') }})"
                    },
                    {
                        value: "pa-IN-Standard-B",
                        label: "Gurpreet ({{ __('Male') }})"
                    },
                    {
                        value: "pa-IN-Standard-C",
                        label: "Jasmine ({{ __('Female') }})"
                    },
                    {
                        value: "pa-IN-Standard-D",
                        label: "Rahul ({{ __('Male') }})"
                    },
                    {
                        value: "pa-IN-Wavenet-A",
                        label: "Simran ({{ __('Female') }})"
                    },
                    {
                        value: "pa-IN-Wavenet-B",
                        label: "Amardeep ({{ __('Male') }})"
                    },
                    {
                        value: "pa-IN-Wavenet-C",
                        label: "Kiran ({{ __('Female') }})"
                    },
                    {
                        value: "pa-IN-Wavenet-D",
                        label: "Raj ({{ __('Male') }})"
                    }
                ],
                "ro-RO": [{
                        value: "ro-RO-Standard-A",
                        label: "Maria ({{ __('Female') }})"
                    },
                    {
                        value: "ro-RO-Wavenet-A",
                        label: "Ioana ({{ __('Female') }})"
                    }
                ],
                "ru-RU": [{
                        value: "ru-RU-Standard-A",
                        label: "Anastasia"
                    },
                    {
                        value: "ru-RU-Standard-B",
                        label: "Alexander"
                    },
                    {
                        value: "ru-RU-Standard-C",
                        label: "Elizabeth"
                    },
                    {
                        value: "ru-RU-Standard-D",
                        label: "Michael"
                    },
                    {
                        value: "ru-RU-Standard-E",
                        label: "Victoria"
                    },
                    {
                        value: "ru-RU-Wavenet-A",
                        label: "Daria"
                    },
                    {
                        value: "ru-RU-Wavenet-B",
                        label: "Dmitry"
                    },
                    {
                        value: "ru-RU-Wavenet-C",
                        label: "Kristina"
                    },
                    {
                        value: "ru-RU-Wavenet-D",
                        label: "Ivan"
                    },
                    {
                        value: "ru-RU-Wavenet-E",
                        label: "Sophia"
                    }
                ],
                "sr-RS": [{
                    value: "sr-RS-Standard-A",
                    label: "Ana"
                }],
                "sk-SK": [{
                        value: "sk-SK-Standard-A",
                        label: "Mária ({{ __('Female') }})"
                    },
                    {
                        value: "sk-SK-Wavenet-A",
                        label: "Zuzana ({{ __('Female') }})"
                    }
                ],
                "es-ES": [
                    //{value:"es-ES-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"es-ES-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"es-ES-Neural2-C", label: "Neural2 - FEMALE"},
                    //{value:"es-ES-Neural2-D", label: "Neural2 - FEMALE"},
                    //{value:"es-ES-Neural2-E", label: "Neural2 - FEMALE"},
                    //{value:"es-ES-Neural2-F", label: "Neural2 - MALE"},
                    //{value: "es-ES-Polyglot-1", label: "Juan ({{ __('Male') }})"},
                    {
                        value: "es-ES-Standard-A",
                        label: "María ({{ __('Female') }})"
                    },
                    {
                        value: "es-ES-Standard-B",
                        label: "José ({{ __('Male') }})"
                    },
                    {
                        value: "es-ES-Standard-C",
                        label: "Ana ({{ __('Female') }})"
                    },
                    {
                        value: "es-ES-Standard-D",
                        label: "Isabel ({{ __('Female') }})"
                    },
                    {
                        value: "es-ES-Wavenet-B",
                        label: "Pedro ({{ __('Male') }})"
                    },
                    {
                        value: "es-ES-Wavenet-C",
                        label: "Laura ({{ __('Female') }})"
                    },
                    {
                        value: "es-ES-Wavenet-D",
                        label: "Julia ({{ __('Female') }})"
                    }

                ],
                "es-US": [
                    //{value:"es-US-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"es-US-Neural2-B", label: "Neural2 - MALE"},
                    //{value:"es-US-Neural2-C", label: "Neural2 - MALE"},
                    {
                        value: "es-US-News-D",
                        label: "Diego ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-News-E",
                        label: "Eduardo ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-News-F",
                        label: "Fátima ({{ __('Female') }})"
                    },
                    {
                        value: "es-US-News-G",
                        label: "Gabriela ({{ __('Female') }})"
                    },
                    //{value:"es-US-Polyglot-1", label: "Juan ({{ __('Male') }})"},
                    {
                        value: "es-US-Standard-A",
                        label: "Ana ({{ __('Female') }})"
                    },
                    {
                        value: "es-US-Standard-B",
                        label: "José ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-Standard-C",
                        label: "Carlos ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-Studio-B",
                        label: "Miguel ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-Wavenet-A",
                        label: "Laura ({{ __('Female') }})"
                    },
                    {
                        value: "es-US-Wavenet-B",
                        label: "Pedro ({{ __('Male') }})"
                    },
                    {
                        value: "es-US-Wavenet-C",
                        label: "Pablo ({{ __('Male') }})"
                    }
                ],
                "sv-SE": [{
                        value: "sv-SE-Standard-A",
                        label: "Ebba ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Standard-B",
                        label: "Saga ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Standard-C",
                        label: "Linnea ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Standard-D",
                        label: "Erik ({{ __('Male') }})"
                    },
                    {
                        value: "sv-SE-Standard-E",
                        label: "Anton ({{ __('Male') }})"
                    },
                    {
                        value: "sv-SE-Wavenet-A",
                        label: "Astrid ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Wavenet-B",
                        label: "Elin ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Wavenet-C",
                        label: "Oskar ({{ __('Male') }})"
                    },
                    {
                        value: "sv-SE-Wavenet-D",
                        label: "Hanna ({{ __('Female') }})"
                    },
                    {
                        value: "sv-SE-Wavenet-E",
                        label: "Felix ({{ __('Male') }})"
                    }
                ],
                "ta-IN": [{
                        value: "ta-IN-Standard-A",
                        label: "Anjali ({{ __('Female') }})"
                    },
                    {
                        value: "ta-IN-Standard-B",
                        label: "Karthik ({{ __('Male') }})"
                    },
                    {
                        value: "ta-IN-Standard-C",
                        label: "Priya ({{ __('Female') }})"
                    },
                    {
                        value: "ta-IN-Standard-D",
                        label: "Ravi ({{ __('Male') }})"
                    },
                    {
                        value: "ta-IN-Wavenet-A",
                        label: "Lakshmi ({{ __('Female') }})"
                    },
                    {
                        value: "ta-IN-Wavenet-B",
                        label: "Suresh ({{ __('Male') }})"
                    },
                    {
                        value: "ta-IN-Wavenet-C",
                        label: "Uma ({{ __('Female') }})"
                    },
                    {
                        value: "ta-IN-Wavenet-D",
                        label: "Venkatesh ({{ __('Male') }})"
                    }
                ],
                "te-IN": [{
                        value: "-IN-Standard-A",
                        label: "Anjali - ({{ __('Female') }})"
                    },
                    {
                        value: "-IN-Standard-B",
                        label: "Karthik - ({{ __('Male') }})"
                    }
                ],
                "th-TH": [
                    //{value:"th-TH-Neural2-C", label: "Neural2 - FEMALE"},
                    {
                        value: "th-TH-Standard-A",
                        label: "Ariya - ({{ __('Female') }})"
                    }
                ],
                "tr-TR": [{
                        value: "tr-TR-Standard-A",
                        label: "Ayşe ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Standard-B",
                        label: "Berk ({{ __('Male') }})"
                    },
                    {
                        value: "tr-TR-Standard-C",
                        label: "Cansu ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Standard-D",
                        label: "Deniz ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Standard-E",
                        label: "Emre ({{ __('Male') }})"
                    },
                    {
                        value: "tr-TR-Wavenet-A",
                        label: "Gül ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Wavenet-B",
                        label: "Mert ({{ __('Male') }})"
                    },
                    {
                        value: "tr-TR-Wavenet-C",
                        label: "Nilay ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Wavenet-D",
                        label: "Selin ({{ __('Female') }})"
                    },
                    {
                        value: "tr-TR-Wavenet-E",
                        label: "Tolga ({{ __('Male') }})"
                    }
                ],
                "uk-UA": [{
                        value: "uk-UA-Standard-A",
                        label: "Anya - ({{ __('Female') }})"
                    },
                    {
                        value: "uk-UA-Wavenet-A",
                        label: "Dasha - ({{ __('Female') }})"
                    }
                ],
                "vi-VN": [
                    //{value:"vi-VN-Neural2-A", label: "Neural2 - FEMALE"},
                    //{value:"vi-VN-Neural2-D", label: "Neural2 - MALE"},
                    {
                        value: "vi-VN-Standard-A",
                        label: "Mai ({{ __('Female') }})"
                    },
                    {
                        value: "vi-VN-Standard-B",
                        label: "Nam ({{ __('Male') }})"
                    },
                    {
                        value: "vi-VN-Standard-C",
                        label: "Hoa ({{ __('Female') }})"
                    },
                    {
                        value: "vi-VN-Standard-D",
                        label: "Huy ({{ __('Male') }})"
                    },
                    {
                        value: "vi-VN-Wavenet-A",
                        label: "Lan ({{ __('Female') }})"
                    },
                    {
                        value: "vi-VN-Wavenet-B",
                        label: "Son ({{ __('Male') }})"
                    },
                    {
                        value: "vi-VN-Wavenet-C",
                        label: "Thao ({{ __('Female') }})"
                    },
                    {
                        value: "vi-VN-Wavenet-D",
                        label: "Tuan ({{ __('Male') }})"
                    }
                ]
            }

            function country2flag(languageCode) {
                var countryCode = languageCode.match(/[A-Z]{2}$/i);
                if (!countryCode) {
                    return null;
                }
                var flag = countryCode[0].replace(/./g, function(letter) {
                    return String.fromCodePoint(letter.charCodeAt(0) % 32 + 0x1F1E5);
                });
                return flag;
            }

            $(document).ready(function() {
                "use strict";

                populateVoiceSelect();
                populatePaceSelect();

                $("#languages").on("change", function() {
                    populateVoiceSelect();
                    populatePaceSelect();
                });

                $("#voice").on("change", function() {
                    populatePaceSelect();
                });

                function populateVoiceSelect() {
                    const selectedLanguage = $("#languages").val();
                    const selectedLanguage_ = $('#languages option:selected').attr('language');
                    const selectedOptions = voicesData[selectedLanguage];
                    const voiceSelect = $("#voice");

                    console.log(selectedLanguage_);

                    voiceSelect.empty();

                    if (selectedOptions) {

                        if (@json($settings_two->feature_tts_google) == true) {
                            selectedOptions.forEach(option => {
                                $("<option></option>")
                                    .val(option.value)
                                    .text(option.label)
                                    .attr('name', option.label)
                                    .attr('platform', "google")
                                    .appendTo(voiceSelect);
                            });
                        }

                        if (@json($settings_two->feature_tts_openai) == true) {
                            if (allowedOpenAIList.includes(selectedLanguage)) {
                                openaiVoiceData.forEach(option => {
                                    $("<option></option>")
                                        .val(option.value)
                                        .text(option.label + (" (OpenAI)"))
                                        .attr('platform', "openai")
                                        .attr('name', option.label + (" (OpenAI)"))
                                        .appendTo(voiceSelect);
                                });
                            }
                        }

                        if (@json($settings_two->feature_tts_elevenlabs) == true) {
                            if (allowedElevenLabsList.includes(selectedLanguage)) {
                                elevenLabsVoices.forEach(option => {
                                    $("<option></option>")
                                        .val(option.voice_id)
                                        .text(option.name + (" (ElevenLabs)"))
                                        .attr('name', option.name + (" (ElevenLabs)"))
                                        .attr('platform', "elevenlabs")
                                        .appendTo(voiceSelect);
                                });
                            }
                        }
                    }
                }

                function populatePaceSelect() {
                    const selectedPlatform = $('#voice option:selected').attr('platform');
                    const selectedOptions_google = [{
                        value: "x-slow",
                        label: "Very Slow"
                    }, {
                        value: "slow",
                        label: "Slow"
                    }, {
                        value: "medium",
                        label: "Medium"
                    }, {
                        value: "fast",
                        label: "Fast"
                    }, {
                        value: "x-fast",
                        label: "Very Fast"
                    }, ];

                    const selectedOptions_openai = [{
                        value: "tts-1",
                        label: "Normal"
                    }, {
                        value: "tts-1-hd",
                        label: "Neural"
                    }];

                    const selectedOptions_elevenlabs = [{
                        value: "100",
                        label: "Slow"
                    }, {
                        value: "50",
                        label: "Medium"
                    }, {
                        value: "0",
                        label: "Fast"
                    }, ];

                    const paceSelect = $("#pace");

                    paceSelect.empty();
                    console.log(selectedPlatform);

                    if (selectedPlatform == 'google') {
                        $("#break").parent().removeClass('hidden');
                        selectedOptions_google.forEach(option => {
                            $("<option></option>")
                                .val(option.value)
                                .text(option.label)
                                .attr('platform', "google")
                                .appendTo(paceSelect);
                        });
                    } else if (selectedPlatform == "openai") {
                        $("#break").parent().addClass('hidden');
                        selectedOptions_openai.forEach(option => {
                            $("<option></option>")
                                .val(option.value)
                                .text(option.label)
                                .attr('platform', "openai")
                                .appendTo(paceSelect);
                        });
                    } else if (selectedPlatform == "elevenlabs") {
                        $("#break").parent().addClass('hidden');
                        selectedOptions_elevenlabs.forEach(option => {
                            $("<option></option>")
                                .val(option.value)
                                .text(option.label)
                                .attr('platform', "elevenlabs")
                                .appendTo(paceSelect);
                        });
                    }
                }

                $('.add-new-text').click(function() {
                    var selectedVoice = $('#voice option:selected').val();
                    var selectedVoiceText = $('#voice option:selected').text();
                    var selectedLang = $('#languages option:selected').val();
                    var selectedLanguage = $('#languages option:selected').attr('language');
                    var selectedPace = $('#pace option:selected').val();
                    var selectedBreak = $('#break option:selected').val();
                    var selectedVoiceOpenAI = $('#voice_openai option:selected').val();
                    var selectedModelOpenAI = $('#model_openai option:selected').val();
                    var selectedPlatform = $('#voice option:selected').attr('platform');

                    const speechTemplate = document.querySelector('#speech-template').content.cloneNode(true);
                    const speechEl = speechTemplate.querySelector('.speech');
                    const speechTemplateLang = speechEl.querySelector('.data-lang');
                    const speechTemplateVoice = speechEl.querySelector('.data-voice');
                    const speechTemplatePreview = speechEl.querySelector('.preview-speech');
                    const speechTemplateDelete = speechEl.querySelector('.delete-speech');
                    const speechTemplateTextarea = speechEl.querySelector('textarea');

                    let speechContent = '';

                    let tts = @json($settings_two->tts);

                    speechTemplateLang.innerText = country2flag(selectedLang);
                    speechTemplateVoice.innerText = selectedVoiceText;
                    selectedPlatform === 'elevenlabs' && speechTemplateTextarea.setAttribute('name',
                        selectedVoiceText);
                    speechTemplateTextarea.setAttribute('data-platform', selectedPlatform);
                    speechTemplateTextarea.setAttribute('data-voice', selectedVoice);
                    speechTemplateTextarea.setAttribute('data-language', selectedLanguage);
                    speechTemplateTextarea.setAttribute('data-lang', selectedLang);
                    speechTemplateTextarea.setAttribute('data-pace', selectedPace);
                    speechTemplateTextarea.setAttribute('data-break', selectedBreak);
                    speechTemplateTextarea.setAttribute('data-modelopenai', selectedModelOpenAI);
                    speechTemplateTextarea.setAttribute('data-voiceopenai', selectedVoiceOpenAI);

                    if (selectedPlatform !== 'elevenlabs' && selectedPlatform !== 'openai') {
                        const sayAsTemplate = document.querySelector('#speech-say-as-template').content
                            .cloneNode(true);
                        const sayAsEl = sayAsTemplate.querySelector('.say-as');
                        speechEl.querySelector('.say-as-container').appendChild(sayAsEl);
                    }

                    $('.speeches').append(speechEl);
                });

                $(document).on('click', '.delete-speech', function() {
                    $(this).closest('.speech').remove();
                });

                $(document).on('change', '.say-as', function() {
                    var selectedValue = $(this).val();
                    if (selectedValue === 'currency') {
                        selectedValue = "<say-as interpret-as='currency' language='en-US'>$42.01</say-as>";
                    } else if (selectedValue === 'telephone') {
                        selectedValue =
                            "<say-as interpret-as='telephone' google:style='zero-as-zero'>1800-202-1212</say-as>";
                    } else if (selectedValue === 'verbatim') {
                        selectedValue = "<say-as interpret-as='verbatim'>abcdefg</say-as>";
                    } else if (selectedValue === 'date') {
                        selectedValue =
                            "<say-as interpret-as='date' format='yyyymmdd' detail='1'>1960-09-10</say-as>";
                    } else if (selectedValue === 'characters') {
                        selectedValue = "<say-as interpret-as='characters'>can</say-as>";
                    } else if (selectedValue === 'cardinal') {
                        selectedValue = "<say-as interpret-as='cardinal'>12345</say-as>";
                    } else if (selectedValue === 'ordinal') {
                        selectedValue = "<say-as interpret-as='ordinal'>1</say-as>";
                    } else if (selectedValue === 'fraction') {
                        selectedValue = "<say-as interpret-as='fraction'>5+1/2</say-as>";
                    } else if (selectedValue === 'bleep') {
                        selectedValue = "<say-as interpret-as='expletive'>censor this</say-as>";
                    } else if (selectedValue === 'unit') {
                        selectedValue = "<say-as interpret-as='unit'>10 foot</say-as>";
                    } else if (selectedValue === 'time') {
                        selectedValue = "<say-as interpret-as='time' format='hms12'>2:30pm</say-as>";
                    }
                    var textarea = $(this).closest('.speech').find('textarea');
                    var existingValue = textarea.val();
                    textarea.val(existingValue + selectedValue);
                    $(this).val('0');
                });

                // Preview
                $(document).on('click', '.preview-speech', function() {

                    const previewButton = $(this);
                    var speechElement = $(this).closest('.speech');
                    var textareaValue = speechElement.find('textarea').val();

                    if (textareaValue) {
                        previewButton.addClass('loading');

                        var formData = new FormData();
                        var speechData = [];

                        var data = {
                            voice: speechElement.find('textarea').attr('data-voice'),
                            lang: speechElement.find('textarea').attr('data-lang'),
                            language: speechElement.find('textarea').attr('data-language'),
                            pace: speechElement.find('textarea').attr('data-pace'),
                            platform: speechElement.find('textarea').attr('data-platform'),
                            break: speechElement.find('textarea').attr('data-break'),
                            voice_openai: speechElement.find('textarea').attr('data-voiceopenai'),
                            model_openai: speechElement.find('textarea').attr('data-modelopenai'),
                            content: speechElement.find('textarea').val(),
                            name: speechElement.find('textarea').attr('name'),
                        };
                        speechData.push(data);

                        var jsonData = JSON.stringify(speechData);
                        formData.append('speeches', jsonData);
                        formData.append('preview', true);


                        $.ajax({
                            type: "post",
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            url: "/dashboard/user/openai/generate-speech",
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(data) {
                                toastr.success("{{ __('Generated') }}");
                                document.getElementById("generate_speech_button").disabled = false;
                                document.getElementById("generate_speech_button").innerHTML =
                                    "{{ __('Generate') }}";
                                speechElement.find('.data-preview').html(data.output);
                                generateWaveForm(speechElement[0].querySelector('.data-audio'));
                                previewButton.removeClass('loading');
                            },
                            error: function(data) {
                                var err = data.responseJSON.errors;
                                $.each(err, function(index, value) {
                                    toastr.error(value);
                                });
                                document.getElementById("generate_speech_button").disabled = false;
                                previewButton.removeClass('loading');
                            },
                        });
                    } else {
                        toastr.error("{{ __('Input is empty!') }}");
                    }

                });

            });
        </script>
    @endif

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

    <script>
        var stablediffusionType = "text-to-image";

        var resultVideoId = "";
        var intervalId = -1;
        var sourceImgUrl = "";
        var checking = false;

        function hideLoadingIndicators() {
            document.getElementById("openai_generator_button").disabled = false;
            document.getElementById("openai_generator_button").innerHTML = "Regenerate";
            Alpine.store('appLoadingIndicator').hide();
            document.querySelector('#workbook_regenerate')?.classList?.remove('hidden');
        }

        function checkVideoDone() {
            'use strict';
            if (checking) return;
            checking = true;

            let formData = new FormData();
            formData.append('id', resultVideoId);
            formData.append('url', sourceImgUrl);
            formData.append('size', `${postImageWidth}x${postImageHeight}`);

            $.ajax({
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                url: "/dashboard/user/openai/check/videoprogress",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    checking = false;
                    console.log(res);
                    if (res.status == 'finished') {
                        clearInterval(intervalId);
                        intervalId = -1;
                        const videoContainer = document.querySelector('.video-results');
                        const videoResultTemplate = document.querySelector('#video_result').content.cloneNode(
                            true);
                        const delete_url =
                            `${server}/dashboard/user/openai/documents/delete/image/${res.video.slug}`;

                        videoResultTemplate.querySelector('.video-result').classList.remove('lqd-is-loading');
                        videoResultTemplate.querySelector('.video-result').setAttribute('data-id', res.video.id);
                        videoResultTemplate.querySelector('.lqd-video-result-video source').setAttribute('src',
                            res.video.output);
                        videoResultTemplate.querySelector('.lqd-video-result-view').setAttribute('data-payload',
                            JSON.stringify(res.video));

                        videoResultTemplate.querySelector('.lqd-video-result-download').setAttribute('href', res
                            .video.output);
                        videoResultTemplate.querySelector('.lqd-video-result-download').setAttribute('download', res
                            .video.slug);
                        videoResultTemplate.querySelector('.lqd-video-result-play').setAttribute('href', res
                            .video.output);

                        videoContainer.insertBefore(videoResultTemplate, videoContainer.firstChild);

                        hideLoadingIndicators();

                        refreshFsLightbox();
                    } else if (res.status == 'in-progress') {

                    }
                },
                error: function(data) {
                    checking = false;
                    clearInterval(intervalId);
                    document.getElementById("openai_generator_button").disabled = false;
                    document.getElementById("openai_generator_button").innerHTML = "Generate";
                    Alpine.store('appLoadingIndicator').hide();
                    document.querySelector('#workbook_regenerate')?.classList?.add('hidden');
                    if (data.responseJSON.errors) {
                        $.each(data.responseJSON.errors, function(index, value) {
                            toastr.error(value);
                        });
                    } else if (data.responseJSON.message) {
                        toastr.error(data.responseJSON.message);
                    }
                }
            });
        }

        function sendOpenaiGeneratorForm(ev) {

            ev?.preventDefault();
            ev?.stopPropagation();

            @if ($openai->type == 'video')
                if (resizedImage == undefined) {
                    toastr.warning('Please input image');
                    Alpine.store('appLoadingIndicator').hide();
                    return false;
                }
                if (!((imageWidth == 1024 && imageHeight == 576) || (imageWidth == 768 && imageHeight == 768) || (
                        imageWidth == 576 && imageHeight == 1024))) {
                    toastr.warning('Image size should be  1024x576, 576x1024, 768x768');
                    return false;
                }
                postImageWidth = imageWidth;
                postImageHeight = imageHeight;
            @endif

            document.getElementById("openai_generator_button").disabled = true;
            document.getElementById("openai_generator_button").innerHTML = magicai_localize.please_wait;
            Alpine.store('appLoadingIndicator').show();
            @if ($openai->type == 'image')
                var imageGenerator = document.querySelector('[data-generator-name][data-active=true]')?.getAttribute(
                    'data-generator-name');
            @endif
            var formData = new FormData();
            formData.append('post_type', '{{ $openai->slug }}');
            formData.append('openai_id', {{ $openai->id }});
            formData.append('custom_template', {{ $openai->custom_template }});
            @if ($openai->type == 'text')
                formData.append('maximum_length', $("#maximum_length").val());
                formData.append('number_of_results', $("#number_of_results").val());
                formData.append('creativity', $("#creativity").val());
                formData.append('tone_of_voice', $("#tone_of_voice").val());
                formData.append('language', $("#language").val());
            @endif
            @if ($openai->type == 'audio')
                formData.append('file', $('#file').prop('files')[0]);
            @endif

            @if ($openai->type == 'image')
                formData.append('image_generator', imageGenerator);

                if (imageGenerator == 'dall-e') {
                    formData.append('image_style', $("#image_style").val());
                    formData.append('image_lighting', $("#image_lighting").val());
                    formData.append('image_mood', $("#image_mood").val());
                    // formData.append('image_model', document.getElementById('image_model').value)
                    formData.delete('size');
                    //if(document.getElementById('image_model').value == 'dall-e-2'){
                    formData.append('image_number_of_images', $("#image_number_of_images").val());
                    formData.append('size', $("#size").val());
                    formData.append('quality', $("#image_quality").val());
                    // } else {
                    //     formData.append('image_number_of_images', $("#image_number_of_images_3").val());
                    //     formData.append('size', $("#size_3").val());
                    // }
                } else {
                    formData.append('type', stablediffusionType);
                    formData.append('negative_prompt', $("#negative_prompt").val());
                    formData.append('style_preset', $("#style_preset").val());
                    formData.append('image_mood', $("#image_mood_stable").val());
                    formData.append('sampler', $("#sampler").val());
                    formData.append('clip_guidance_preset', $("#clip_guidance_preset").val());
                    formData.append('image_resolution', $("#image_resolution").val());
                    formData.append('image_number_of_images', $("#image_number_of_images_stable").val());

                    switch (stablediffusionType) {
                        case 'text-to-image':
                            formData.append("stable_description", $("#txt2img_description").val());
                            break;
                        case 'image-to-image':
                            formData.append("stable_description", $("#img2img_description").val());
                            formData.append("image_src", resizedImage);
                            break;
                        case 'upscale':
                            formData.append("stable_description", "upscale");
                            formData.append("image_src", resizedImage);
                            break;
                        case 'multi-prompt':
                            $('.multi_prompts_description').each(function(idx, e) {
                                formData.append("stable_description[]", $(e).val())
                            })
                            break;
                    }
                }
            @endif

            @if ($openai->type == 'video')
                formData.append("image_src", resizedImage);
                formData.append('seed', $("#video_seed").val());
                formData.append('cfg_scale', $("#video_cfg_scale").val());
                formData.append('motion_bucket_id', $("#video_motion_bucket_id").val());
            @endif

            @foreach (json_decode($openai->questions) as $question)
                if ("{{ $question->name }}" != "size")
                    formData.append("{{ $question->name }}", $("#{{ $question->name }}").val());
            @endforeach

            // for (var pair of formData.entries()) {
            //     console.log(pair[0]+ ' : ' + pair[1]);
            // }

            $.ajax({
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                url: "/dashboard/user/openai/generate",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {

                    if (res.status !== 'success' && (res.message)) {
                        toastr.error(res.message);
                        hideLoadingIndicators();
                        return;
                    }

                    //show successful message
                    @if ($openai->type == 'image')
                        toastr.success(`Image Generated Successfully in ${res.image_storage}`);
                    @elseif ($openai->type == 'video')
                        resultVideoId = res.id;
                    @else
                        toastr.success("{{ __('Generated Successfully!') }}");
                    @endif

                    setTimeout(function() {
                        @if ($openai->type == 'image')

                            const images = res.images;
                            const currenturl = window.location.href;
                            const server = currenturl.split('/')[0];
                            const imageContainer = document.querySelector('.image-results');
                            const imageResultTemplate = document.querySelector('#image_result').content
                                .cloneNode(true);

                            images.forEach((image) => {
                                const delete_url =
                                    `${server}/dashboard/user/openai/documents/delete/image/${image.slug}`;

                                imageResultTemplate.querySelector('.image-result').setAttribute('data-id', image.id);
                                imageResultTemplate.querySelector('.image-result').classList.remove('lqd-is-loading');
                                imageResultTemplate.querySelector('.image-result').setAttribute(
                                    'data-generator', image.response == "SD" ? "sd" : "de");
                                imageResultTemplate.querySelector('.lqd-image-result-img')
                                    .setAttribute('src', image.output);
                                imageResultTemplate.querySelector('.lqd-image-result-type')
                                    .innerHTML = image.response == "SD" ? "SD" : "DE";
                                imageResultTemplate.querySelector('.lqd-image-result-view')
                                    .setAttribute('data-payload', JSON.stringify(image));

                                imageResultTemplate.querySelector('.lqd-image-result-delete')
                                    .setAttribute('href', delete_url);
                                imageResultTemplate.querySelector('.lqd-image-result-download')
                                    .setAttribute('href', image.output);
                                imageResultTemplate.querySelector('.lqd-image-result-download')
                                    .setAttribute('download', image.slug);
                                imageResultTemplate.querySelector('.lqd-image-result-title')
                                    .setAttribute('title', image.input);
                                imageResultTemplate.querySelector('.lqd-image-result-title')
                                    .innerText = image.input;
                                imageContainer.insertBefore(imageResultTemplate, imageContainer
                                    .firstChild);

                            })
                            @if ($openai->type != 'image')
                                refreshFsLightbox();
                            @endif
                        @elseif ($openai->type == 'video')
                            sourceImgUrl = res.sourceUrl;
                            console.log('Create interval');
                            intervalId = setInterval(checkVideoDone, 10000);
                        @elseif ($openai->type == 'audio')
                            $("#generator_sidebar_table").html(res?.data?.html2 || res.html2);
                        @else
                            if ($("#code-output").length) {
                                $("#workbook_textarea").html(res.data.html2);
                                window.codeRaw = $("#code-output").text();
                                $("#code-output").addClass(
                                    `language-${$('#code_lang').val() || 'javascript'}`);
                                Prism.highlightElement($("#code-output")[0]);
                            } else {
                                tinymce.activeEditor.destroy();
                                $("#generator_sidebar_table").html(res.data.html2);
                                getResult();
                            }
                        @endif
                        @if ($openai->type != 'video')
                            hideLoadingIndicators();
                        @endif
                        @if ($openai->type != 'image')
                            refreshFsLightbox();
                        @endif
                    }, 750);
                },
                error: function(data) {
                    console.log(data);
                    document.getElementById("openai_generator_button").disabled = false;
                    document.getElementById("openai_generator_button").innerHTML = "Genarate";
                    Alpine.store('appLoadingIndicator').hide();
                    document.querySelector('#workbook_regenerate')?.classList?.add('hidden');
                    if (data.responseJSON.errors) {
                        $.each(data.responseJSON.errors, function(index, value) {
                            toastr.error(value);
                        });
                    } else if (data.responseJSON.message) {
                        toastr.error(data.responseJSON.message);
                    }
                }
            });
            return false;
        }
    </script>

@endpush
