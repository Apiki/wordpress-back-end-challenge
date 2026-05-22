# WordPress Back-end Challenge

Desafio para os futuros programadores back-end em WordPress da Apiki.

## Introdução

Desenvolva um Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a [WP REST API](https://developer.wordpress.org/rest-api/).

**Especifícações**:

* Possibilidade de favoritar e desfavoritar um post;
* Persistir os dados em uma [tabela a parte](https://codex.wordpress.org/Creating_Tables_with_Plugins);

## Pré-requisitos

* PHP >= 5.6
* Orientado a objetos
* Composer

## 🚀 Solução Proposta

O plugin **Apiki Favorite Posts** implementa a funcionalidade completa de favoritar e desfavoritar posts para usuários logados usando a **WP REST API**. 

### Principais Diferenciais:
1. **Design Object-Oriented**: Todo o código está isolado em Namespaces (`Apiki\FavoritePosts`) utilizando PSR-4 Autoloading gerenciado pelo Composer. Sem poluição no escopo global.
2. **Segurança Avançada**: 
   - Autorização bloqueada estritamente pela validação de `is_user_logged_in()`.
   - Prevenção ativa a injeção de SQL via `$wpdb->prepare()`.
   - Sanitização de inputs do usuário via `absint` nos controllers da REST API.
   - Validações de domínio rigorosas: impede que usuários favoritem posts que não existem, que são rascunhos, ou que pertençam a *Custom Post Types* não públicos.
3. **Persistência Customizada Otimizada**: Tabela de banco de dados criada automaticamente com índices de alta performance e chaves compostas (UNIQUE no `user_id` e `post_id`) para garantir a integridade dos dados, impedindo duplicação de maneira relacional.
4. **Testes Unitários Automatizados**: Suíte completa no PHPUnit rodando localmente sem a necessidade de instanciar o banco de dados (usando Mocks do `$wpdb` e de funções core do WP).

---

## 🛠 Instruções de Instalação e Testes

### 1. Testes Automatizados (Sem Precisar do WordPress)
Você não precisa de um servidor local rodando para validar que a regra de negócio está intacta.
Abra o terminal na pasta do plugin e execute:

```bash
composer install
vendor/bin/phpunit
```

### 2. Testando Diretamente no WordPress
Mova (ou instale) esta pasta para o diretório de plugins da sua instalação local (`/wp-content/plugins/`).
Ative o plugin no painel Administrativo (wp-admin).

#### Como realizar requisições via Postman/cURL
Como os endpoints exigem que o usuário esteja **autenticado**, o WordPress exige que você informe credenciais na requisição.
1. No `wp-admin`, vá em **Usuários > Perfil**, role até a seção "Senhas de aplicativo" e crie uma.
2. No seu Postman, utilize autenticação do tipo **Basic Auth**.
3. Use seu Login como *username* e a Senha de Aplicativo que foi gerada como *password*.

#### Endpoints Implementados (Prefixo: `/wp-json/apiki/v1/favorites`)

- **Adicionar Favorito**
  `POST /wp-json/apiki/v1/favorites`
  - Body (JSON): `{"post_id": 123}`
  - Respostas: `201 Created` (Sucesso), `400 Bad Request` (Post inválido/Já favoritado), `401 Unauthorized` (Não logado), `404 Not Found` (Post não existe).

- **Desfavoritar Post**
  `DELETE /wp-json/apiki/v1/favorites/{ID_DO_POST}`
  - Exemplo: `DELETE /wp-json/apiki/v1/favorites/123`
  - Respostas: `200 OK` (Sucesso), `404 Not Found` (Não está favoritado).

- **Listar Meus Favoritos (Paginado)**
  `GET /wp-json/apiki/v1/favorites?page=1&per_page=10`
  - Retorna a lista de IDs de posts favoritados pelo usuário em um JSON array (ex: `[123, 125, 400]`).
  - Inclui cabeçalhos padronizados HTTP do WordPress de paginação na resposta: `X-WP-Total` e `X-WP-TotalPages`.
