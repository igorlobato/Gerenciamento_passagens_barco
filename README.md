<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sistema de Gerenciamento de Passagem de Barcos

# Equipe:
## Clevesson Robert Xavier de Oliveira
## Ronilson Gomes Do Amaral Neto
## Igor Lobato de Oliveira

## O trabalho tem como objetivo implementar 2 funções de segurança a fim de praticar métodos de segurança da informação, este trabalho irá usar na API métodos de segurança para autenticação e login, sendi divididos em:
- Registro com ativação de conta por e-mail
- Login com token
- Vai pondo aqui...

## Para usar a API basta usar os seguintes comandos

git clone https://github.com/igorlobato/Gerenciamento_passagens_barco.git

## Após isso deve-se executar um banco de dados Postgres no computador. O projeto tem configurado o .env para o docker-compose, então se for executá-lo de forma diferente precisa configurar o arquivo com as configurações do seu banco.

## Com o banco rodando, executa-se as migrations para criar as tabelas do banco de dados com:

php artisan migrate

## Se estiver usando o Docker do Sail

./vendor/bin/sail artisan migrate