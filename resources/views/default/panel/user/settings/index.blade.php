@extends('panel.layout.app')
@section('title', __('My Account'))

@section('content')
    <div class="page-header">
        <div class="container-xl">
            <div class="row g-2 items-center">
                <div class="col">
                    <a
                        class="page-pretitle flex items-center"
                        href="{{ LaravelLocalization::localizeUrl(route('dashboard.index')) }}"
                    >
                        <svg
                            class="!me-2 rtl:-scale-x-100"
                            width="8"
                            height="10"
                            viewBox="0 0 6 10"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M4.45536 9.45539C4.52679 9.45539 4.60714 9.41968 4.66071 9.36611L5.10714 8.91968C5.16071 8.86611 5.19643 8.78575 5.19643 8.71432C5.19643 8.64289 5.16071 8.56254 5.10714 8.50896L1.59821 5.00004L5.10714 1.49111C5.16071 1.43753 5.19643 1.35718 5.19643 1.28575C5.19643 1.20539 5.16071 1.13396 5.10714 1.08039L4.66071 0.633963C4.60714 0.580392 4.52679 0.544678 4.45536 0.544678C4.38393 0.544678 4.30357 0.580392 4.25 0.633963L0.0892856 4.79468C0.0357141 4.84825 0 4.92861 0 5.00004C0 5.07146 0.0357141 5.15182 0.0892856 5.20539L4.25 9.36611C4.30357 9.41968 4.38393 9.45539 4.45536 9.45539Z"
                            />
                        </svg>
                        {{ __('Back to dashboard') }}
                    </a>
                    <h2 class="page-title mb-2">
                        {{ __('Hello') }} {{ $user->fullName() }}.
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="py-10">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-5 mx-auto">
                    <form
                        id="user_edit_form"
                        onsubmit="return userProfileSave();"
                        action=""
                        enctype="multipart/form-data"
                    >
                        <div class="row">
                            <div class="col-md-12 col-xl-12">

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Avatar') }}</label>
                                            <input
                                                class="form-control"
                                                id="avatar"
                                                type="file"
                                                name="avatar"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input
                                                class="form-control"
                                                id="name"
                                                type="text"
                                                name="name"
                                                value="{{ $user->name }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Surname') }}</label>
                                            <input
                                                class="form-control"
                                                id="surname"
                                                type="text"
                                                name="surname"
                                                value="{{ $user->surname }}"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Phone') }}</label>
                                            <input
                                                class="form-control"
                                                id="phone"
                                                data-mask="+0000000000000"
                                                data-mask-visible="true"
                                                type="text"
                                                name="phone"
                                                placeholder="+000000000000"
                                                autocomplete="off"
                                                value="{{ $user->phone }}"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Email') }}</label>
                                            <input
                                                class="form-control"
                                                type="email"
                                                value="{{ $user->email }}"
                                                disabled
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-[20px]">
                                    <label class="form-label">{{ __('Country') }}</label>
                                    <select
                                        class="form-select"
                                        id="country"
                                        type="text"
                                        name="country"
                                    >
                                        @include('panel.admin.users.countries')
                                    </select>
                                </div>

                                <div class="row">
                                    <h4>Change Password</h4>
                                    <x-warn
                                        class="!mt-2 mb-3"
                                        variant="warn"
                                    >
                                        <p>
                                            {{ __('Please leave empty if you don’t want to change your password.') }}
                                        </p>
                                    </x-warn>
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Old Password') }}</label>
                                            <input
                                                class="form-control"autocomplete="off"
                                                id="old_password"
                                                type="password"
                                                name="old_password"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('New Password') }}</label>
                                            <input
                                                class="form-control"autocomplete="off"
                                                id="new_password"
                                                type="password"
                                                name="new_password"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-[20px]">
                                            <label class="form-label">{{ __('Confirm Your New Password') }}</label>
                                            <input
                                                class="form-control"
                                                id="new_password_confirmation"
                                                type="password"
                                                name="new_password_confirmation"
                                                autocomplete="off"
                                            />
                                        </div>
                                    </div>
                                </div>

                                @if ($app_is_demo and Auth::user()->type == 'admin')
                                    <a
                                        class="btn btn-primary w-full"
                                        onclick="return toastr.info('Admin settings disabled on Demo version.')"
                                    >
                                        {{ __('Save') }}
                                    </a>
                                @else
                                    <button
                                        class="btn btn-primary w-full"
                                        id="user_edit_button"
                                        form="user_edit_form"
                                    >
                                        {{ __('Save') }}
                                    </button>
                                @endif

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/user.js') }}"></script>
@endpush
