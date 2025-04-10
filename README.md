# 📌 CRM Zabulon – Sistema de Atendimento com WhatsApp + Painel Kanban

## 💡 Visão Geral

O **CRM Zabulon** é um sistema de atendimento e organização de contatos via WhatsApp, com foco em **clínicas, empresas e atendimento ao público**. Ele tem como principal objetivo facilitar o controle de mensagens recebidas, identificar os clientes ativos e organizar o atendimento com **status visuais no estilo Kanban**.

As mensagens chegam via **API da Evolution** (integrada ao WhatsApp) e são armazenadas automaticamente no sistema. O painel Kanban exibe as conversas organizadas por status (ex: Aguardando, Bot, Em Atendimento), possibilitando o controle eficiente da fila.

---

## ✅ Funcionalidades já implementadas

- [x] Recebimento de mensagens via **Webhook** vindo do **n8n**
- [x] Salvamento de mensagens no banco com:
  - Número do cliente
  - Tipo de mensagem (texto, imagem, áudio, vídeo)
  - Data e hora de envio
  - Indicação se foi enviada pelo bot
  - Base64 convertida em arquivo físico (imagem/áudio/vídeo)
- [x] Ignora mensagens desnecessárias (reactions, stickers, etc.)
- [x] Painel Kanban responsivo com as últimas mensagens de cada número
- [x] Sistema de **drag and drop** para organizar os cards por status
- [x] Criação e exclusão de novos status dinâmicos
- [x] Atualização automática do Kanban a cada 5 segundos
- [x] Banco de dados em **PostgreSQL** hospedado no **Supabase**

---

## 🛠️ Tecnologias e ferramentas utilizadas

- **Laravel** (PHP)
- **Blade** (Frontend com HTML/CSS)
- **SortableJS** (arrastar e soltar os cards)
- **TailwindCSS** (estilização leve e rápida)
- **n8n** (para orquestrar as automações e conexão com a Evolution API)
- **Supabase PostgreSQL** (banco de dados)
- **Laragon** (ambiente local)
- **Postman** (testes manuais de API)
- **Ngrok** (exposição de webhook em ambiente local)

---

## 🗂️ Estrutura atual


---

## 🚧 Próximos passos (futuros)

- [ ] Adicionar visualização de histórico por número (modal ou página nova)
- [ ] Implementar sistema de **tempo real com Laravel Echo**
- [ ] Autenticação multiusuário
- [ ] Painel de chat manual (responder direto do CRM)
- [ ] Registro de atendentes e atribuição por cliente
- [ ] Filtro por status/data/perfil
- [ ] Logs de interação e auditoria

---

> Qualquer dúvida ou ideia para melhoria, é só chamar. 🚀
