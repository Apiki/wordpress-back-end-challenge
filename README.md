# Favorite Posts

Plugin WordPress para favoritar posts via REST API.

## Descrição
Este plugin permite que usuários autenticados marquem ou desmarquem posts como favoritos utilizando uma rota customizada na REST API do WordPress. Os favoritos são armazenados em uma tabela personalizada no banco de dados.

## Instalação
1. Copie a pasta `favorite-posts` para o diretório `wp-content/plugins` do seu WordPress.
2. Ative o plugin pelo painel de administração do WordPress.

## Como funciona
- Cria a tabela `wp_favorite_posts` para armazenar os favoritos (usuário x post).
- Disponibiliza uma rota na REST API para favoritar/desfavoritar posts.
- Apenas usuários autenticados podem utilizar a funcionalidade.

## Endpoints da API

### Favoritar/Desfavoritar Post
- **Endpoint:** `/wp-json/favorite-posts/v1/toggle/{post_id}`
- **Método:** `POST`
- **Autenticação:** Necessária (usuário logado)
- **Parâmetros:**
	- `post_id` (na URL): ID do post a ser favoritado/desfavoritado
- **Resposta:**
	- `{ "status": "added" }` se o post foi favoritado
	- `{ "status": "removed" }` se o post foi removido dos favoritos

#### Exemplo de requisição
```bash
curl -X POST \
	-H "Authorization: Bearer <token>" \
	https://seusite.com/wp-json/favorite-posts/v1/toggle/123
```

## Autor
Pedro Marcusso
