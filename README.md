# WordPress Back-end Challenge

Desafio para vaga de dev. back-end em WordPress da Apiki.
Um plugin que usuário logado, favorita os posts do blog.

## Instruções:

**Instalação**:

1. Carregue toda a pasta `favicon-posts` para o diretório `/wp-content/plugins/`;

2. Ative o plugin através da tela Plugins (Plugins > Plugins Instalados);

3. Depois disso, pronto, o plugin está instalado, você encontrará o menu `Favoritos por usuário` na tela de administração do WordPress;

4. Cria uma página nova (Páginas > Adicionar nova) com modelo: `Favoritos`, essa página vai ser para o usuário logado, possa ver seus favoritos;

**Como favoritar um post**:

1. Navega pelo site, entra no link do post desejado;

2. Na página do post (a single), vai ter um botão fixo no lado direito da tela, `Favoritar Post`;

3. Pronto, só aguardar a requisição, quando terminar o botão vai ficar: `Remover dos Favoritos`;

Se clicar em `Remover dos Favoritos`, vai fazer o processo parecido para adicionar, só que a ação vai ser de remover;

**Por que só pode adicionar favoritos na single? E não no card ou link dos posts?**

Porque o eventos dos botões de adicionar e remover, ocorre pelo javascript, que faz uma requisição em um endpoint.
E para fazer as ações nas páginas de categorias, ou qualquer página do site/blog, teria que fazer o evento no tema usado, pois cada tema tem seu markup.

## Estrutura da tabela do plugin:

Quando o plugin é ativado, é criado a tabela com nome: " `base_prefi(geralmente é o "wp_")`favicons_posts.


A estrutura é formada por três colunas:

1. `id`: bigint, com auto incremento, não aceita valor nulo, chave primária;
2. `user`: bigint, não aceita valor nulo, nesse coluna, vai ser cadastrado o `ID` do usuário, essa coluna está relacionada com `ID` da tabela `base_prefi(geralmente é o "wp_")`_users;
3. `post`: bigint, não aceita valor nulo, nesse coluna, vai ser cadastrado o `ID` do post, essa coluna está relacionada com `ID` da tabela `base_prefi(geralmente é o "wp_")`_posts;

## Endpoints:

**Adicionar favorito**:
```
/wp-json/favicon/v1/add_favicon
```
* Método aceito: POST;
* Body: Duas chaves em form-data: `user` e `post`, as duas são numericos e obrigatórios.
* Retorno de sucesso (200):
```
{
    "message": "Favorito adicionado",
    "id": 16
}
```

* Retorno de falha, quando usuário tenta favoritar, mas já tem aquele post favoritado (400):
```
{
    "code": "post_duplicate",
    "message": "Post já favoritado",
    "data": {
        "status": 400
    }
}
```
* Retorno de falha, quando não passa o form-data: `post` ou passa valor errado (não numerico) (400):
```
{
    "code": "invalid_post",
    "message": "Invalid Post",
    "data": {
        "status": 400
    }
}
```
* Retorno de falha, quando não passa o form-data: `user` ou passa valor errado (não numerico) (400):
```
{
    "code": "invalid_user",
    "message": "Invalid User",
    "data": {
        "status": 400
    }
}
```

**Remover favorito**:
```
/wp-json/favicon/v1/rm_favicon
```
* Método aceito: POST;
* Body: Duas chaves em form-data: `user` e `post`, as duas são numericos e obrigatórios.
* Retorno de sucesso (200):
```
{
    "message": "Favicon removido com sucesso."
}
```
* Retorno de falha, quando usuário não tem o post favoritad (400):
```
{
    "code": "not_find",
    "message": "Favorito não encontrado",
    "data": {
        "status": 400
    }
}
```
* Retorno de falha, quando não passa o form-data: `post` ou passa valor errado (não numerico) (400):
```
{
    "code": "invalid_post",
    "message": "Invalid Post",
    "data": {
        "status": 400
    }
}
```
* Retorno de falha, quando não passa o form-data: `user` ou passa valor errado (não numerico) (400):
```
{
    "code": "invalid_user",
    "message": "Invalid User",
    "data": {
        "status": 400
    }
}
```

**Verifica se o usuário tem o post favoritado**:
```
/wp-json/favicon/v1/check_post_favicon/?post=${post_id}&user=${user_id}
```
* Método aceito: GET;
* Query Params: `post` e `user` os dois são obrigatórios.
* Retorno de quando o usuario tem o post favoritado:
```
{
    "favorite": true
}
```
* Retorno de quando o usuario não tem o post favoritado:
```
{
    "favorite": false
}
```
* Retorno de erro, quando não passa o param `user`:
```
{
    "error": "Parametro user não especificado"
}
```
* Retorno de erro, quando não passa o param `post`:
```
{
    "error": "Parametro post não especificado"
}
```
* Retorno de erro, quando passa o param `post` errado, por exemplo `post=aaa`:
```
{
    "error": "Formato errado para o parametro post."
}
```
* Retorno de erro, quando passa o param `user` errado, por exemplo `user=aaa`:
```
{
    "error": "Formato errado para o parametro user."
}
```
Os endpoint, foram registrado na função `register_endpoints` na classe: `plugin_processo_seletivo`, dentro do arquivo `favicon-posts.php`.

## Diretórios e arquivos:

O plugin está no diretório `favicon-posts`.

**/favicon-posts.php**:
Arquivo principal do plugin, onde fica toda lógica, registro de table no banco, hook para registrar endpoint's...

**/page-favicons.php**:
Template de página para cadastro e deixar o usuário visualizar seus favoritos.

**/assets**:
Diretório de arquivos de css, javascript.

**/assets/dev_js**:
Diretório de densenvolvimento, javascript.

**/assets/js**:
Diretório de produção, javascript.

**/assets/scss**:
Diretório para arquivos SCSS (SASS), para compilar em css, desenvolvimento.

**/assets/css**:
Diretório para arquivos css compilado.

**/gulpfile.js**:
Arquivo de configuração do Gulp, não afeta no funcionamento do plugin, foi usado para compilar os arquivos `.js` e `.css`...
