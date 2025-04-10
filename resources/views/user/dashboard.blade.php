<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel</title>
</head>
<body>
    <h2>Olá, {{ auth()->user()->name }}!</h2>
    <p>Você está logado no sistema.</p>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Sair</button>
    </form>
</body>
</html>
