# Dogday Manager — Cauda Feliz

Sistema de gestão para creche e escola canina, construído com **Laravel 13**, **Vue 3**, **Inertia.js** e **Tailwind CSS**.

## Funcionalidades

### Portal do Manager (Staff)
- **Horário semanal** — grelha visual com todos os serviços do dia/semana, por hora
- **Pedidos de reserva** — aprovação/rejeição com notas, separados por estado
- **Gestão de pagamentos** — geração de referências MB / MBWay via Easypay, com sincronização automática de estado
- **Preços configuráveis** — todos os preços editáveis pelo manager sem tocar no código
- **Utilizadores** — criação e gestão de contas staff e donos
- **Clientes regulares/não regulares** — calculado automaticamente (sem serviço há +3 meses = não regular)

### Portal do Dono
- **Dashboard pessoal** — lista de cães, pedidos e estado (regular/não regular)
- **Novo pedido** — formulário com todos os serviços disponíveis
- **Pagamentos** — consulta de referências MB/MBWay, reenvio, alteração de método

### Serviços suportados
| Serviço | Preço base |
|---|---|
| Creche ATL (regular) | 12€/dia |
| Creche ATL (não regular) | 14€/dia |
| Estadia Hotel (regular) | 15€/noite |
| Estadia Hotel (não regular) | 17,50€/noite |
| Integração | 14€/sessão |
| Treino Individual | 17,50€ |
| Treino a Domicílio | 25€ |
| Treino em Grupo | 15€ |
| Avaliação Comportamental | 30€ |
| Pack Creche (4–15 sessões) | 40€–150€ |
| Pet Sitting | Sob consulta |
| Dog Walking | Sob consulta |
| Banho | Sob consulta |
| Pet Taxi (ida e volta) | 4€ |

---

## Tech Stack

- PHP 8.4 / Laravel 13
- Vue 3 + Inertia.js
- Tailwind CSS
- MySQL
- Vite
- Easypay v2 API (pagamentos)

---

## Requisitos

- PHP >= 8.4
- Composer
- Node.js >= 18
- MySQL

---

## Instalação local

### 1. Clonar o repositório

```bash
git clone https://github.com/Ricardo-Casal/dogday-manager.git
cd dogday-manager
```

### 2. Instalar dependências PHP

```bash
composer install
```

### 3. Instalar dependências Node

```bash
npm install
```

### 4. Configurar o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` com as credenciais da base de dados:

```env
DB_DATABASE=dogday_manager
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 5. Criar a base de dados

```sql
CREATE DATABASE dogday_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Correr migrações e seeds

```bash
php artisan migrate
php artisan db:seed --class=SettingsSeeder
```

### 7. Criar conta de staff

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
    'role'     => 'staff',
]);
```

### 8. (Opcional) Popular dados de teste

```bash
php artisan db:seed --class=TestDataSeeder
```

Cria 10 donos, 16 cães e 19 reservas aprovadas (incluindo clientes regulares e não regulares).

### 9. Iniciar servidores de desenvolvimento

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (assets frontend)
npm run dev
```

Disponível em [http://localhost:8000](http://localhost:8000).

---

## Pagamentos (Easypay)

Integração com [Easypay](https://www.easypay.pt/) para MB e MBWay. Por defeito corre em modo sandbox.

```env
EASYPAY_SANDBOX=true
EASYPAY_ACCOUNT_ID=your_sandbox_account_id
EASYPAY_API_KEY=your_sandbox_api_key
```

Sem credenciais configuradas, os pagamentos funcionam em **modo mock** (útil para desenvolvimento local).

---

## Email

Por defeito o driver de mail é `log` — os emails são escritos em `storage/logs/laravel.log`. Para usar um provider real, configurar as variáveis `MAIL_*` no `.env`.

---

## Deploy (Railway)

O projeto inclui `Dockerfile` e `start.sh` para deploy no [Railway](https://railway.app/). O `start.sh` aguarda a base de dados ficar disponível antes de correr as migrações automaticamente.
