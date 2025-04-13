<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Teste API Evolution</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: sans-serif; padding: 2rem; background: #f4f4f4; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: orange; border: none; color: white; cursor: pointer; }
        pre { background: #222; color: lime; padding: 1rem; overflow: auto; }
    </style>
</head>
<body>

<h2>Teste Envio de Mensagem via Evolution API</h2>

<form id="form-envio">
    <label>Número (com DDI e DDD):</label>
    <input type="text" id="numero" placeholder="Ex: 5564999999999" required>

    <label>Mensagem:</label>
    <textarea id="mensagem" rows="4" placeholder="Digite sua mensagem..." required></textarea>

    <button type="submit">Enviar Mensagem</button>
</form>

<h3>Resposta da API:</h3>
<pre id="retorno">...</pre>

<script>
    document.getElementById('form-envio').addEventListener('submit', function (e) {
        e.preventDefault();

        const numero = document.getElementById('numero').value;
        const mensagem = document.getElementById('mensagem').value;
        const retorno = document.getElementById('retorno');

        fetch('/kanban/enviar-mensagem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ numero, mensagem })
        })
        .then(res => res.json())
        .then(data => {
            retorno.textContent = JSON.stringify(data, null, 4);
        })
        .catch(err => {
            retorno.textContent = 'Erro na requisição: ' + err;
        });
    });
</script>

</body>
</html>
