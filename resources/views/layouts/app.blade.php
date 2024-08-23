<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Laralogger - Log Viewer')</title>

    @yield('css')
</head>

<body>
    @include('components.navbar')

    <div class="container-fluid p-5">
        @yield('contents')
    </div>

    @include('components.footer')

    @yield('js')
</body>

</html>
