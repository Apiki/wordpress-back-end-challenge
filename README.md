# WP Favorites Plugin

Plugin para favoritar posts via REST API.

## Funcionalidades

- Favoritar / desfavoritar posts
- Listar favoritos
- Persistência em tabela customizada
- API REST

## Endpoints

### Toggle favorito
POST /wp-json/wpfav/v1/toggle

Body:
{
  "post_id": 123
}

### Listar favoritos
GET /wp-json/wpfav/v1/list

## Requisitos

- PHP >= 7
- WordPress >= 5.x

## Instalação

1. Copie para wp-content/plugins
2. Ative o plugin
3. A tabela será criada automaticamente

## Estrutura do banco

Tabela: wp_favorites

- id
- user_id
- post_id
- created_at