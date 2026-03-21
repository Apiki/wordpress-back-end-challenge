# Apiki Favoritos

Plugin WordPress para favoritar posts via WP REST API.

## Instalação

1. Copie a pasta `apiki-favoritos` para `wp-content/plugins/`
2. Ative o plugin no painel do WordPress em **Plugins > Plugins instalados**
3. A tabela `apiki_favoritos` será criada automaticamente no banco de dados

## Endpoints

Base URL: `/wp-json/apiki/v1`

### Listar favoritos

```
GET /wp-json/apiki/v1/favoritos
```

Retorna todos os posts favoritados pelo usuário logado.

### Favoritar um post

```
POST /wp-json/apiki/v1/favoritos/{post_id}
```

### Desfavoritar um post

```
DELETE /wp-json/apiki/v1/favoritos/{post_id}
```

