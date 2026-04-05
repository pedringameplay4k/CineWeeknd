<div align="center">

# 🎬 CineWeeknd

**Plataforma de cinema digital e presencial — desenvolvida em PHP puro com visual neon 80s**

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/Licença-MIT-green?style=for-the-badge)](LICENSE)

*Compre ingressos para sessões digitais ou presenciais, escolha seu assento e receba seu comprovante por e-mail.*

</div>

---

## ✨ Funcionalidades

### 🎥 Para o Usuário
- **Catálogo completo** com 55+ filmes, pôsteres via TMDB, filtros por gênero e categoria
- **Dois modos de sessão:**
  - 💻 **Digital** — assista em casa, ingresso a partir de R$ 2,90
  - 🎭 **Presencial** — escolha o shopping e horário, a partir de R$ 5,90
- **7 shoppings de Brasília** — Conjunto Nacional, ParkShopping, Brasília Shopping, Iguatemi, Pátio Brasil, Taguatinga Shopping e JK Shopping
- **Seletor visual de assentos** — mapa interativo com poltronas disponíveis, ocupadas e selecionadas
- **Carrinho completo** — combos de pipoca, cupons de desconto, ajuste de quantidade
- **Comprovante em tela e por e-mail** — com todos os itens, assento e valores
- **Meus ingressos** — acesso por token único com countdown para a sessão
- **Favoritos**, avaliações e perfil do usuário

### 🛠️ Para o Admin
- **Painel administrativo** completo em `/admin`
- **Importação de filmes via TMDB** — busca, seleciona e importa com pôster e trailer automático
- **Busca automática de pôsteres** em lote para filmes sem imagem
- **Gerenciamento** de filmes, combos, cupons e pedidos

### ⚙️ Técnico
- **Auto-instalação** — na primeira visita, cria o banco, tabelas e popula tudo automaticamente
- **MVC puro em PHP** — sem frameworks, sem Composer obrigatório
- **AJAX** em todo o carrinho — sem recarregar página
- **CSRF protection** em todos os formulários
- **E-mail transacional** via Resend API

---

## 🚀 Instalação

### Pré-requisitos
- XAMPP (ou equivalente) com **PHP 8.1+** e **MySQL 8.0+**
- Conta gratuita no [Resend](https://resend.com) (e-mail)
- Conta gratuita no [TMDB](https://themoviedb.org/settings/api) (pôsteres)

### Passo a passo

**1. Clone o repositório**
```bash
git clone https://github.com/seu-usuario/CineWeeknd.git
cd CineWeeknd
```

**2. Copie para o htdocs**
```bash
# Windows (XAMPP)
xcopy /E /I CineWeeknd C:\xampp\htdocs\CineWeeknd_final

# Linux/Mac
cp -r CineWeeknd /opt/lampp/htdocs/CineWeeknd_final
```

**3. Configure as credenciais**

Edite `config/config.php`:
```php
define('DB_HOST',     'localhost');
define('DB_NAME',     'cineweeknd');
define('DB_USER',     'root');
define('DB_PASS',     '');

define('APP_URL',     'http://localhost/CineWeeknd_final/public');
define('TMDB_API_KEY','sua_chave_tmdb_aqui');
define('RESEND_API_KEY','sua_chave_resend_aqui');
```

**4. Acesse o site**
```
http://localhost/CineWeeknd_final
```
> Na primeira visita, o **Auto-Installer** cria o banco, as tabelas e insere todos os filmes automaticamente. Aguarde a barra de progresso concluir (~10 segundos).

**5. Acesse o painel admin**
```
http://localhost/CineWeeknd_final/public/admin
```
> Credenciais padrão: `admin@cineweeknd.com` / `admin123`
> 
> Recomendado: atualize para seu próprio e-mail via phpMyAdmin:
> ```sql
> UPDATE users SET is_admin = 1 WHERE email = 'seu@email.com';
> ```

---

## 📁 Estrutura do Projeto

```
CineWeeknd_final/
├── config/
│   ├── config.php          # Configurações gerais e constantes
│   ├── database.php        # Conexão PDO singleton
│   └── AutoInstaller.php   # Instalação automática na 1ª visita
├── public/
│   ├── index.php           # Entry point
│   └── assets/
│       ├── css/main.css    # Estilos globais (tema neon 80s)
│       └── js/main.js      # AJAX do carrinho, favoritos, filtros
├── routes/
│   └── web.php             # Roteamento manual (sem framework)
├── src/
│   ├── Controllers/        # AuthController, MovieController, CartController...
│   ├── Models/             # MovieModel, OrderModel, ScreeningModel, VenueModel...
│   ├── Services/           # EmailService (Resend), TMDBService
│   └── Views/
│       ├── layouts/        # header.php, footer.php
│       ├── pages/          # movies, cart, checkout, order-detail, admin...
│       └── components/     # movie-card.php
├── database/
│   └── schema.sql          # SQL completo (alternativa ao Auto-Installer)
└── storage/
    └── .htaccess           # Protege arquivos internos
```

---

## 🗄️ Banco de Dados

As tabelas principais são criadas automaticamente na primeira visita. Se preferir importar manualmente, use `database/schema.sql` no phpMyAdmin.

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários e administradores |
| `movies` | Catálogo de filmes com preços digital e presencial |
| `genres` | Gêneros dos filmes |
| `venues` | Shoppings de Brasília |
| `screenings` | Sessões (digital ou presencial) por venue |
| `access_tokens` | Tokens de acesso aos ingressos digitais |
| `orders` | Pedidos realizados |
| `order_items` | Itens dos pedidos (com assento) |
| `combos` | Combos de pipoca e bebida |
| `coupons` | Cupons de desconto |
| `favorites` | Filmes favoritos por usuário |
| `reviews` | Avaliações dos usuários |

---

## 🔑 APIs Utilizadas

| API | Uso | Plano gratuito |
|-----|-----|---------------|
| [TMDB](https://themoviedb.org/settings/api) | Pôsteres e metadados dos filmes | ✅ Sim |
| [Resend](https://resend.com) | E-mails transacionais | ✅ 3.000/mês |

> ⚠️ **Resend:** no plano gratuito, e-mails só são enviados para o seu próprio endereço cadastrado. Para enviar a qualquer destinatário, verifique um domínio em [resend.com/domains](https://resend.com/domains).

---

## 🛒 Fluxo de Compra

```
1. Catálogo → escolhe o filme
2. "Ver Sessões" → escolhe Digital ou Presencial
      Digital  → escolhe horário → adiciona ao carrinho (R$ 2,90)
      Presencial → filtra shopping → escolhe horário → seleciona assento (R$ 5,90)
3. Carrinho → adiciona combos (só presencial) → aplica cupom
4. Checkout → pagamento (PIX ou cartão simulado)
5. Comprovante em tela + e-mail com todos os detalhes e assento
6. "Meus Ingressos" → acesso ao player ou QR code para o cinema
```

---

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

<div align="center">

Desenvolvido com ☕ e muito PHP por **Pedro Camargo**

</div>
