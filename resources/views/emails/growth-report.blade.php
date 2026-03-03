<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('report_mail.subject') }}</title>
</head>
<body>
    {!! $reportHtml !!}
</body>
</html>
