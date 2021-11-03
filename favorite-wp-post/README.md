# Usagem

## Consultar se o post foi favoritado pelo usuário

```
GET /wp-json/favorite-wp-post/v1/{id}
```

## Favoritar post

```
POST /wp-json/favorite-wp-post/v1/{id}
```

## Desfavoritar post

```
DELETE /wp-json/favorite-wp-post/v1/{id}
```

O plugin ainda não esta configurado para implementar atualizações.
Ao desinstalar, a tabela a parte associada ao plugin é apagada.

# Testando a API com um cliente REST API

Neste exemplo foi usado o cliente [CURL](https://curl.se/) por linha de comandos.
O ambiente foi em uma instalação local de wordpress linux/apache/php 7.2/mysql.
Por defeito, a autenticação a Wordpress é do tipo _Basic authentication_ estabelecida por cookies.
Sugestão: iniciar sessão por navegador e pegar os cookies de sessão em `Ferramentas para desenvolvedores / Aplicação / Almacenamento / Cookies`

* Formato de cookie: `wordpress_logged_in_<hash>`
* Formato de valor: `<seu_usuario><outra_hash>`

## Consultar se o post foi favoritado pelo usuário

```sh
curl -b "<cookie>=<valor>" -v http://localhost/wp-json/favorite-wp-post/v1/<id>
```

## Favoritar post:

```sh
curl -X POST -b "<cookie>=<valor>" -v http://localhost/wp-json/favorite-wp-post/v1/<id>
```

## Desfavoritar post:

```sh
curl -X DELETE -b "<cookie>=<valor>" -v http://localhost/wp-json/favorite-wp-post/v1/<id>
```

# P.D.
Estas linhas foram escritas para informar como o plugin funciona e como é instalado além de explicar para meu EU do futuro como é que eu fiz este desafio ✍🏼 .

# Exemplos: meus testes manuais

```sh
curl -b "wordpress_logged_in_86a9106ae65537651a8e456835b316ab=alfredo%7C1636951571%7C9edzKCjwqckAtUaWqk0VfSwiDgPlDvopuSA3nhpaQnO%7C44e1eb31a2ade67e7b32086cd3403439f4c288d183c8c2cc02aba36534e73256" http://localhost/wp-json/favorite-wp-post/v1/1
```

```sh
curl -X POST -b "wordpress_logged_in_86a9106ae65537651a8e456835b316ab=alfredo%7C1636951571%7C9edzKCjwqckAtUaWqk0VfSwiDgPlDvopuSA3nhpaQnO%7C44e1eb31a2ade67e7b32086cd3403439f4c288d183c8c2cc02aba36534e73256" http://localhost/wp-json/favorite-wp-post/v1/1
```

```sh
curl -X DELETE -b "wordpress_logged_in_86a9106ae65537651a8e456835b316ab=alfredo%7C1636951571%7C9edzKCjwqckAtUaWqk0VfSwiDgPlDvopuSA3nhpaQnO%7C44e1eb31a2ade67e7b32086cd3403439f4c288d183c8c2cc02aba36534e73256" http://localhost/wp-json/favorite-wp-post/v1/2
```
