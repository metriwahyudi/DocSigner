<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form List</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        * {
            font-family: Arial;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <h1 style="">Form List</h1>
    <ul>
        @foreach($forms as $form)
        <li>
            <a href="{{route('bitrix.form',$form['id'])}}" target="_blank" style="margin-bottom: 10px;">{{$form['name']}}</a>
        </li>
        @endforeach
    </ul>

</body>
</html>
