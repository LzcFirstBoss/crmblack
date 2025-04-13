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
    nome VARCHAR(255) UNIQUE NOT NULL,
    cor VARCHAR(7) DEFAULT '#ffffff',
    fixo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




