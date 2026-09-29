<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). -->

# Biblioteca Digital de Robótica

## 1. Descrição do Projeto

A **Biblioteca Digital de Robótica** é uma plataforma interna da Secretaria Municipal de Educação para centralizar, organizar, versionar e disponibilizar materiais digitais de robótica educacional.

Professores poderão consultar e baixar os materiais sem alterar os arquivos originais, enquanto a equipe responsável poderá publicar novas versões mantendo o histórico dos conteúdos.

## 2. Tecnologias Utilizadas

* Laravel
* Laravel Jetstream
* Livewire
* Blade
* Tailwind CSS
* MySQL
* Git e GitHub

## 3. Requisitos

* PHP
* Composer
* Node.js e NPM
* MySQL
* Git

## 4. Instalação

```bash
git clone <https://github.com/souza-yan/biblioteca-digital.git>
cd <bibioteca-digital>

composer install
npm install

cp .env.example .env

php artisan key:generate
```

## 5. Configuração do `.env`

Configure as principais informações da aplicação no arquivo `.env`, principalmente:

```env
APP_NAME="Biblioteca Digital de Robótica"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblioteca_digital
DB_USERNAME=root
DB_PASSWORD=password
```

## 6. Configuração do Banco de Dados

Crie o banco de dados MySQL e configure suas credenciais no `.env`.

Depois, execute:

```bash
php artisan migrate
```

Caso existam seeders:

```bash
php artisan db:seed
```

Ou:

```bash
php artisan migrate --seed
```

## 7. Configuração do Armazenamento

Os arquivos da biblioteca serão armazenados utilizando o sistema de armazenamento do Laravel.

Após configurar o armazenamento, execute:

```bash
php artisan storage:link
```

Os arquivos oficiais devem ser armazenados de forma que os usuários possam realizar o download sem possuir permissão para modificar ou excluir os arquivos.

## 8. Migrations e Seeders

As **migrations** são utilizadas para criar e estruturar as tabelas necessárias ao funcionamento do sistema.

Os **seeders** são utilizados para inserir dados iniciais, como usuários, perfis e outros registros necessários para testes.

Para executar:

```bash
php artisan migrate --seed
```

## 9. Usuários de Teste

| Perfil        | E-mail                  | Senha   |
| ------------- | ----------------------- | ------- |
| Administrador | `admin@example.com`     | `senha` |
| Professor     | `professor@example.com` | `senha` |

> Os usuários e credenciais acima devem ser atualizados conforme os seeders utilizados no projeto.

## 10. Decisões Técnicas

### 10.1 Armazenamento dos Arquivos

Os arquivos são armazenados utilizando o sistema de **Storage do Laravel**, mantendo os arquivos separados dos dados registrados no banco de dados.

O banco armazena as informações necessárias para identificar e gerenciar cada material, enquanto o arquivo físico permanece no armazenamento configurado pela aplicação.

### 10.2 Perfis de Usuário

O sistema utiliza diferentes perfis para controlar as funcionalidades disponíveis.

* **Administrador:** responsável pelo gerenciamento dos materiais, usuários e versões.
* **Professor:** pode consultar e baixar os materiais disponibilizados, sem alterar os arquivos oficiais.

### 10.3 Versionamento

Cada nova atualização de um material é registrada como uma **nova versão**, mantendo as versões anteriores disponíveis no histórico.

Dessa forma, uma atualização não sobrescreve ou elimina o registro da versão anterior.

### 10.4 Autorização

A autorização é utilizada para garantir que cada perfil tenha acesso somente às funcionalidades permitidas.

As permissões são verificadas antes da execução das ações, impedindo que usuários sem autorização alterem, excluam ou gerenciem materiais e recursos administrativos.

## 11. Execução do Projeto

Para iniciar o ambiente de desenvolvimento:

```bash
php artisan serve
```

Em outro terminal:

```bash
npm run dev
```

A aplicação estará disponível no endereço configurado pelo servidor Laravel.
