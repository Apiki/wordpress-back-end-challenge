# WordPress Back-end Challenge

Desafio para os futuros programadores back-end em WordPress da Apiki.

## Introdução

- URL: http://localhost:10008/wp-json/api-like/v1/like
  - Para dar Like: Método POST passando USER e POST como parâmetro e os ID'S como value.
  - Para dar unlike Método DELETE passando USER e POST como parâmetro e os ID'S como value.

Não foi criado nenhum tipo de autenticação, seja JWT, Bearer Token etc. Então para ver as funcionalidades like/unlike será preciso remover a função **is_user_logged_in()** caso contrário terá o retorno não autorizado 401.

Também não foi criado nenhum visual, criei apenas me baseando em uma aplicação wordpress headless.

Outra opção, é instalar algum plugin com funcionalidades de autenticação por REST para realização do teste no POSTMAN, por ex. Sem ter que remover a função **is_user_logged_in()**.

Eu poderia ter criado a parte visual no front do wordpress, ou até mesmo autenticação JWT/BEARER TOKEN, mas acredito que esse não era o foco, então tentei simplificar ao máximo.