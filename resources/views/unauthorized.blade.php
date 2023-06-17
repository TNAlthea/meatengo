<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- VITE -->
    @vite('resources/css/app.css')

    <?php
        // return response()->json([
        //     'error' => 'you are unable to perform this action',
        // ], 401);
    ?>
</head>

<body class="bg-gray-300 flex justify-center items-center h-screen">
    <section class="container flex flex-col gap-3 justify-center items-center p-auto">
        <p>SORRY BUT YOU ARE UNAUTHORIZED FROM PERFORMING THIS ACTION</p>
        <a href="{{ route('home') }}">Back to Home</a>
    </section>
</body>
</html>