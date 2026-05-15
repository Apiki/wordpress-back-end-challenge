# Apiki Favorites

Plugin WordPress que permite a usuários logados favoritar e desfavoritar posts via WP REST API. Os dados são persistidos em uma tabela própria do plugin.

## Spec atendida

- ✅ Favoritar um post
- ✅ Desfavoritar um post
- ✅ Listar favoritos do usuário atual (extra, útil para o consumidor da API)
- ✅ Persistência em **tabela própria** (não usa `user_meta` nem `post_meta`)
- ✅ Acessível apenas a usuários logados
- ✅ Orientado a objetos

## Endpoints

Todos abaixo `/wp-json/apiki-favorites/v1`. Autenticação por cookie + nonce do WordPress (`X-WP-Nonce` header) ou Application Password.

| Método HTTP | URL | Descrição | Status sucesso |
|---|---|---|---|
| `GET` | `/favorites?page=1&per_page=10` | Lista favoritos paginados do usuário atual | `200` |
| `POST` | `/favorites` body `{"post_id": 123}` | Favorita um post | `201` |
| `DELETE` | `/favorites/{post_id}` | Desfavorita um post | `204` |

Erros padronizados via `WP_Error`:

| Código | Quando | Erro |
|---|---|---|
| `401` | Usuário não autenticado | `rest_forbidden_context` |
| `403` | Usuário sem capability `read` | `rest_forbidden` |
| `404` | Post inexistente ou não publicado / favorito inexistente | `rest_post_invalid` / `rest_favorite_not_found` |
| `409` | Post já está nos favoritos | `rest_already_favorited` |

### Exemplos com `curl`

```bash
# Favoritar (precisa de cookie de login + nonce)
curl -X POST http://localhost:8080/wp-json/apiki-favorites/v1/favorites \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: $NONCE" \
  --cookie "$COOKIE" \
  -d '{"post_id": 123}'

# Listar
curl http://localhost:8080/wp-json/apiki-favorites/v1/favorites \
  -H "X-WP-Nonce: $NONCE" --cookie "$COOKIE"

# Desfavoritar
curl -X DELETE http://localhost:8080/wp-json/apiki-favorites/v1/favorites/123 \
  -H "X-WP-Nonce: $NONCE" --cookie "$COOKIE"
```

Em desenvolvimento, a forma mais simples de testar é gerar uma **Application Password** no perfil do usuário do wp-admin e usar Basic Auth:

```bash
curl -u "usuario:senha-app-aqui" \
  http://localhost:8080/wp-json/apiki-favorites/v1/favorites
```

## Como rodar localmente (Docker)

```bash
docker compose up -d
# Acesse http://localhost:8080 e finalize o setup do WordPress.
# Depois, em Plugins, ative o "Apiki Favorites".

# Instalar dependências do plugin (PHPCS):
docker compose run --rm cli install

# Rodar lint (WordPress Coding Standards):
docker compose run --rm cli lint
```

## Estrutura do projeto

```
apiki-favorites/
├── apiki-favorites.php              # Plugin header + bootstrap (entry point)
├── uninstall.php                    # Limpa a tabela quando o plugin é DESINSTALADO (não no deactivate)
├── composer.json                    # PSR-4 + WPCS lint
├── phpcs.xml.dist                   # WordPress Coding Standards
├── docker-compose.yml               # WP 6.6 + PHP 8.1 + MySQL 8
└── src/
    ├── Plugin.php                   # Composition root — registra hooks
    ├── Activator.php                # Cria a tabela via dbDelta
    ├── Domain/
    │   ├── Favorite.php             # Value object readonly
    │   └── FavoriteException.php    # Exception de domínio, carrega o HTTP code
    ├── Repository/
    │   └── FavoritesRepository.php  # CRUD; recebe wpdb via DI no construtor
    └── REST/
        └── FavoritesController.php  # extends WP_REST_Controller
```

## Tabela

```sql
CREATE TABLE wp_apiki_favorites (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_post (user_id, post_id),
    KEY user_id (user_id),
    KEY post_id (post_id)
);
```

Decisões:

- **Tabela própria** em vez de `user_meta` — a spec pede explicitamente, e queries do tipo "quem favoritou o post X?" ou "quais posts o usuário Y favoritou?" são `O(N)` em meta tables (full scan), mas `O(log N)` aqui graças aos índices.
- **`UNIQUE (user_id, post_id)`** — impede duplicação no nível do banco. Mesmo se a aplicação esquecer de checar antes do INSERT, o MySQL rejeita a linha.
- **`dbDelta`** no activation hook — padrão do WordPress, lida com ALTER TABLE em upgrades futuros sem perder dados.

## Decisões de design

| Decisão | Por quê |
|---|---|
| **`final class` em tudo** | Fecha o contrato. Se alguém quiser variar comportamento, compõe — não herda. |
| **Sem Singleton** | `Plugin::boot()` é um *composition root* sem estado mutável. WordPress já garante que o arquivo é carregado uma única vez por request. |
| **`global $wpdb` lido apenas no `Plugin::boot()`** | O `FavoritesRepository` recebe `wpdb` via construtor e o guarda em property `readonly`. Nenhum método chama `global $wpdb` — facilita teste, deixa a dependência explícita. |
| **Value Object `Favorite` com properties `readonly`** | Mesma motivação do `ExchangeInput` do back-end PHP: o objeto que sai do repositório não pode ser modificado por engano. |
| **`extends WP_REST_Controller`** em vez de chamar `register_rest_route()` no nada | Herda os helpers de schema, `prepare_response_for_collection`, `add_additional_fields_schema`, etc. |
| **`WP_REST_Server::READABLE / CREATABLE / DELETABLE`** em vez de strings `'GET'/'POST'/'DELETE'` | Constantes são bitmask-friendly, documentadas, e qualquer mudança no core do WP é refletida automaticamente. |
| **`current_user_can('read')` em vez de só `is_user_logged_in()`** | Routear a autorização pelo sistema de capabilities permite que um admin (com um plugin de roles, por exemplo) restrinja o acesso sem mexer no código. `is_user_logged_in()` apenas responde "tem usuário?" — `current_user_can()` responde "ele PODE fazer isso?". |
| **`defined('ABSPATH') \|\| exit;` no topo de todos os `.php`** | Defesa: se alguém acessar o arquivo direto via URL (configuração errada de servidor), o script aborta em vez de vazar caminho/código. |
| **`uninstall.php` separado** | WordPress chama esse arquivo só no *uninstall* (DELETE), não no *deactivate*. A tabela só some quando o usuário REALMENTE remove o plugin. |
| **Exception de domínio** (`FavoriteException`) | Carrega o HTTP code (`409`, `404`) em `getCode()` — o controller mapeia direto pra `WP_Error` sem inspecionar mensagem. |

## Requisitos

- WordPress 6.0+
- PHP 8.1+ (uso de `readonly` properties e constructor property promotion)
- MySQL 5.7+ / MariaDB 10.3+
