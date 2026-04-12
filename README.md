# NomadeApp 🏢
**Sistema de Agendamento para Condomínios** – Lavanderia & Churrasqueira

---

## Estrutura de Pastas

```
condominio/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── LavanderiaController.php
│   │   ├── ChurrasqueiraController.php
│   │   └── AdminController.php
│   ├── models/
│   │   ├── MoradorModel.php
│   │   ├── LavanderiaModel.php
│   │   └── ChurrasqueiraModel.php
│   └── views/
│       ├── auth/login.php
│       ├── dashboard/ (index.php + _layout_top/bottom)
│       ├── laundry/index.php
│       ├── bbq/index.php
│       └── admin/index.php
├── config/
│   ├── database.php       ← Configurações de conexão
│   └── Database.php       ← Singleton PDO
├── public/                ← Document root do servidor
│   ├── index.php          ← Front controller (router)
│   ├── css/style.css
│   ├── js/app.js
│   └── .htaccess
└── sql/
    └── schema.sql         ← Script do banco de dados
```

---

## Instalação

### 1. Banco de Dados

```bash
mysql -u root -p < sql/schema.sql
```

Ou importe o arquivo `sql/schema.sql` utilizando se SGBD favorito.

### 2. Configuração

Edite `config/database.php` com suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'condominio_agendamento');
define('DB_USER', 'root');      // seu usuário MySQL
define('DB_PASS', '');          // sua senha MySQL
```

### 3. Servidor Web

**Apache** – aponte o `DocumentRoot` para a pasta `public/`:

```apache
<VirtualHost *:80>
    ServerName condo.local
    DocumentRoot /var/www/condominio/public
    <Directory /var/www/condominio/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**PHP Built-in (desenvolvimento)**:

```bash
cd public
php -S localhost:8000
```

Acesse: `http://localhost:8000`

---

## Credenciais Padrão

| Tipo       | Usuário / Apartamento | Senha     |
|------------|-----------------------|-----------|
| Morador    | Apto `101`            | admin123  |
| Morador    | Apto `102`            | admin123  |
| Morador    | Apto `201`            | admin123  |
| Admin      | `admin`               | admin123  |

> ⚠️ **Troque as senhas em produção!**

---

## Regras de Negócio

| Recurso        | Limite              | Período  |
|----------------|---------------------|----------|
| Lavanderia     | 4 horas             | Semanal (seg–dom) |
| Churrasqueira  | 2 usos              | Mensal   |

- Slots de lavanderia: intervalos de **30 em 30 minutos**
- Churrasqueira: turnos **Manhã** (08–12), **Tarde** (12–17), **Noite** (17–22)
- Cancelamentos devolvem créditos automaticamente
- Apenas agendamentos futuros podem ser cancelados

---

## Requisitos

- PHP **8.0+** com extensões: `pdo`, `pdo_mysql`, `mbstring`
- MySQL **5.7+** ou MariaDB **10.3+**
- Apache com `mod_rewrite` habilitado (ou Nginx equivalente)

---

## Personalização

Para alterar os limites de uso, edite diretamente a tabela `configuracoes`:

```sql
UPDATE configuracoes SET valor = '6' WHERE chave = 'horas_lavanderia_semana';
UPDATE configuracoes SET valor = '3' WHERE chave = 'usos_churrasqueira_mes';
UPDATE configuracoes SET valor = '08:00' WHERE chave = 'horario_abertura';
UPDATE configuracoes SET valor = '22:00' WHERE chave = 'horario_fechamento';
```
