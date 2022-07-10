# WordPress Plugin Apiki Update Posts

## Atualização de posts

Verbo:
* PUT

Endpoint: 
* /wp-json/apiki/v1/posts/{postId}

Corpo da requisição: 
* title: Titulo do post
* content: Conteudo do post

Retornos: 
* Status Code 200: Post atualizado
* Status Code 400: Ausencia de dados
* Status Code 403: Não autorizado
