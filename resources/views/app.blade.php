<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Task Manager') }}</title>

    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite assets (CSS + JS) -->
    @vite(['resources/js/app.js'])
</head>
<body class="bg-[#f6f6f8] dark:bg-[#121022] text-slate-900 dark:text-slate-100 antialiased min-h-screen">
    <!-- Vue 3 SPA mount point -->
    <div id="app"></div>
</body>
</html>
