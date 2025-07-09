<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Sistema de Gerenciamento de Passagem de Barcos

## Descrição

O Sistema de Gerenciamento de Passagens de Barco é um sistema web desenvolvido treinar a plicação dos cinco princípios de segurança da informação: Autenticidade, Confidencialidade, Não Repúdio, Disponibilidade e Integridade. Devido ao ambiente local de desenvolvimento, a disponibilidade não foi testada.

## Desenvolvedor:
- Igor Lobato de Oliveira

## Funcionalidades de Segurança
## 1. Autenticidade
 - <b>Registro com Validação:</b> Criação de contas com validação de e-mail (formato válido) e senha (mínimo de 8 caracteres, letras maíscula e caracter especial). Após o registro, o usuário recebe um e-mail com um link de ativação para validar a conta, diferenciando contas ativas de cadastros inativos.
 - <b>Login com Token JWT:</b> Autenticação baseada em e-mail e senha, gerando um token JWT (JSON Web Token) após validação bem-sucedida. O token é usado para autenticar requisições em rotas protegidas.
- <b>Autenticação em Dois Fatores (2FA):</b> Após o login, o usuário recebe um código de verificação por e-mail, válido por 10 minutos, para confirmar a identidade, reforçando a autenticidade.

## 2. Confidencialidade
 - <b>HTTPS em Todo o Ambiente:</b>  Tanto o backend (Laravel com Sail) quanto o frontend (React com Vite) rodam em HTTPS, garantindo que todas as comunicações entre cliente e servidor sejam criptografadas.
    <b>Backend:</b> Configurado com ryoluo/sail-ssl, que usa certificados autossinados para HTTPS na porta 443 (https://localhost).
    <b>Frontend:</b> Configurado com certificados confiáveis gerados pelo mkcert, servidos na porta 3000 (https://localhost:3000).
- <b>Proteção de Dados Sensíveis:</b> Senhas são armazenadas usando hash (Bcrypt) no banco de dados.

## 3. Não Repúdio
- <b>Registro de Atividades (Logging):</b> Todas as ações dos usuários (login, logout, tentativas de login mal-sucedidas, verificação 2FA, etc.) são registradas em logs detalhados, incluindo informações como IP, Id, rotas, garantindo rastreabilidade.
- <b>Rate Limiting:</b> Limitação de tentativas de login por IP (máximo de 5 tentativas em 15 minutos) para prevenir ataques de força bruta.

## 4. Integridade
- <b>Tokens Temporários:</b> Links de ativação de conta e redefinição de senha, bem como códigos 2FA, têm validade limitada, garantindo que tokens não sejam reutilizados.
- <b>Proteção de Rotas:</b> Endpoints da API são protegidos por middleware de autenticação (auth:api), garantindo que apenas usuários autenticados com tokens válidos acessem recursos sensíveis.

## Arquivos importantes:
- app/Http/Controllers/Api/*.php: Controllers responsáveis por implementar a lógica.
- app/Http/Notifications/*.php: Notificações.
- app/Http/Middleware/LogActivity.php: Middleware para registrar atividades dos usuários em logs.
- routes/api.php: Define rotas da API, com proteção por middleware de autenticação e permissões.



config/cors.php: Configuração de CORS para permitir apenas requisições do frontend 

## A implementação das funcionalidades pode ser encontrada nos seguintes arquivos:
- App/Http/Controllers/Api/AuthController.php | Funcionalidades relacionadas a autenticação do usuário (token, login, logout, envio de e-mail, redefinição de senha, token de e-mail com tempo limitado)
- App/Http/Notifications/ActivateAccountNotification.php e ResetPasswordNotification | Criação dos e-mails e seus links 
- Routes/api.php | Definicação das rotas da API e proteção por token
- App/Middlewate/LogAcitivity | Criação do middlewate de log de atividades do sistema.

## Pré-requisitos
- Para rodar a API localmente, você precisa de:
- PHP
- Composer
- PostgreSQL
- Docker com Laravel Sail
- Git

# Para usar a API basta executar os seguintes passos: 

## Clonar o repositório
- git clone https://github.com/igorlobato/Gerenciamento_passagens_barco.git 
- cd Gerenciamento_passagens_barco

## Instalar Dependências
- composer install

## Usar Docker com Laravel Sail:
- Instale o Laravel Sail: composer require laravel/sail --dev
- Inicie os contêineres: ./vendor/bin/sail up -d

## Configure o arquivo .env:
- Copie o arquivo .env.example para .env: cp .env.example .env
- Edite o .env com as configurações do seu banco.

## Executar as Migrations
- php artisan migrate
- ./vendor/bin/sail artisan migrate