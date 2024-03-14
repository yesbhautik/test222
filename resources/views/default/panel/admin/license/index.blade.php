@extends('panel.layout.app')
@section('title', __('Update License'))
@section('titlebar_actions', '')
@section('content')
    <!-- Page body    -->
    <div class="py-10">
        <div class="container-xl">
            @if ($settings_two->liquid_license_type == 'Regular License')
                <x-alert
                    class="mt-2"
                    variant="warn"
                >
                    <p>
                        {{ __('Your are using Regular License. Please upgrade to Extended License.') }}
                        <br>
                        <a
                            class="unerline"
                            href="https://magicaidocs.liquid-themes.com/upgrading-to-extended-license/"
                            target="_blank"
                        >
                            {{ __('How can i upgrade?') }}
                        </a>
                    </p>
                </x-alert>
            @elseif($settings_two->liquid_license_type == 'Extended License')
                <div class="!mt-2 rounded-xl bg-green-100 !p-3 text-center text-green-600 dark:bg-green-600/20 dark:text-green-200">
                    {{ __('Your are using Extended License.') }}
                </div>
            @endif
        </div>
        @include('vendor.installer.magicai_c4st_Act')
    </div>

@endsection
