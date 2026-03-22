# WP Favorites Plugin

Plugin WordPress para favoritar posts via REST API, com persistência em tabela customizada.

## Funcionalidades

- Favoritar e desfavoritar posts (toggle)
- Listar favoritos do usuário logado
- Persistência em tabela própria (`wp_favorites`)
- Criação automática da tabela ao ativar o plugin
- Sistema de versionamento da tabela para futuras atualizações
- Script front-end com feedback visual e tratamento de erros

## Requisitos

- PHP >= 7.4
- WordPress >= 5.0

## Instalação

1. Copie a pasta `wp-favorites-plugin` para `wp-content/plugins/`
2. Ative o plugin no painel do WordPress
3. A tabela `wp_favorites` será criada automaticamente

## Estrutura do projeto

```
wp-favorites-plugin/
├── wp-favorites-plugin.php   # Bootstrap: constantes, hooks de ativação
├── uninstall.php             # Remove tabela e opções ao desinstalar
├── includes/
│   ├── class-database.php    # Criação e atualização da tabela
│   ├── class-plugin.php      # Registro de hooks e enqueue de scripts
│   ├── class-api.php         # Endpoints REST (registro e callbacks)
│   └── class-favorites.php   # Regras de negócio (toggle, list)
└── assets/
    └── js/
        └── favorites.js      # Front-end: requisições e feedback visual
```

## Endpoints REST

Todos os endpoints exigem autenticação (usuário logado + nonce).

### Toggle favorito

```
POST /wp-json/wpfav/v1/toggle
```

**Body (JSON):**
```json
{ "post_id": 123 }
```

**Resposta — adicionado (201):**
```json
{
  "status": "added",
  "post_id": 123,
  "message": "Post adicionado aos favoritos."
}
```

**Resposta — removido (200):**
```json
{
  "status": "removed",
  "post_id": 123,
  "message": "Post removido dos favoritos."
}
```

**Resposta — post não encontrado (404):**
```json
{
  "code": "invalid_post",
  "message": "O post informado não existe ou não é válido.",
  "data": { "status": 404 }
}
```

**Resposta — não autenticado (401):**
```json
{
  "code": "rest_forbidden",
  "message": "Você precisa estar logado para usar esta funcionalidade.",
  "data": { "status": 401 }
}
```

### Listar favoritos

```
GET /wp-json/wpfav/v1/list
```

**Resposta (200):**
```json
{
  "total": 2,
  "favorites": [
    { "post_id": "123", "created_at": "2026-03-21 10:00:00" },
    { "post_id": "45",  "created_at": "2026-03-20 08:30:00" }
  ]
}
```

## Uso no tema (botão de favoritar)

O plugin registra automaticamente o script `favorites.js` para usuários logados.
Adicione o atributo `data-wpfav-post-id` a qualquer botão no seu tema:

```php
<?php if ( is_user_logged_in() ) : ?>
    <button
        data-wpfav-post-id="<?php the_ID(); ?>"
        aria-pressed="false"
        class="wpfav-btn"
    >
        Favoritar
    </button>
<?php endif; ?>
```

O script escuta cliques via delegação de evento no `document`, portanto funciona
com conteúdo carregado dinamicamente (infinite scroll, AJAX, etc.).

### Exemplo de estilo CSS básico

```css
.wpfav-btn { cursor: pointer; }
.wpfav-btn.wpfav--active { color: red; }
.wpfav-btn[disabled] { opacity: 0.5; }
```

### Ouvindo o evento customizado

```js
document.addEventListener( 'wpfav:toggled', function ( event ) {
    console.log( 'Post', event.detail.postId, '→', event.detail.status );
} );
```

## Estrutura do banco de dados

Tabela: `wp_favorites`

| Coluna       | Tipo                | Descrição                     |
|--------------|---------------------|-------------------------------|
| `id`         | BIGINT UNSIGNED PK  | Identificador único           |
| `user_id`    | BIGINT UNSIGNED     | ID do usuário (FK wp_users)   |
| `post_id`    | BIGINT UNSIGNED     | ID do post (FK wp_posts)      |
| `created_at` | DATETIME            | Data/hora de criação          |

Índices: `PRIMARY KEY (id)`, `UNIQUE KEY user_post (user_id, post_id)`, `KEY idx_user_id (user_id)`

## Versionamento da tabela

A versão da tabela é armazenada na opção `wpfav_db_version`.
Ao atualizar o plugin, incremente `WPFAV_VERSION` em `wp-favorites-plugin.php`.
O método `WPFAV_Database::maybe_update()` (hook `plugins_loaded`) detecta a diferença
de versão e chama `create_table()` via `dbDelta()` automaticamente.
