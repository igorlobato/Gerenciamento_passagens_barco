<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sistema de Gerenciamento de Passagem de Barcos

# Equipe:
- Clevesson Robert Xavier de Oliveira
- Ronilson Gomes Do Amaral Neto
- Igor Lobato de Oliveira

## O trabalho tem como objetivo implementar 2 funções de segurança a fim de praticar métodos de segurança da informação, este trabalho irá usar na API métodos de segurança para autenticação e login, sendi divididos em:
- Registro com ativação de conta por e-mail
- Login com token
- Vai pondo aqui...

## Funcionalidades de Segurança
- Registro com Validação: Criação de contas com validação de e-mail, CPF, número de telefone e senha (mínimo 8 caracteres). Ativação de conta por e-mail (em desenvolvimento).
- Login com Token JWT: Autenticação com e-mail e senha, retornando um token JWT para acesso protegido.
- Logout Seguro: Invalidação do token JWT ao deslogar.
- Validação de Entrada: Proteção contra injeções e dados inválidos usando regras do Laravel.
- Proteção de Rotas: Endpoints protegidos com middleware de autenticação.

## Pré-requisitos
- Para rodar a API localmente, você precisa de:
- PHP 8.1+
- Composer
- PostgreSQL 13+
- Opcional: Docker com Laravel Sail
- Git
- O front-end do projeto configurado em http://localhost:8001 ou porta disponível.

# Para usar a API basta executar os seguintes passos: 

## Clonar o repositório
- git clone https://github.com/igorlobato/Gerenciamento_passagens_barco.git 
- cd Gerenciamento_passagens_barco

## Instalar Dependências
- composer install

## Configurar o Banco de Dados
- Instale o PostgreSQL e execute.
- Crie um banco de dados chamado passagens_barco.

## Configure o arquivo .env:
- Copie o arquivo .env.example para .env: cp .env.example .env
- Edite o .env com as configurações do seu banco.

## (Opcional) Usar Docker com Laravel Sail:
- Instale o Laravel Sail: composer require laravel/sail --dev
- Inicie os contêineres: ./vendor/bin/sail up -d

## Executar as Migrations
- php artisan migrate
- ./vendor/bin/sail artisan migrate

##  Iniciar o Servidor (Se não estiver usando docker)
- php artisan serve