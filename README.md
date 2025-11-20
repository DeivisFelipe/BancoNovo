# 🏦 BancoNovo - Sistema Bancário com WebSocket

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red?style=flat&logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green?style=flat&logo=vue.js)](https://vuejs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue?style=flat&logo=postgresql)](https://postgresql.org)
[![WebSocket](https://img.shields.io/badge/WebSocket-Reverb-orange?style=flat)](https://reverb.laravel.com)

> Sistema bancário moderno desenvolvido com Laravel, Vue.js e WebSocket para notificações em tempo real.

---

## 📋 Sobre o Projeto

**BancoNovo** é um sistema bancário completo que permite:

-   ✅ **Cadastro e Autenticação** de usuários
-   💰 **Depósitos** ilimitados na própria conta
-   💸 **Transferências** entre usuários com validação de saldo
-   🔔 **Notificações em tempo real** via WebSocket (Laravel Reverb)
-   🔊 **Alertas sonoros** para novas transações
-   🔍 **Busca inteligente** de destinatários (autocomplete)
-   📊 **Histórico de transações** com filtros
-   🔒 **Segurança robusta** com transações atômicas e lock pessimista

### 🎯 Diferenciais Técnicos

-   **Arquitetura baseada em transações**: Sem campo de saldo, calculado dinamicamente
-   **WebSocket nativo**: Laravel Reverb para comunicação bidirecional
-   **Proteção contra Race Conditions**: Lock pessimista (`FOR UPDATE`)
-   **Interface moderna**: Vuetify 3 + Material Design
-   **Real-time**: Notificações instantâneas com som
-   **Docker Ready**: Ambiente completo com um comando

---

## 🛠️ Tecnologias Utilizadas

### Backend

-   **Laravel 12.0** - Framework PHP
-   **PostgreSQL 16** - Banco de dados relacional
-   **Laravel Reverb** - WebSocket server nativo
-   **Laravel Sanctum** - Autenticação de sessão
-   **Inertia.js 2.0** - SSR sem API

### Frontend

-   **Vue.js 3.5** - Framework JavaScript
-   **Vuetify 3** - Material Design Components
-   **Vite 6** - Build tool
-   **Laravel Echo** - Cliente WebSocket
-   **Pusher JS** - Protocolo WebSocket
-   **SweetAlert2** - Notificações elegantes

### DevOps

-   **Docker** - Containerização
-   **Docker Compose** - Orquestração de containers
-   **pnpm** - Gerenciador de pacotes

---

## 🚀 Como Executar

### 📦 Opção 1: Docker (Recomendado)

#### Pré-requisitos

-   Docker Desktop instalado
-   Docker Compose

#### Passos

1. **Clone o repositório**

```bash
git clone https://github.com/DeivisFelipe/BancoNovo.git
cd BancoNovo
```

2. **Inicie os containers**

**Windows:**

```bash
docker-start.bat
```

**Linux/Mac:**

```bash
chmod +x docker-start.sh
./docker-start.sh
```

3. **Acesse o sistema**

-   **Aplicação**: http://localhost:8000
-   **pgAdmin**: http://localhost:5050 (admin@banconovo.com / admin123)

#### Serviços Docker

| Serviço       | Porta | Descrição                   |
| ------------- | ----- | --------------------------- |
| App (Laravel) | 8000  | Aplicação principal         |
| Vite          | 5173  | Dev server frontend         |
| Reverb        | 9000  | WebSocket server            |
| PostgreSQL    | 5432  | Banco de dados              |
| pgAdmin       | 5050  | Interface web do PostgreSQL |

---

### 💻 Opção 2: Execução Nativa

#### Pré-requisitos

-   PHP 8.3+
-   Composer 2.7+
-   Node.js 20+
-   pnpm
-   PostgreSQL 16+

#### Passos

1. **Clone o repositório**

```bash
git clone https://github.com/DeivisFelipe/BancoNovo.git
cd BancoNovo
```

2. **Instale as dependências do PHP**

```bash
composer install
```

3. **Instale as dependências do Node.js**

```bash
pnpm install
```

4. **Configure o ambiente**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados no `.env`**

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=banconovo
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. **Configure o Reverb no `.env`**

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=500690
REVERB_APP_KEY=jrspa1ufjb2ug6be2aaf
REVERB_APP_SECRET=yd0fabvvdnbsjumh5gaq
REVERB_HOST="localhost"
REVERB_PORT=9000
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=9000
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

7. **Execute as migrations**

```bash
php artisan migrate --seed
```

8. **Inicie os servidores (3 terminais)**

**Terminal 1 - Laravel:**

```bash
php artisan serve
```

**Terminal 2 - Vite:**

```bash
pnpm run dev
```

**Terminal 3 - Reverb (WebSocket):**

```bash
php artisan reverb:start
```

9. **Acesse o sistema**

-   http://localhost:8000

---

## 👤 Usuários de Teste

Após executar as migrations com seed, você terá 3 usuários disponíveis:

| Nome         | Email             | Conta  | Senha    |
| ------------ | ----------------- | ------ | -------- |
| Test User    | test@example.com  | 000001 | password |
| João Silva   | joao@example.com  | 000002 | password |
| Maria Santos | maria@example.com | 000003 | password |

---

## 🎮 Como Usar

1. **Faça login** com um dos usuários de teste
2. **Faça um depósito** para ter saldo inicial
3. **Abra em outra aba** com outro usuário
4. **Faça uma transferência** entre os usuários
5. **Observe** a notificação em tempo real aparecendo automaticamente! 🎉

---

## 🏗️ Arquitetura do Sistema

### Modelo de Dados

```
┌─────────────┐         ┌──────────────┐
│    Users    │         │ Transactions │
├─────────────┤         ├──────────────┤
│ id          │◄────┐   │ id           │
│ name        │     │   │ from_user_id │─┐
│ email       │     └───│ to_user_id   │◄┘
│ password    │         │ amount       │
│ account_num │         │ type         │
│ created_at  │         │ description  │
│ updated_at  │         │ created_at   │
└─────────────┘         └──────────────┘
```

### Fluxo de Transferência

```
┌──────────┐      ┌───────────┐      ┌──────────┐
│ Frontend │─────▶│  Laravel  │─────▶│   DB     │
│  (Vue)   │◄─────│Controller │◄─────│(PostgreSQL)│
└──────────┘      └───────────┘      └──────────┘
     │                  │
     │            ┌─────▼─────┐
     └───────────▶│  Reverb   │
        WebSocket │ (WS:9000) │
                  └───────────┘
```

### Segurança Implementada

1. **Lock Pessimista** - Previne race conditions

    ```php
    User::where('id', $userId)->lockForUpdate()->first();
    ```

2. **Transações Atômicas** - Garantia de consistência

    ```php
    DB::beginTransaction();
    // operações
    DB::commit();
    ```

3. **Validação em Camadas** - Frontend + Backend + Database
4. **Constraints** - Amount > 0 no PostgreSQL
5. **Mass Assignment Protection** - `$guarded` no modelo
6. **CSRF Protection** - Token em todas as requisições

---

## 📂 Estrutura do Projeto

```
BancoNovo/
├── app/
│   ├── Events/
│   │   └── TransactionReceived.php    # Evento WebSocket
│   ├── Http/Controllers/
│   │   ├── AuthController.php         # Login/Registro
│   │   └── TransactionController.php  # Transações
│   └── Models/
│       ├── User.php                    # Modelo de usuário
│       └── Transaction.php             # Modelo de transação
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   └── 0001_01_01_000003_create_transactions_table.php
│   └── seeders/
│       └── DatabaseSeeder.php          # Seed dos usuários
├── resources/
│   ├── js/
│   │   ├── app.js                      # Bootstrap + Echo
│   │   └── pages/
│   │       ├── Dashboard.vue           # Dashboard principal
│   │       ├── Login.vue               # Tela de login
│   │       └── Register.vue            # Tela de registro
│   └── views/
│       └── app.blade.php               # Template base
├── routes/
│   ├── web.php                         # Rotas web
│   └── channels.php                    # Canais WebSocket
├── docker-compose.yml                  # Orquestração Docker
├── Dockerfile                          # Imagem PHP/Laravel
├── Dockerfile.vite                     # Imagem Node/Vite
└── .env.docker                         # Variáveis Docker
```

---

## 🧪 Testes

### Testar Notificações em Tempo Real

1. Abra **duas janelas** do navegador (ou use navegador + anônimo)
2. Faça login com usuários **diferentes** em cada janela
3. Faça uma **transferência** de uma conta para outra
4. Observe a **notificação aparecer instantaneamente** na conta receptora! 🎯

### Verificar WebSocket

Abra o console do navegador (F12) e verifique:

```
🚀 Iniciando conexão WebSocket...
📡 Conectado ao canal: user.1
✅ Listener registrado para .transaction.received
```

---

## 🐛 Troubleshooting

### WebSocket não conecta

**Solução 1: Verificar se o Reverb está rodando**

```bash
# Docker
docker-compose logs reverb

# Nativo
# Verificar se o terminal com "php artisan reverb:start" está rodando
```

**Solução 2: Limpar cache**

```bash
php artisan config:clear
php artisan cache:clear
```

### Erro de CORS

Certifique-se que as variáveis `VITE_REVERB_*` estão corretas no `.env`

### Transações não aparecem

Execute as migrations novamente:

```bash
php artisan migrate:fresh --seed
```

---

## 📝 Comandos Úteis

### Docker

```bash
# Ver logs
docker-compose logs -f

# Reiniciar serviços
docker-compose restart

# Parar tudo
docker-compose down

# Entrar no container
docker-compose exec app bash
```

### Laravel

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# WebSocket
php artisan reverb:start
php artisan reverb:restart
```

### Frontend

```bash
# Dev
pnpm run dev

# Build
pnpm run build

# Preview
pnpm run preview
```

---

## 🔮 Próximas Funcionalidades

-   [ ] Sistema de PIX
-   [ ] Boletos e pagamentos
-   [ ] Extrato em PDF
-   [ ] Limite de transferência diário
-   [ ] Autenticação 2FA
-   [ ] Histórico de login
-   [ ] Dashboard administrativo
-   [ ] Relatórios gerenciais
-   [ ] API REST para mobile
-   [ ] Testes automatizados

---

## 📄 Licença

Este projeto foi desenvolvido como parte de um processo seletivo.

---

## 👨‍💻 Autor

**Deivis Felipe**

-   GitHub: [@DeivisFelipe](https://github.com/DeivisFelipe)
-   LinkedIn: [/in/deivisfelipe](https://linkedin.com/in/deivisfelipe)

---

## 🙏 Agradecimentos

Projeto desenvolvido para demonstrar habilidades em:

-   Desenvolvimento Full Stack
-   WebSocket e Real-time
-   Arquitetura de Software
-   DevOps com Docker
-   Boas práticas de segurança

---

**⭐ Se você gostou deste projeto, considere dar uma estrela!**
