<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>

<body>
    <div id="app" class="container">
        <form action="{{ route('TrintaDias.store') }}" method="POST">
            @csrf
            <meu-componente></meu-componente>
        </form>
    </div>
</body>

</html>