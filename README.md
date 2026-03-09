# Apiki Favorites Plugin

Plugin WordPress para permitir que usuários logados favoritem posts usando a WP REST API.

## Instalação

1. Faça o download ou clone este repositório.
2. Copie a pasta do plugin para o diretório `wp-content/plugins/` do seu WordPress.
3. Ative o plugin através do menu "Plugins" no WordPress.

## Estrutura de Arquivos

```
apiki-favorites/
├── apiki-favorites.php          # Arquivo principal do plugin
├── README.md                    # Este arquivo
└── includes/
    ├── class-activator.php          # Classe de ativação (cria a tabela)
    ├── class-favorites-repository.php    # Repositório (persistência no banco)
    ├── class-favorites-rest-controller.php # Controlador REST API
    └── class-plugin.php             # Classe principal do plugin
```

## Endpoints da API

### Favoritar um Post

- **Endpoint:** `POST /wp-json/apiki/v1/favorites/{post_id}`
- **Autenticação:** Necessária (usuário logado)
- **Exemplo de requisição:**
  
```
bash
  curl -X POST https://seusite.com.br/wp-json/apiki/v1/favorites/123 \
    -H "Authorization: Basic base64_encode(username:application_password)"
  
```

- **Resposta de sucesso (201):**
  
```
json
  {
    "message": "Post favoritado com sucesso.",
    "data": {
      "post_id": 123,
      "user_id": 1
    }
  }
  
```

- **Resposta de erro - Post não encontrado (404):**
  
```
json
  {
    "code": "apiki_favorites_post_not_found",
    "message": "Post não encontrado.",
    "data": {
      "status": 404
    }
  }
  
```

- **Resposta de erro - Já favoritado (422):**
  
```
json
  {
    "code": "apiki_favorites_already_exists",
    "message": "Este post já está favoritado.",
    "data": {
      "status": 422
    }
  }
  
```

### Desfavoritar um Post

- **Endpoint:** `DELETE /wp-json/apiki/v1/favorites/{post_id}`
- **Autenticação:** Necessária (usuário logado)
- **Exemplo de requisição:**
  
```
bash
  curl -X DELETE https://seusite.com.br/wp-json/apiki/v1/favorites/123 \
    -H "Authorization: Basic base64_encode(username:application_password)"
  
```

- **Resposta de sucesso (200):**
  
```
json
  {
    "message": "Post desfavoritado com sucesso.",
    "data": {
      "post_id": 123,
      "user_id": 1
    }
  }
  
```

- **Resposta de erro - Post não encontrado (404):**
  
```
json
  {
    "code": "apiki_favorites_post_not_found",
    "message": "Post não encontrado.",
    "data": {
      "status": 404
    }
  }
  
```

- **Resposta de erro - Não favoritado (404):**
  
```json
  {
    "code": "apiki_favorites_not_found",
    "message": "Este post não está favoritado.",
    "data": {
      "status": 404
    }
  }
  
```

## Tabela do Banco de Dados

O plugin cria uma tabela chamada `{prefix}apiki_favorites` com a seguinte estrutura:

| Coluna     | Tipo              | Descrição           |
|------------|-------------------|---------------------|
| id         | bigint(20)        | ID único            |
| user_id    | bigint(20)        | ID do usuário       |
| post_id    | bigint(20)        | ID do post          |
| created_at | datetime          | Data de criação     |

A tabela possui uma chave única na combinação (user_id, post_id) para evitar duplicatas.

## Requisitos

- PHP >= 5.6
- WordPress 4.7 ou superior (para REST API)
- Usuário logado para favoritar/desfavoritar

## Autenticação

A API requer autenticação. Você pode usar:

- **Application Passwords** (recomendado para WordPress 5.6+)
- **Cookie Authentication** (para testes locais)
- **JWT Authentication** (se instalado)

Exemplo com Application Password:
```
bash
curl -X POST https://seusite.com.br/wp-json/apiki/v1/favorites/123 \
  -u "username:application_password"
