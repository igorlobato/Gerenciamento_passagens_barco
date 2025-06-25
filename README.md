<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sistema de Gerenciamento de Passagem de Barcos

# Dev
- Igor Lobato de Oliveira

## O trabalho tem como objetivo treinar a aplicação dos 5 princípios de segurança da informação: Autenticidade; Confidenciabilidade; Não repúdio; Disponibilidade, Integridade. Devido a limitações do sistema funcionar em ambiente local, não será possível testar a disponíbilidade.

## Funcionalidades de Segurança
- Registro com Validação: Criação de contas com validação de e-mail e senha (mínimo 8 caracteres). Ativação de conta por e-mail.
- Login com Token JWT: Autenticação com e-mail e senha, retornando um token JWT para acesso protegido.
- Logout Seguro: Invalidação do token JWT ao deslogar.
- Validação de Entrada: Proteção contra injeções e dados inválidos usando regras do Laravel.
- Proteção de Rotas: Endpoints protegidos com middleware de autenticação verificando o token.
- Registro de atividade: A atividade dos usuários são registradas em um log exclusivo.

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