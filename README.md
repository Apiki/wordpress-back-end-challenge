# API usando WP_Rest_API 
Api está com as funções básicas

* GET - posts
* GET - posts/favoritos
* GET - posts/{id}
* POST - posts/add/{id}
* DELETE = posts/del/{id}

** Pendências **
1 - Verificar se o usuário está logado;
2 - Criar a tabela via instalação do plugin;
3 - Orientação a Objetos;
4 - Verificar as funções para evitar insersão de registros duplicados;
5 - Melhora as mensagens de retorno.

# WordPress Back-end Challenge

Desafio para os futuros programadores back-end em WordPress da Apiki.

## Introdução

Desenvolva um Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a [WP REST API](https://developer.wordpress.org/rest-api/).

**Especifícações**:

* Possibilidade de favoritar e desfavoritar um post;
* Persistir os dados em uma [tabela a parte](https://codex.wordpress.org/Creating_Tables_with_Plugins);

## Instruções

1. Efetue o fork deste repositório e crie um branch com o seu nome e sobrenome. (exemplo: fulano-dasilva)
2. Após finalizar o desafio, crie um Pull Request.
3. Aguarde algum contribuidor realizar o code review.

## Pré-requisitos

* PHP >= 5.6
* Orientado a objetos

## Dúvidas

Em caso de dúvidas, crie uma issue.
