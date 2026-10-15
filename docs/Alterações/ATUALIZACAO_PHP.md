# Atualização para PHP 8.4 + Laravel 12

## O que foi feito

- Projeto atualizado de **Laravel 9 / PHP 8.2** para **Laravel 12 / PHP ^8.2** (rodando em **PHP 8.4.12**).
- Estrutura nova do Laravel 11+: `bootstrap/app.php`, `bootstrap/providers.php`.
- Dependências atualizadas (Sanctum 4, DomPDF 3, PHPUnit 11, Collision 8).

## PHP em uso

| Uso | Caminho | Versão |
|-----|---------|--------|
| CLI / `artisan` / Composer | `C:\php\php.exe` | **8.4.12** |
| Apache do XAMPP (se abrir via `localhost/...`) | `C:\xampp\php` | 8.2.12 |

**Importante:** para este projeto, use sempre o PHP 8.4:

```powershell
$env:Path = "C:\php;" + $env:Path
php -v
php artisan serve
```

Ou dê dois cliques em `serve.bat` na pasta do projeto.

## Extensões habilitadas no PHP 8.4

No arquivo `C:\php\php.ini`:

- `extension=mbstring`
- `extension=pdo_pgsql`
- `extension=pgsql`

## Como rodar

```powershell
cd "c:\xampp\htdocs\Projeto Oficina\sistema-oficina"
$env:Path = "C:\php;" + $env:Path
php artisan serve
```

Acesse: http://127.0.0.1:8000

Login admin: `admin@oficina.com` / `admin123`

## Se o Composer usar o PHP errado

```powershell
$env:Path = "C:\php;" + $env:Path
composer --version
composer update
```

## Opcional: PATH permanente

1. Painel de Controle → Sistema → Variáveis de Ambiente
2. Em **Path**, coloque `C:\php` **acima** de `C:\xampp\php`
3. Abra um novo terminal e confira com `php -v`
