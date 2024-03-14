@extends('layout.app')

@section('content')

    <section
        class="site-section relative flex min-h-screen items-center justify-center overflow-hidden py-52 text-center text-white max-md:pb-16 max-md:pt-48"
        id="banner"
    >
        <div class="absolute start-0 top-0 h-full w-full transform-gpu overflow-hidden [backface-visibility:hidden]">
            <div class="banner-bg absolute left-0 top-0 h-full w-full"></div>
        </div>
        <div class="container relative">
            <div class="mx-auto flex w-1/2 flex-col items-center max-lg:w-2/3 max-md:w-full">
                <h6
                    class="relative mb-8 translate-y-6 overflow-hidden rounded-2xl bg-white bg-opacity-15 px-3 py-1 text-white opacity-0 blur-lg transition-all ease-out group-[.page-loaded]/body:translate-y-0 group-[.page-loaded]/body:opacity-100 group-[.page-loaded]/body:blur-0">
                    <div class="banner-subtitle-gradient absolute -inset-3 blur-3xl transition-all duration-500 group-[.page-loaded]/body:opacity-0">
                        <div class="animate-hue-rotate absolute inset-0 bg-gradient-to-br from-violet-600 to-red-500"></div>
                    </div>
                    <span class="relative">{!! __($setting->site_name) !!}</span>
                    <span class="dot relative"></span>
                    <span class="relative opacity-60">{!! __($fSetting->hero_subtitle) !!}</span>
                </h6>
                <div class="banner-title-wrap relative">
                    <h1
                        class="banner-title mb-7 translate-y-7 font-body font-bold -tracking-wide text-white opacity-0 transition-all ease-out group-[.page-loaded]/body:translate-y-0 group-[.page-loaded]/body:opacity-100">
                        {!! __($fSetting->hero_title) !!}
                        @if ($fSetting->hero_title_text_rotator != null)
                            <span class="lqd-text-rotator inline-grid grid-cols-1 grid-rows-1 transition-[width] duration-200">
                                @foreach (explode(',', __($fSetting->hero_title_text_rotator)) as $keyword)
                                    <span
                                        class="lqd-text-rotator-item {{ $loop->first ? 'lqd-is-active' : '' }} col-start-1 row-start-1 inline-flex translate-x-3 opacity-0 blur-sm transition-all duration-300 [&.lqd-is-active]:translate-x-0 [&.lqd-is-active]:opacity-100 [&.lqd-is-active]:blur-0"
                                    >
                                        <span>{!! $keyword !!}</span>
                                    </span>
                                @endforeach
                            </span>
                        @endif
                        <svg
                            class="lqd-split-text-words inline transition-all duration-[2850ms]"
                            width="47"
                            height="62"
                            viewBox="0 0 47 62"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="M27.95 0L0 38.213H18.633V61.141L46.583 22.928H27.95V0Z" />
                        </svg>
                    </h1>
                </div>
                <p
                    class="mb-7 w-3/4 text-[20px] leading-[1.25em] text-fuchsia-700 opacity-75 max-sm:w-full [&_.lqd-split-text-words]:translate-y-3 [&_.lqd-split-text-words]:opacity-0 [&_.lqd-split-text-words]:transition-all [&_.lqd-split-text-words]:ease-out group-[.page-loaded]/body:[&_.lqd-split-text-words]:translate-y-0 group-[.page-loaded]/body:[&_.lqd-split-text-words]:text-white group-[.page-loaded]/body:[&_.lqd-split-text-words]:opacity-100">
                    <x-split-words
                        text="{!! __($fSetting->hero_description) !!}"
                        transitionDelayStart="{{ 0.15 }}"
                        transitionDelayStep="{{ 0.02 }}"
                    />
                </p>
                <div class="translate-y-3 opacity-0 transition-all delay-[450ms] group-[.page-loaded]/body:translate-y-0 group-[.page-loaded]/body:opacity-100">
                    @if ($fSetting->hero_button_type == 1)
                        <a
                            class="relative inline-flex items-center overflow-hidden rounded-xl border-[2px] border-[#343C57] border-opacity-0 bg-white bg-opacity-10 px-7 py-4 font-semibold transition-all duration-300 hover:scale-105 hover:border-white hover:bg-white hover:bg-opacity-100 hover:text-black hover:shadow-lg"
                            href="{{ !empty($fSetting->hero_button_url) ? $fSetting->hero_button_url : '#' }}"
                        >
                            <span class="relative z-10 inline-flex items-center">
                                {!! __($fSetting->hero_button) !!}
                                <svg
                                    class="ml-2"
                                    width="11"
                                    height="14"
                                    viewBox="0 0 47 62"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path d="M27.95 0L0 38.213H18.633V61.141L46.583 22.928H27.95V0Z"></path>
                                </svg>
                            </span>
                        </a>
                    @else
                        <a
                            class="inline-flex w-full items-center justify-center bg-black bg-opacity-10 px-4 py-3 text-lg font-semibold text-white transition-all duration-300 hover:bg-opacity-20"
                            data-fslightbox="video-gallery"
                            style="border-radius: 3rem;"
                            href="{{ !empty($fSetting->hero_button_url) ? $fSetting->hero_button_url : '#' }}"
                        >
                            <svg
                                class="icon icon-tabler icon-tabler-player-play-filled me-4 bg-white"
                                style="padding: 13px; border-radius: 2rem;"
                                xmlns="http://www.w3.org/2000/svg"
                                width="40"
                                height="40"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M6 4v16a1 1 0 0 0 1.524 .852l13 -8a1 1 0 0 0 0 -1.704l-13 -8a1 1 0 0 0 -1.524 .852z"
                                    stroke-width="0"
                                    fill="#37393d"
                                ></path>
                            </svg>
                            {!! __($fSetting->hero_button) !!} &nbsp;
                        </a>
                    @endif
                </div>
                <br>
                <div class="translate-y-3 opacity-0 transition-all delay-[500ms] group-[.page-loaded]/body:translate-y-0 group-[.page-loaded]/body:opacity-100">
                    <a
                        class="opacity-50 transition-opacity hover:opacity-100"
                        href="#features"
                    >{!! __($fSetting->hero_scroll_text) !!}</a>
                </div>
            </div>
        </div>
        <div class="banner-divider absolute inset-x-0 -bottom-[2px]">
            <svg
                class="h-auto w-full fill-background"
                width="1440"
                height="105"
                viewBox="0 0 1440 105"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                preserveAspectRatio="none"
            >
                <path d="M0 0C240 68.7147 480 103.072 720 103.072C960 103.072 1200 68.7147 1440 0V104.113H0V0Z" />
            </svg>
        </div>
    </section>

    @if ($fSectSettings->features_active == 1)
        {!! adsense_features_728x90() !!}
        <section id="features">
            <section class="site-section pb-20 pt-32 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100">
                <div class="container">
                    <x-section-header
                        title="{!! __($fSectSettings->features_title) !!}"
                        subtitle="{!! __($fSectSettings->features_description) ?? __('MagicAI is designed to help you generate high-quality content instantly, without breaking a sweat.') !!}"
                    />
                    <div class="grid grid-cols-3 justify-between gap-x-20 gap-y-9 max-lg:grid-cols-2 max-lg:gap-x-10 max-md:grid-cols-1">
                        @foreach ($futures as $future)
                            <x-box
                                title="{!! __($future->title) !!}"
                                desc="{!! __($future->description) !!}"
                            >
                                <x-slot name="icon">
                                    {!! $future->image !!}
                                </x-slot>
                            </x-box>
                        @endforeach
                    </div>
                </div>
            </section>
    @endif

    @if ($fSectSettings->generators_active == 1)
        <section class="site-section transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100">
            <div class="container">
                <div class="rounded-[50px] border p-20 max-xl:px-10 max-lg:py-12 max-sm:px-5">
                    <div
                        class="lqd-tabs"
                        data-lqd-tabs-style="1"
                    >
                        <div class="lqd-tabs-triggers mb-9 grid grid-cols-5 justify-between gap-8 max-lg:grid-cols-3 max-lg:gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                            @foreach ($generatorsList as $entry)
                                <x-tabs-trigger
                                    target="#{{ \Illuminate\Support\Str::slug($entry->menu_title) }}"
                                    label="{!! __($entry->menu_title) !!}"
                                    active="{{ $loop->first ? 'true' : '' }}"
                                />
                            @endforeach
                        </div>
                        <div class="lqd-tabs-content-wrap">
                            @foreach ($generatorsList as $entry)
                                <div
                                    class="lqd-tabs-content {{ !$loop->first ? 'hidden' : '' }}"
                                    id="{{ \Illuminate\Support\Str::slug($entry->menu_title) }}"
                                >
                                    <div class="flex flex-wrap justify-between max-md:gap-4">
                                        <div class="flex w-[47%] flex-col items-start rounded-xl p-8 shadow-lg max-md:w-full">
                                            <h6 class="mb-10 rounded-xl bg-[#F3E5F5] px-3 py-1">
                                                {!! __($entry->subtitle_one) !!}
                                                <span class="dot"></span>
                                                <span class="opacity-50">{!! __($entry->subtitle_two) !!}</span>
                                            </h6>
                                            <h3 class="mb-7 mt-auto">{!! __($entry->title) !!}</h3>
                                            <p class="text-lg [&_strong]:font-semibold [&_strong]:text-black">
                                                {!! __($entry->text) !!}</p>
                                        </div>
                                        <div
                                            class="group w-[47%] rounded-xl p-8 max-md:w-full"
                                            style="background-color: {{ $entry->color }};"
                                        >
                                            <div class="text-center">
                                                <figure class="mb-6 w-full">
                                                    <img
                                                        class="w-full rounded-2xl shadow-[0px_3px_45px_rgba(0,0,0,0.07)] transition-all duration-300 group-hover:-translate-y-2 group-hover:scale-[1.025] group-hover:shadow-[0px_20px_65px_rgba(0,0,0,0.05)]"
                                                        width="878"
                                                        height="748"
                                                        src="{{ custom_theme_url($entry->image, true) }}"
                                                        alt="{{ __($entry->image_title) }}"
                                                    >
                                                </figure>
                                                <p class="text-lg font-semibold text-heading-foreground">{!! __($entry->image_title) !!}</p>
                                                <p class="text-sm text-heading-foreground">{!! __($entry->image_subtitle) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->who_is_for_active == 1)
        <section class="site-section py-20 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100">
            <div class="container">
                <div class="grid grid-cols-3 gap-4 max-lg:grid-cols-2 max-md:grid-cols-1">
                    @foreach ($who_is_for as $entry)
                        <x-color-box
                            title="{!! __($entry->title) !!}"
                            color="{{ $entry->color }}"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    </section>

    @if ($fSectSettings->custom_templates_active == 1)
        {!! adsense_templates_728x90() !!}
        <section
            class="site-section pb-9 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="templates"
        >
            <div class="container">
                <div class="rounded-[50px] border p-10 max-sm:px-5">
                    <x-section-header
                        mb="7"
                        width="w-3/5"
                        title="{!! __($fSectSettings->custom_templates_title) !!}"
                        subtitle="{!! $fSectSettings->custom_templates_description ??
                            'Create your own template or use pre-made templates and examples for various content types and industries to help you get started quickly.' !!}"
                    >
                        <h6 class="mb-6 inline-block rounded-md bg-[#083D91] bg-opacity-15 px-3 py-1 text-[13px] font-medium text-[#083D91]">
                            {!! __($fSectSettings->custom_templates_subtitle_one) !!}
                            <span class="dot"></span>
                            <span class="opacity-50">{!! __($fSectSettings->custom_templates_subtitle_two) !!}</span>
                        </h6>
                    </x-section-header>
                    <div class="flex flex-col items-center">
                        <div class="mx-auto mb-10 inline-flex flex-wrap items-center gap-2 rounded-lg border p-[0.35rem] text-sm font-semibold leading-none max-md:justify-center">
                            <x-tabs-trigger
                                target=".templates-all"
                                style="2"
                                label="{{ __('All') }}"
                                active="true"
                            />
                            @foreach ($filters as $filter)
                                <x-tabs-trigger
                                    target=".templates-{{ \Illuminate\Support\Str::slug($filter->name) }}"
                                    style="2"
                                    label="{{ __($filter->name) }}"
                                />
                            @endforeach
                        </div>
                    </div>
                    <div class="relative">
                        <div class="templates-cards grid max-h-[28rem] grid-cols-3 gap-4 overflow-hidden max-lg:grid-cols-2 max-md:grid-cols-1">
                            @foreach ($templates as $item)
                                @if ($item->active != 1)
                                    @continue
                                @endif
                                <x-box
                                    wrapperClass="templates-all templates-{{ \Illuminate\Support\Str::slug($item->filters) }}"
                                    style="2"
                                    title="{{ __($item->title) }}"
                                    desc="{{ __($item->description) }}"
                                >
                                    <x-slot name="image">
                                        <span
                                            class="size-11 mb-4 inline-flex items-center justify-center rounded-lg bg-gradient-to-bl from-[#f0f0f2] to-[#d7d7d9] [&_path]:fill-inherit [&_svg]:h-5 [&_svg]:w-6 [&_svg]:fill-[#7c7c7e]"
                                        >
                                            {!! $item->image !!}
                                        </span>
                                    </x-slot>
                                </x-box>
                            @endforeach
                        </div>
                        <div class="templates-cards-overlay absolute inset-x-0 bottom-0 z-10 h-[230px] bg-gradient-to-t from-background from-20% to-transparent">
                        </div>
                    </div>
                    <div class="relative z-20 mt-2 text-center">
                        <button class="templates-show-more text-[14px] font-semibold text-[#5A4791]">
                            <span class="size-7 mr-1 inline-grid place-content-center rounded-lg bg-[#885EFE] bg-opacity-10">
                                <svg
                                    width="12"
                                    height="12"
                                    viewBox="0 0 12 12"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path d="M5.671 11.796V0.996H7.125V11.796H5.671ZM0.998 7.125V5.671H11.798V7.125H0.998Z" />
                                </svg>
                            </span>
                            <span class="inline-grid h-7 place-content-center rounded-lg bg-[#885EFE] bg-opacity-10 px-2">
                                {{ __('Show more') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->tools_active == 1)
        {!! adsense_tools_728x90() !!}
        <section class="site-section py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100">
            <div class="container">
                <div class="rounded-[50px] border p-10 max-sm:px-6 max-sm:py-16">
                    <x-section-header
                        mb="14"
                        title="{{ __($fSectSettings->tools_title) }}"
                        subtitle="{{ __($fSectSettings->tools_description) ?? __('MagicAI has all the tools you need to create and manage your SaaS platform.') }}"
                    />
                    <div class="grid grid-cols-3 gap-3 max-lg:grid-cols-2 max-md:grid-cols-1">
                        @foreach ($tools as $tool)
                            <x-box
                                style="3"
                                title="{!! __($tool->title) !!}"
                                desc="{!! __($tool->description) !!}"
                            >
                                <x-slot name="image">
                                    <img
                                        class="-mx-8 max-w-[calc(100%+4rem)]"
                                        src="{{ custom_theme_url($tool->image, true) }}"
                                        alt="{!! __($tool->title) !!}"
                                        width="696"
                                        height="426"
                                    >
                                </x-slot>
                            </x-box>
                        @endforeach

                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->how_it_works_active == 1)
        {!! adsense_how_it_works_728x90() !!}
        <section
            class="site-section py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="how-it-works"
        >
            <div class="container">
                <div
                    class="rounded-[50px] bg-[#010101] bg-cover p-10 py-24 text-white text-opacity-60 shadow-xl max-sm:bg-bottom max-sm:px-5"
                    style="background-image: url({{ custom_theme_url('assets/img/site/steps-bg.jpg') }});"
                >
                    <div class="mx-auto mb-14 w-2/5 text-center max-xl:w-1/2 max-lg:w-8/12 max-md:w-full">
                        <h2 class="text-[64px] leading-none text-[#E5E6E6] max-sm:text-[45px]">{!! __($fSectSettings->how_it_works_title) !!}
                        </h2>
                    </div>
                    <div class="grid-cols-{{ count($howitWorks) }} mb-20 grid gap-7 max-md:grid-cols-1">
                        @foreach ($howitWorks as $step)
                            <div class="group mx-auto flex max-w-[270px] flex-col items-center text-center text-xl font-medium">
                                <span
                                    class="size-16 mb-10 grid place-content-center rounded-full border-[2px] border-[#A2B2C9] border-opacity-15 text-[26px] font-medium transition-all group-hover:-translate-y-2 group-hover:scale-110 group-hover:border-white group-hover:bg-white group-hover:text-black"
                                >{{ __($step->order) }}</span>
                                <p>{!! __($step->title) !!}</p>
                            </div>
                        @endforeach
                    </div>
                    @if ($howitWorksDefaults['option'] == 1)
                        <div class="flex justify-center text-[#A2B2C9]">
                            {!! $howitWorksDefaults['html'] !!}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->testimonials_active == 1)
        {!! adsense_testimonials_728x90() !!}
        <section
            class="site-section relative py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="testimonials"
        >
            <div
                class="absolute inset-x-0 top-0 -z-1 h-[150vh]"
                style="background: linear-gradient(to bottom, transparent, #F0EFFA, transparent)"
            ></div>
            <div class="container relative">
                <div
                    class="rounded-[50px] border bg-contain bg-center bg-no-repeat p-11 pb-24 max-sm:px-5"
                    style="background-image: url({{ custom_theme_url('assets/img/site/world-map.png') }})"
                >
                    <x-section-header
                        width="w-1/2"
                        mb="10"
                        title="{!! $fSectSettings->testimonials_title !!}"
                        subtitle=""
                    >
                        <h6 class="mb-6 inline-block rounded-md bg-[#28027C] bg-opacity-15 px-3 py-1 text-[13px] font-medium text-[#28027C]">
                            {!! __($fSectSettings->testimonials_subtitle_one) !!}
                            <span class="dot"></span>
                            <span class="opacity-50">{!! __($fSectSettings->testimonials_subtitle_two) !!}</span>
                        </h6>
                    </x-section-header>
                    <div class="max-lg:11/12 mx-auto w-8/12 max-md:w-full">
                        <div class="mb-20">
                            <div
                                class="mx-auto mb-7 w-[235px] gap-5"
                                data-flickity='{ "asNavFor": ".testimonials-main-carousel", "contain": false, "pageDots": false, "cellAlign": "center", "prevNextButtons": false, "wrapAround": true, "draggable": false }'
                                style="mask-image: linear-gradient(to right, transparent 0%, #000 15%, #000 85%, transparent 100% ); -webkit-mask-image: linear-gradient(to right, transparent 0%, #000 15%, #000 85%, transparent 100% );"
                            >
                                @foreach ($testimonials as $entry)
                                    <div class="w1/3 group cursor-pointer pb-[16px] pt-9 text-center text-[15px] font-medium">
                                        <figure
                                            class="size-11 mx-auto mb-4 overflow-hidden rounded-full transition-all group-[&.is-nav-selected]:-translate-y-4 group-[&.is-nav-selected]:scale-[1.75] group-[&.is-nav-selected]:border-[5px] group-[&.is-nav-selected]:border-white group-[&.is-nav-selected]:shadow-sm max-sm:group-[&.is-nav-selected]:scale-150"
                                        >
                                            <img
                                                class="h-full w-full object-cover object-center"
                                                src="{{ url('') . isset($entry->avatar) ? (str_starts_with($entry->avatar, 'asset') ? custom_theme_url($entry->avatar) : '/testimonialAvatar/' . $entry->avatar) : custom_theme_url('assets/img/auth/default-avatar.png') }}"
                                                alt="{{ __($entry->full_name) }}"
                                            >
                                        </figure>
                                        <div class="whitespace-nowrap opacity-0 transition-all group-[&.is-nav-selected]:opacity-100">
                                            <p class="text-heading-foreground">{!! __($entry->full_name) !!}</p>
                                            <p class="text-heading-foreground opacity-15">{!! __($entry->job_title) !!}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div
                                class="testimonials-main-carousel text-center text-[26px] leading-[1.27em] text-heading-foreground max-sm:text-lg max-sm:[&_.flickity-button-icon]:!left-1/4 max-sm:[&_.flickity-button-icon]:!top-1/4 max-sm:[&_.flickity-button-icon]:!h-1/2 max-sm:[&_.flickity-button-icon]:!w-1/2 [&_.flickity-button.next]:-right-16 max-md:[&_.flickity-button.next]:-right-10 [&_.flickity-button.previous]:-left-16 max-md:[&_.flickity-button.previous]:-left-10 [&_.flickity-button]:opacity-40 [&_.flickity-button]:transition-all [&_.flickity-button]:hover:bg-transparent [&_.flickity-button]:hover:opacity-100 [&_.flickity-button]:focus:shadow-none max-sm:[&_.flickity-button]:relative max-sm:[&_.flickity-button]:!left-auto max-sm:[&_.flickity-button]:!right-auto max-sm:[&_.flickity-button]:top-auto max-sm:[&_.flickity-button]:!mx-4 max-sm:[&_.flickity-button]:translate-y-0"
                                data-flickity='{ "contain": true, "wrapAround": true, "pageDots": false, "adaptiveHeight": true }'
                            >
                                @foreach ($testimonials as $entry)
                                    <div class="w-full shrink-0 grow-0 basis-full">
                                        <blockquote class="max-sm:mb-7">
                                            <p>{!! __('“' . $entry->words . '”') !!}</p>
                                        </blockquote>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                        <div class="flex justify-center gap-20 opacity-80 max-lg:gap-12 max-sm:gap-4">
                            @foreach ($clients as $entry)
                                <img
                                    class="h-full w-full object-cover object-center"
                                    style="max-width: 48px; max-height: 48px;"
                                    src="{{ url('') . isset($entry->avatar) ? (str_starts_with($entry->avatar, 'asset') ? custom_theme_url($entry->avatar) : '/clientAvatar/' . $entry->avatar) : custom_theme_url('assets/img/auth/default-avatar.png') }}"
                                    alt="{{ __($entry->alt) }}"
                                    title="{{ __($entry->title) }}"
                                >
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->pricing_active == 1)
        {!! adsense_pricing_728x90() !!}
        <section
            class="site-section relative py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="pricing"
        >
            <div class="container relative">
                <div class="relative rounded-[50px] border p-11 max-lg:px-5">
                    <x-section-header
                        mb="7"
                        title="{!! __($fSectSettings->pricing_title) !!}"
                        subtitle="{!! __($fSectSettings->pricing_description) ?? __('Flexible and affording plans tailored to your needs. Save up to %20 for a limited time.') !!}"
                    />
                    <div class="lqd-tabs text-center">
                        <div class="lqd-tabs-triggers mx-auto mb-9 inline-flex flex-wrap gap-2 rounded-md border text-[15px] font-medium leading-none">
                            @if ($plansSubscriptionMonthly->count() > 0)
                                <x-tabs-trigger
                                    target="#pricing-monthly"
                                    style="3"
                                    label="{{ __('Monthly') }}"
                                    active="true"
                                />
                            @endif
                            @if ($plansSubscriptionAnnual->count() > 0)
                                <x-tabs-trigger
                                    target="#pricing-annual"
                                    style="3"
                                    label="{{ __('Annual') }}"
                                    badge="{{ __($fSectSettings->pricing_save_percent) }}"
                                />
                            @endif
                            @if ($plansSubscriptionLifetime->count() > 0)
                                <x-tabs-trigger
                                    target="#pricing-lifetime"
                                    style="3"
                                    label="{{ __('Lifetime') }}"
                                />
                            @endif
                            @if ($plansPrepaid->count() > 0)
                                <x-tabs-trigger
                                    target="#pricing-prepaid"
                                    style="3"
                                    label="{{ __('Pre-Paid') }}"
                                />
                            @endif
                        </div>
                        <div class="lqd-tabs-content-wrap px-10 max-xl:px-0">
                            <div class="lqd-tabs-content">
                                <div id="pricing-monthly">
                                    <div class="grid grid-cols-3 gap-2 max-md:grid-cols-1">
                                        @foreach ($plansSubscriptionMonthly as $plan)
                                            <x-price-table
                                                currency="{{ currency()->symbol }}"
                                                featured="{{ $plan->is_featured == 1 }}"
                                                title="{!! $plan->name !!}"
                                                price="{{ formatPrice($plan->price, 2) }}"
                                                period="{{ $plan->frequency == 'monthly' ? 'month' : 'year' }}"
                                                buttonLabel="{{ __('Select') }} {{ __($plan->name) }}"
                                                buttonLink="{{ route('register', ['plan' => $plan->id]) }}"
                                                activeFeatures="{{ $plan->features }}"
                                                inactiveFeatures=""
                                                totalWords="{{ $plan->total_words }}"
                                                totalImages="{{ $plan->total_images }}"
                                                trialDays="{{ $plan->trial_days }}"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                                <div
                                    class="hidden"
                                    id="pricing-annual"
                                >
                                    <div class="grid grid-cols-3 gap-2 max-md:grid-cols-1">
                                        @foreach ($plansSubscriptionAnnual as $plan)
                                            <x-price-table
                                                currency="{{ currency()->symbol }}"
                                                featured="{{ $plan->is_featured == 1 }}"
                                                title="{!! $plan->name !!}"
                                                price="{{ formatPrice($plan->price, 2) }}"
                                                period="{{ $plan->frequency == 'monthly' ? 'month' : 'year' }}"
                                                buttonLabel="{{ __('Select') }} {{ __($plan->name) }}"
                                                buttonLink="{{ route('register', ['plan' => $plan->id]) }}"
                                                activeFeatures="{{ $plan->features }}"
                                                inactiveFeatures=""
                                                totalWords="{{ $plan->total_words }}"
                                                totalImages="{{ $plan->total_images }}"
                                                trialDays="{{ $plan->trial_days }}"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                                <div
                                    class="hidden"
                                    id="pricing-prepaid"
                                >
                                    <div class="grid grid-cols-3 gap-2 max-md:grid-cols-1">
                                        @foreach ($plansPrepaid as $plan)
                                            <x-price-table
                                                currency="{{ currency()->symbol }}"
                                                featured="{{ $plan->is_featured == 1 }}"
                                                title="{!! $plan->name !!}"
                                                price="{{ formatPrice($plan->price, 2) }}"
                                                period="One Time Payment"
                                                buttonLabel="{{ __('Select') }} {{ __($plan->name) }}"
                                                buttonLink="{{ route('register', ['plan' => $plan->id]) }}"
                                                activeFeatures="{{ $plan->features }}"
                                                inactiveFeatures=""
                                                totalWords="{{ $plan->total_words }}"
                                                totalImages="{{ $plan->total_images }}"
                                                trialDays="{{ $plan->trial_days }}"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                                <div
                                    class="hidden"
                                    id="pricing-lifetime"
                                >
                                    <div class="grid grid-cols-3 gap-2 max-md:grid-cols-1">
                                        @foreach ($plansSubscriptionLifetime as $plan)
                                            <x-price-table
                                                currency="{{ currency()->symbol }}"
                                                featured="{{ $plan->is_featured == 1 }}"
                                                title="{!! $plan->name !!}"
                                                price="{{ formatPrice($plan->price, 2) }}"
                                                period="{{ $plan->frequency == 'lifetime_monthly' ? 'month' : 'year' }}"
                                                buttonLabel="{{ __('Select') }} {{ __($plan->name) }}"
                                                buttonLink="{{ route('register', ['plan' => $plan->id]) }}"
                                                activeFeatures="{{ $plan->features }}"
                                                inactiveFeatures=""
                                                totalWords="{{ $plan->total_words }}"
                                                totalImages="{{ $plan->total_images }}"
                                                trialDays="{{ $plan->trial_days }}"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-9 flex justify-center">
                        <div class="flex w-[305px] items-center gap-5 text-sm text-[#002A40] text-opacity-60">
                            <span class="size-10 inline-flex shrink-0 items-center justify-center rounded-xl bg-[#6C727B] bg-opacity-10">
                                <svg
                                    width="13"
                                    height="18"
                                    viewBox="0 0 13 18"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M10.346 6.323H4.024V3.449C4.024 2.839 4.26632 2.25399 4.69765 1.82266C5.12899 1.39132 5.714 1.149 6.324 1.149C6.934 1.149 7.51901 1.39132 7.95035 1.82266C8.38168 2.25399 8.624 2.839 8.624 3.449C8.624 3.6015 8.68458 3.74775 8.79241 3.85559C8.90025 3.96342 9.0465 4.024 9.199 4.024C9.3515 4.024 9.49775 3.96342 9.60558 3.85559C9.71342 3.74775 9.774 3.6015 9.774 3.449C9.774 2.534 9.41052 1.65648 8.76352 1.00948C8.11652 0.362482 7.23899 -0.000999451 6.324 -0.000999451C5.409 -0.000999451 4.53148 0.362482 3.88448 1.00948C3.23748 1.65648 2.874 2.534 2.874 3.449V6.323H2.3C1.69001 6.323 1.10499 6.56532 0.673653 6.99666C0.242319 7.42799 0 8.013 0 8.623V14.946C0 15.248 0.0594935 15.5471 0.175079 15.8262C0.290665 16.1052 0.460078 16.3588 0.673653 16.5723C0.887227 16.7859 1.14078 16.9553 1.41983 17.0709C1.69888 17.1865 1.99796 17.246 2.3 17.246H10.347C10.649 17.246 10.9481 17.1865 11.2272 17.0709C11.5062 16.9553 11.7598 16.7859 11.9733 16.5723C12.1869 16.3588 12.3563 16.1052 12.4719 15.8262C12.5875 15.5471 12.647 15.248 12.647 14.946V8.622C12.6469 8.31996 12.5872 8.0209 12.4715 7.7419C12.3558 7.46291 12.1863 7.20943 11.9726 6.99595C11.759 6.78247 11.5053 6.61316 11.2262 6.49769C10.9472 6.38223 10.648 6.32287 10.346 6.323Z"
                                        fill="#6C727B"
                                    />
                                </svg>
                            </span>
                            <p class="[&_strong]:block">{!! __('<strong>Safe Payment:</strong> Use Stripe or Credit Card.') !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->faq_active == 1)
        {!! adsense_faq_728x90() !!}
        <section
            class="site-section py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="faq"
        >
            <div class="container">
                <div class="relative rounded-[50px] border p-11 pb-16 max-sm:px-5">
                    <x-section-header
                        mb="9"
                        width="w-1/2"
                        title="{!! __($fSectSettings->faq_title) !!}"
                        subtitle="{!! __($fSectSettings->faq_subtitle) !!}"
                    >
                        <h6 class="mb-6 inline-block rounded-md bg-[#60027C] bg-opacity-15 px-3 py-1 text-[13px] font-medium text-[#60027C]">
                            {!! __($fSectSettings->faq_text_one) !!}
                            <span class="dot"></span>
                            <span class="opacity-50">{!! __($fSectSettings->faq_text_two) !!}</span>
                        </h6>
                    </x-section-header>
                    <div class="lqd-accordion mx-auto w-5/6 max-lg:w-full">
                        @foreach ($faq as $item)
                            <x-accordion-item
                                id="faq-{{ $item->id }}"
                                title="{!! __($item->question) !!}"
                                content="{!! __($item->answer) !!}"
                            />
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($fSectSettings->blog_active == 1)
        <section
            class="site-section mb-14 py-10 transition-all duration-700 md:translate-y-8 md:opacity-0 [&.lqd-is-in-view]:translate-y-0 [&.lqd-is-in-view]:opacity-100"
            id="blog"
        >
            <div class="container">
                <x-section-header
                    mb="9"
                    width="w-1/2"
                    title="{!! __($fSectSettings->blog_title) !!}"
                    subtitle=""
                >
                    <h6 class="mb-6 inline-block rounded-md bg-[#60027C] bg-opacity-15 px-3 py-1 text-[13px] font-medium text-[#60027C]">
                        {!! __($fSectSettings->blog_subtitle) !!}</span>
                    </h6>
                </x-section-header>
                <div class="lg:grid-cols-{{ $fSectSettings->blog_posts_per_page }} mb-10 grid grid-cols-1 gap-14 md:grid-cols-2">
                    @foreach ($posts as $post)
                        @include('blog.part.card')
                    @endforeach
                </div>
                <div class="flex justify-center">
                    <a
                        class="group flex space-x-2"
                        href="/blog"
                    >
                        <div class="rounded-md bg-green-100 px-2 py-1 text-sm font-semibold text-green-500 transition-colors group-hover:bg-green-200">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path d="M12 5l0 14"></path>
                                <path d="M5 12l14 0"></path>
                            </svg>
                        </div>
                        <div class="rounded-md bg-green-100 px-2 py-1 text-sm font-semibold text-green-500 transition-colors group-hover:bg-green-200">
                            {{ __($fSectSettings->blog_button_text) }}
                        </div>
                    </a>
                </div>
            </div>
        </section>
    @endif

    @if ($setting->gdpr_status == 1)
        <div
            class="fixed bottom-12 left-1/2 z-50 -translate-x-1/2 rounded-full bg-white p-2 drop-shadow-2xl max-sm:w-11/12"
            id="gdpr"
        >
            <div class="flex items-center justify-between gap-6 text-sm">
                <div class="content-left pl-4">
                    {!! __($setting->gdpr_content) !!}
                </div>
                <div class="content-right text-end">
                    <button class="cursor-pointer rounded-full bg-black px-4 py-2 text-white">{!! __($setting->gdpr_button) !!}</button>
                </div>
            </div>
        </div>
    @endif

@endsection
