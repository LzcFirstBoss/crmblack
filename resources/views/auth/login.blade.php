<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Zabulon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="min-h-screen flex items-center justify-center font-sans">

    <div class="flex w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <!-- LADO ESQUERDO - Login -->
        <div class="w-full md:w-1/2 p-10">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('img/zabulon/logopretalaranja.svg') }}" alt="Logo Zabulon" class="h-16">
            </div>

            <p class="text-gray-600 text-center mb-6">Acesse o painel administrativo da Zabulon</p>

            @if(session('error'))
                <p class="text-red-600 text-sm mb-4 text-center">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color: #FE7F32;">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color: #FE7F32;">
                </div>

                <button id="loginBtn" type="submit"
                class="w-full flex items-center justify-center gap-2 text-white font-semibold py-2 rounded-md transition duration-300 shadow-md"
                style="background-color: #FE7F32;">
            <span id="btnText">Entrar</span>
            <svg id="spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        </button>
            </form>

            <div class="text-sm text-center text-gray-500 mt-6">
                Esqueceu a senha? <a href="#" class="hover:underline" style="color: #FE7F32;">Clique aqui</a>
            </div>
        </div>

        <!-- LADO DIREITO - Visual de Impacto -->
        <div class="hidden md:flex md:w-1/2 items-center justify-center p-10 relative" style="background: linear-gradient(135deg, #FE7F32, #e8671a);">
            <div class="text-center text-white">
                <h2 class="text-4xl font-bold mb-4 leading-tight"><img src="{{ asset('img/zabulon/logodeashboard.svg') }}" alt="Logo Zabulon" class="h-12 mx-auto">                </h2>
                <p class="text-lg font-light">Transformando negócios em máquinas de crescimento.</p>
            </div>
            <div class="absolute bottom-4 text-xs text-white opacity-50">© {{ date('Y') }} Zabulon. Todos os direitos reservados.</div>
        </div>
    </div>

</body>
</html>
<script>
    const form = document.querySelector('form');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('spinner');

    form.addEventListener('submit', function () {
        // Desativa o botão
        loginBtn.disabled = true;
        // Troca o texto e mostra o spinner
        btnText.textContent = 'Entrando...';
        spinner.classList.remove('hidden');
    });
</script>
