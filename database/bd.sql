CREATE TABLE mensagens (
    id BIGSERIAL PRIMARY KEY,
    numero_cliente VARCHAR(20) NOT NULL,
    tipo_de_mensagem VARCHAR(50) NOT NULL,
    mensagem_enviada TEXT,
    base64 TEXT,
    data_e_hora_envio TIMESTAMP,
    enviado_por_mim BOOLEAN DEFAULT FALSE,
    bot BOOLEAN DEFAULT FALSE,
    usuario_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_id text,
    
    CONSTRAINT fk_usuario FOREIGN KEY (usuario_id)
        REFERENCES users(id)
        ON DELETE SET NULL
);

CREATE TABLE status (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cor VARCHAR(20), -- opcional (ex: bg-green-100)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO status (nome, cor) VALUES
('aguardando', 'bg-yellow-100'),
('bot', 'bg-blue-100'),
('atendimento', 'bg-green-100');


