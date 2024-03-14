<html>
<head>
    <title></title>
</head>
<body>
    {{-- <h1>{!! $title !!}</h1> --}}
    @foreach($messages as $message)
        @if ($message->input != null)
            <p style='font-family: sans-serif; display: flex;padding: 10px 14px;justify-content: center;align-items: center;gap: 10px;flex-shrink: 0;border-radius: 12px;background: #F3E2FD; font-size: 15px;font-style: normal;line-height: 23px;'>You: {{ $message->input }}</p>
        @endif
        @if ($message->output != null)
            <p style='font-family: sans-serif; display: flex;padding: 10px 14px;justify-content: center;align-items: center;gap: 10px;flex-shrink: 0;border-radius: 12px;background: #F4F4F4;color: #474D59;font-size: 15px;font-style: normal;line-height: 23px;'>Chatbot: {{ $message->output }}</p>
        @endif
    @endforeach
</body>
</html>
