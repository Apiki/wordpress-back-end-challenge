# WordPress Back-end Challenge

Desafio back-end em WordPress da Apiki.

## Introdução

Foi desenvolvido um Plugin em WordPress que implementa a funcionalidade de favoritar posts para usuários logados usando a [WP REST API](https://developer.wordpress.org/rest-api/).

Foi criado uma tabela customizada para armazenar as informações do post favoritado e do usuário que favoritou.

Para favoritar o usuário necessita estar logado no WordPress. Para isso foi utilizado a biblioteca [PHP-JWT](https://github.com/firebase/php-jwt) do Firebase para criar e gerenciar o JWT Token do usuário.

## Instruções

1. Efetue o clone do repositório dentro do diretórios de `/wp-content/plugins/` do Wordpress: `git clone https://github.com/luispaiva/wordpress-back-end-challenge`
2. Troque de branch: `git checkout luis-paiva`;
3. Instale as dependências utilizando composer: `composer install`;
4. Ative o plugin `WordPress Back-end Challenge` no painel do WordPress;
5. Utilizando uma ferramenta que faz requisições para `REST API` como por exemplo o [Insominia](https://insomnia.rest/) acesse as seguintes URLs:

	* Para autenticar no WordPress faça uma requisição POST para o endpoint abaixo:

		```
		POST: http://localhost/wp-json/apiki/challenge/login
		```

		```json
		Body:
		{
			"username": "USERNAME",
			"password": "PASSWORD"
		}
		```

		```json
		Response:
		{
			"token": "SEU.JWT.TOKEN",
			"user_name": "fulano",
			"user_email": "admin@admin.com.br",
			"user_registered": "0000-00-00 00:00:00"
		}
		```

	* Para favoritar um post faça uma requisição POST para o endpoint abaixo:
		```
		POST: http://localhost/wp-json/apiki/challenge/favorite/1
		```
		```json
		Authorization:
		{
			"Authorization": "Bearer token",
		}
		```
	*Obs.: Troque a **URL** e os **dados** de acesso de acordo com a sua instalação do WordPress.*
6. Foram utilizado linter para padronização de código e de segurança. Para executa-los você pode rodar os seguintes comandos no terminal:
```
composer lint
composer lint:fix
composer security
```
Ou todos juntos utilizando o comando:
```
composer check
```
## Pré-requisitos

* PHP >= 5.6
