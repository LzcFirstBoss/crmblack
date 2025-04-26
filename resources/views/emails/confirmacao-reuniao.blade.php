<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Reunião Confirmada</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.08);">

        <div style="background-color: #1F1F1F; padding: 20px;">
            <h2 style="margin: 0; color: #FE7F32;"> Reunião Confirmada com a Zabulon</h2>
        </div>

        <div style="padding: 30px;">
            <p style="font-size: 15px;">Olá,</p>

            <p style="font-size: 15px;">Sua reunião foi <strong>agendada com sucesso</strong>! Veja os detalhes abaixo:</p>

            <div style="padding: 20px; background-color: #f9f9f9; border-left: 4px solid #FE7F32; border-radius: 6px;">
                <p style="margin: 0;"><strong style="color: #666;">Título:</strong><br>{{ $titulo }}</p>
                <p style="margin: 15px 0 0;"><strong style="color: #666;">Início:</strong><br>{{ \Carbon\Carbon::parse($inicio)->format('d/m/Y H:i') }}</p>
                <p style="margin: 15px 0 0;"><strong style="color: #666;">Previsão de fim:</strong><br>{{ \Carbon\Carbon::parse($fim)->format('d/m/Y H:i') }}</p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="https://wa.me/5562996625974" style="display: inline-block; background-color: #FE7F32; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                    Ver Mais Detalhes
                </a>
            </div>

            <p style="margin-top: 30px; font-size: 14px;">
                Se tiver qualquer dúvida, basta responder este e-mail ou chamar nossa equipe no WhatsApp.
            </p>

            <p style="font-size: 15px;"><strong style="color: #FE7F32;">Equipe Zabulon</strong></p>
        </div>
    </div>
</body>
</html>
