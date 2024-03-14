<!-- AJAX CALLS -->
<script src="{{ custom_theme_url('/assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ custom_theme_url('/assets/libs/toastr/toastr.min.js') }}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

@if (in_array($settings_two->chatbot_status, ['dashboard', 'both']) &&
        !activeRoute('dashboard.user.openai.chat.list', 'dashboard.user.openai.chat.chat') &&
        !(route('dashboard.user.openai.generator.workbook', 'ai_vision') == url()->current()) &&
        !(route('dashboard.user.openai.generator.workbook', 'ai_chat_image') == url()->current()) &&
        !(route('dashboard.user.openai.generator.workbook', 'ai_pdf') == url()->current()))
    @if (Route::has('dashboard.user.openai.webchat.workbook'))
        @if (!(route('dashboard.user.openai.webchat.workbook') == url()->current()))
            <script src="{{ custom_theme_url('/assets/js/panel/openai_chatbot.js') }}"></script>
        @endif
    @else
        <script src="{{ custom_theme_url('/assets/js/panel/openai_chatbot.js') }}"></script>
    @endif
@endif

<script>
    var magicai_localize = {
        signup: @json(__('Sign Up')),
        please_wait: @json(__('Please Wait...')),
        sign_in: @json(__('Sign In')),
        login_redirect: @json(__('Login Successful, Redirecting...')),
        register_redirect: @json(__('Registration is complete. Redirecting...')),
        password_reset_link: @json(__('Password reset link sent succesfully. Please also check your spam folder.')),
        password_reset_done: @json(__('Password succesfully changed.')),
        password_reset: @json(__('Reset Password')),
        missing_email: @json(__('Please enter your email address.')),
        missing_password: @json(__('Please enter your password.')),
        content_copied_to_clipboard: @json(__('Content copied to clipboard.')),
    }
</script>

<!-- PAGES JS-->
@guest()
    <script src="{{ custom_theme_url('/assets/js/panel/login_register.js') }}"></script>
@endguest

@auth
    <script src="{{ custom_theme_url('/assets/js/tabler.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/search.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/list.js/dist/list.js') }}"></script>
@endauth
