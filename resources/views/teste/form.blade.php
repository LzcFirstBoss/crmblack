<!DOCTYPE html>
<html>
<head>
    <title>Testar Áudio Evolution</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<h1 class="text-2xl mb-6 font-bold">Testar envio de Áudio (Gravar pelo navegador)</h1>

<div class="mb-4">
    <label class="block mb-2">Número (com DDI)</label>
    <input type="text" id="numero" placeholder="Exemplo: 556499999999" class="border p-2 w-full">
</div>

<div class="mb-4">
    <button id="btnGravar" class="bg-red-500 text-white px-6 py-2 rounded">🎤 Gravar Áudio</button>
    <span id="status" class="ml-4 text-gray-500">Parado</span>
</div>

<div class="mb-4">
    <h2 class="font-bold mb-2">Resposta:</h2>
    <pre id="resposta" class="bg-white p-4 rounded text-sm overflow-x-auto"></pre>
</div>

<script>
let gravando = false;
let mediaRecorder;
let audioChunks = [];

document.getElementById('btnGravar').addEventListener('click', () => {
    if (!gravando) {
        iniciarGravacao();
    } else {
        pararGravacao();
    }
});

function iniciarGravacao() {
    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.start();
        gravando = true;

        document.getElementById('btnGravar').textContent = "⏹️ Parar Gravação";
        document.getElementById('btnGravar').classList.remove('bg-red-500');
        document.getElementById('btnGravar').classList.add('bg-gray-700');
        document.getElementById('status').textContent = "Gravando...";

        audioChunks = [];
        mediaRecorder.addEventListener('dataavailable', e => audioChunks.push(e.data));

        mediaRecorder.addEventListener('stop', () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            enviarAudio(audioBlob);
        });
    }).catch(err => {
        alert("Erro ao acessar o microfone");
        console.error(err);
    });
}

function pararGravacao() {
    mediaRecorder.stop();
    gravando = false;

    document.getElementById('btnGravar').textContent = "🎤 Gravar Áudio";
    document.getElementById('btnGravar').classList.remove('bg-gray-700');
    document.getElementById('btnGravar').classList.add('bg-red-500');
    document.getElementById('status').textContent = "Parado";
}

function enviarAudio(blob) {
    const numero = document.getElementById('numero').value.trim();
    if (!numero) {
        alert('Digite um número!');
        return;
    }

    const formData = new FormData();
    formData.append('numero', numero);
    formData.append('audio', blob);

    fetch('{{ route('teste.evolution.audio.enviar') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('resposta').textContent = JSON.stringify(data, null, 4);
    })
    .catch(err => {
        document.getElementById('resposta').textContent = "Erro ao enviar áudio: " + err;
    });
}
</script>

</body>
</html>
