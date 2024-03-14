@if ($fSectSettings->preheader_active)
    <div
        class="absolute inset-x-0 top-0 z-50 flex h-12 items-center justify-center bg-[#343C57] bg-cover px-3 text-center text-sm text-white opacity-0 bg-blend-luminosity transition-all duration-500 group-[.page-loaded]/body:opacity-100"
        style="background-image: url({{ custom_theme_url('assets/img/site/conffetti.png') }});"
    >
        <p>
            <span class="mr-3 text-xs font-semibold uppercase tracking-wide">{{ __($fSetting->header_title) }}</span>
            <span class="opacity-70">{{ __($fSetting->header_text) }}</span>
        </p>
    </div>
@endif
<header
    class="site-header @if ($fSectSettings->preheader_active) top-12 @else top-1 @endif group/header absolute inset-x-0 z-50 text-white transition-[background,shadow] [&.lqd-is-sticky]:bg-white [&.lqd-is-sticky]:text-black [&.lqd-is-sticky]:shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
>
    <nav
        class="relative flex items-center justify-between border-b border-white border-opacity-10 px-7 py-4 text-[14px] opacity-0 transition-all duration-500 group-[.lqd-is-sticky]/header:border-black group-[.lqd-is-sticky]/header:border-opacity-5 group-[.page-loaded]/body:opacity-100 max-sm:px-2"
        id="frontend-local-navbar"
    >
        <a
            class="site-logo relative basis-1/3 max-lg:basis-1/3"
            href="{{ route('index') }}"
        >
            @if (isset($setting->logo_sticky))
                <img
                    class="peer absolute start-0 top-1/2 -translate-y-1/2 translate-x-3 opacity-0 transition-all group-[.lqd-is-sticky]/header:translate-x-0 group-[.lqd-is-sticky]/header:opacity-100"
                    src="{{ custom_theme_url($setting->logo_sticky_path, true) }}"
                    @if (isset($setting->logo_sticky_2x_path)) srcset="/{{ $setting->logo_sticky_2x_path }} 2x" @endif
                    alt="{{ custom_theme_url($setting->site_name) }} logo"
                >
            @endif
            <img
                class="transition-all group-[.lqd-is-sticky]/header:peer-first:translate-x-2 group-[.lqd-is-sticky]/header:peer-first:opacity-0"
                src="{{ custom_theme_url($setting->logo_path, true) }}"
                @if (isset($setting->logo_2x_path)) srcset="/{{ $setting->logo_2x_path }} 2x" @endif
                alt="{{ $setting->site_name }} logo"
            >
        </a>
        <div
            class="site-nav-container basis-1/3 transition-all max-lg:absolute max-lg:right-0 max-lg:top-full max-lg:max-h-0 max-lg:w-full max-lg:overflow-hidden max-lg:bg-[#343C57] max-lg:text-white [&.lqd-is-active]:max-lg:max-h-[calc(100vh-150px)]">
            <div class="max-lg:max-h-[inherit] max-lg:overflow-y-scroll">
                <ul class="flex items-center justify-center gap-14 whitespace-nowrap text-center max-xl:gap-10 max-lg:flex-col max-lg:items-start max-lg:gap-5 max-lg:p-10">
                    @php
                        $setting->menu_options = $setting->menu_options
                            ? $setting->menu_options
                            : '[{"title": "Home","url": "#banner","target": false},{"title": "Features","url": "#features","target": false},{"title": "How it Works","url": "#how-it-works","target": false},{"title": "Testimonials","url": "#testimonials","target": false},{"title": "Pricing","url": "#pricing","target": false},{"title": "FAQ","url": "#faq","target": false}]';
                        $menu_options = json_decode($setting->menu_options, true);
                        foreach ($menu_options as $menu_item) {
                            printf(
                                '
								<li>
									<a href="%1$s" target="%3$s" class="relative before:absolute before:-inset-x-4 before:-inset-y-2 before:scale-75 before:rounded-lg before:bg-current before:opacity-0 before:transition-all hover:before:scale-100 hover:before:opacity-10 [&.active]:before:scale-100 [&.active]:before:opacity-10">
										%2$s
									</a>
								</li>
								',
                                Route::currentRouteName() != 'index' ? url('/') . $menu_item['url'] : $menu_item['url'],
                                __($menu_item['title']),
                                $menu_item['target'] === false ? '_self' : '_blank',
                            );
                        }
                    @endphp
                </ul>
                @if (count(explode(',', $settings_two->languages)) > 1)
                    <div class="group relative -mt-3 block border-t border-white/5 px-10 pb-5 pt-6 md:hidden">
                        <p class="mb-3 flex items-center gap-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="22"
                                height="22"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                <path d="M3.6 9h16.8"></path>
                                <path d="M3.6 15h16.8"></path>
                                <path d="M11.5 3a17 17 0 0 0 0 18"></path>
                                <path d="M12.5 3a17 17 0 0 1 0 18"></path>
                            </svg>
                            {{ __('Languages') }}
                        </p>
                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if (in_array($localeCode, explode(',', $settings_two->languages)))
                                <a
                                    class="block border-b border-black border-opacity-5 py-3 transition-colors last:border-none hover:bg-black hover:bg-opacity-5"
                                    href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                    rel="alternate"
                                    hreflang="{{ $localeCode }}"
                                >{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}
                                    {{ $properties['native'] }}</a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="flex basis-1/3 justify-end gap-2 max-lg:basis-2/3">
            @if (count(explode(',', $settings_two->languages)) > 1)
                <div class="group relative hidden md:block">
                    <button
                        class="size-10 inline-flex items-center justify-center rounded-full border-2 border-solid border-white !border-opacity-20 transition-colors before:absolute before:end-0 before:top-full before:h-4 before:w-full group-hover:!border-opacity-100 group-hover:bg-white group-hover:text-black group-[.lqd-is-sticky]/header:border-black group-[.lqd-is-sticky]/header:group-hover:bg-black group-[.lqd-is-sticky]/header:group-hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M3.6 9h16.8"></path>
                            <path d="M3.6 15h16.8"></path>
                            <path d="M11.5 3a17 17 0 0 0 0 18"></path>
                            <path d="M12.5 3a17 17 0 0 1 0 18"></path>
                        </svg>
                    </button>
                    <div
                        class="pointer-events-none absolute end-0 top-[calc(100%+1rem)] min-w-[145px] translate-y-2 rounded-md bg-white text-black opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100">
                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if (in_array($localeCode, explode(',', $settings_two->languages)))
                                <a
                                    class="block border-b border-black border-opacity-5 px-3 py-3 transition-colors last:border-none hover:bg-black hover:bg-opacity-5"
                                    href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                    rel="alternate"
                                    hreflang="{{ $localeCode }}"
                                >{{ country2flag(substr($properties['regional'], strrpos($properties['regional'], '_') + 1)) }}
                                    {{ $properties['native'] }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @auth
                <div class="mx-3">
                    <a
                        class="relative inline-flex items-center overflow-hidden rounded-lg border-[2px] border-white !border-opacity-0 bg-white !bg-opacity-10 px-4 py-2 font-medium text-white transition-all duration-300 hover:scale-105 hover:border-black hover:bg-black hover:!bg-opacity-100 hover:text-white hover:shadow-lg group-[.lqd-is-sticky]/header:bg-black group-[.lqd-is-sticky]/header:text-black group-[.lqd-is-sticky]/header:hover:!bg-opacity-100 group-[.lqd-is-sticky]/header:hover:text-white"
                        href="{{ LaravelLocalization::localizeUrl(route('dashboard.index')) }}"
                    >
                        {!! __('Dashboard') !!}
                    </a>
                </div>
            @else
                <a
                    class="relative inline-flex items-center overflow-hidden rounded-lg border-[2px] border-white !border-opacity-10 px-4 py-2 font-medium text-white transition-all duration-300 hover:scale-105 hover:border-black hover:bg-black hover:text-white hover:shadow-lg group-[.lqd-is-sticky]/header:border-black group-[.lqd-is-sticky]/header:text-black group-[.lqd-is-sticky]/header:hover:text-white"
                    href="{{ LaravelLocalization::localizeUrl(route('login')) }}"
                >
                    {!! __($fSetting->sign_in) !!}
                </a>
                <a
                    class="relative inline-flex items-center overflow-hidden rounded-lg border-[2px] border-white !border-opacity-0 bg-white !bg-opacity-10 px-4 py-2 font-medium text-white transition-all duration-300 hover:scale-105 hover:border-black hover:bg-black hover:!bg-opacity-100 hover:text-white hover:shadow-lg group-[.lqd-is-sticky]/header:bg-black group-[.lqd-is-sticky]/header:text-black group-[.lqd-is-sticky]/header:hover:!bg-opacity-100 group-[.lqd-is-sticky]/header:hover:text-white"
                    href="{{ LaravelLocalization::localizeUrl(route('register')) }}"
                >
                    {!! __($fSetting->join_hub) !!}
                </a>
            @endauth

            <button
                class="mobile-nav-trigger size-10 group flex shrink-0 items-center justify-center rounded-full bg-white !bg-opacity-10 group-[.lqd-is-sticky]/header:bg-black lg:hidden"
            >
                <span class="flex w-4 flex-col gap-1">
                    @for ($i = 0; $i <= 1; $i++)
                        <span
                            class="inline-flex h-[2px] w-full bg-white transition-transform first:origin-left last:origin-right group-[.lqd-is-sticky]/header:bg-black group-[&.lqd-is-active]:first:-translate-y-[2px] group-[&.lqd-is-active]:first:translate-x-[3px] group-[&.lqd-is-active]:first:rotate-45 group-[&.lqd-is-active]:last:-translate-x-[2px] group-[&.lqd-is-active]:last:-translate-y-[8px] group-[&.lqd-is-active]:last:-rotate-45"
                        ></span>
                    @endfor
                </span>
            </button>
        </div>
    </nav>
</header>

@if ($app_is_demo)
    <div class="fixed bottom-8 start-8 z-50">
        <a
            class="size-12 inline-flex items-center justify-center rounded-full bg-[#262626] transition-all duration-300 hover:-translate-y-1 hover:scale-110 hover:shadow-md"
            href="https://codecanyon.net/item/magicai-openai-content-text-image-chat-code-generator-as-saas/45408109"
            target="_blank"
            title="{{ __('Buy on Envato') }}"
        >
            <svg
                fill="#0ac994"
                xmlns="http://www.w3.org/2000/svg"
                width="19.824"
                height="22.629"
                viewBox="0 0 19.824 22.629"
            >
                <path
                    d="M17.217,9.263c-.663-.368-2.564-.14-4.848.566-4,2.731-7.369,6.756-7.6,13.218-.043.155-.437-.021-.515-.069a9.2,9.2,0,0,1-.606-7.388c.168-.28-.381-.624-.48-.525A11.283,11.283,0,0,0,1.6,17.091a9.84,9.84,0,0,0,17.2,9.571c3.058-5.481.219-16.4-1.574-17.4Z"
                    transform="translate(-0.32 -9.089)"
                />
            </svg>
        </a>
    </div>
@endif

@if ($fSetting->floating_button_active)
    <div
        class="fixed bottom-8 end-8 z-50 hidden"
        id="myBtn"
    >
        <a
            class="flex items-center gap-5 rounded-xl bg-white px-3 py-3 text-sm text-[#002A40] text-opacity-60 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-110 hover:shadow-md"
            data-fslightbox="html5-youtube-videos"
            href="{{ !empty($fSetting->floating_button_link) ? $fSetting->floating_button_link : '#' }}"
        >
            <span class="lqd-is-in-view inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#3655df] via-[#A068FA] via-70% to-[#327BD1]">
                <svg
                    style="padding: 16px;"
                    xmlns="http://www.w3.org/2000/svg"
                    width="45"
                    height="45"
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
                        fill="#fff"
                    ></path>
                </svg>
            </span>
            <p class="[&amp;_strong]:block">{!! __($fSetting->floating_button_small_text) !!} <strong class="text-[0.9rem] text-black">{!! __($fSetting->floating_button_bold_text) !!} &nbsp;</strong></p>
        </a>
    </div>
@endif
@if ($fSetting->floating_button_active)
    <div
        class="fixed bottom-8 end-8 z-50 hidden"
        id="myBtn"
    >
        <a
            class="flex items-center gap-5 rounded-xl bg-white px-3 py-3 text-sm text-[#002A40] text-opacity-60 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-110 hover:shadow-md"
            data-fancybox="video-show"
            href="{{ !empty($fSetting->floating_button_link) ? $fSetting->floating_button_link : '#' }}"
        >
            <span class="lqd-is-in-view inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#3655df] via-[#A068FA] via-70% to-[#327BD1]">
                <svg
                    style="padding: 16px;"
                    xmlns="http://www.w3.org/2000/svg"
                    width="45"
                    height="45"
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
                        fill="#fff"
                    ></path>
                </svg>
            </span>
            <p class="[&amp;_strong]:block">{!! __($fSetting->floating_button_small_text) !!} <strong class="text-[0.9rem] text-black">{!! __($fSetting->floating_button_bold_text) !!} &nbsp;</strong></p>
        </a>
    </div>
@endif

@includeWhen(in_array($settings_two->chatbot_status && ($settings_two->chatbot_login_require == false || ($settings_two->chatbot_login_require == true && auth()->check())), [
        'frontend',
        'both',
    ]),
    'panel.chatbot.widget')
