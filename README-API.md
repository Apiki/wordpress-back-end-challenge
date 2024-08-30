# Documentação da API - Apiki

## Descrição
Esta API permite que os usuários se autentiquem e gerenciem seus posts favoritos na plataforma Apiki.

## Endpoints

### 1. Gerar Token
**Descrição**: Gera um token de autenticação para o usuário.

**Método**: POST  
**URL**: `{{server}}{{prefix_path}}/login`

**Corpo da Solicitação**:
- `username`: "apiki"
- `password`: "Cw37WnYwesrFt9j"

**Eventos**:
- Ao receber a resposta, salva o token no ambiente do Postman:
```javascript
var jsonData = pm.response.json();
pm.environment.set("token", jsonData.token);
```
### 2. Listar Posts Favoritos
**Descrição**: Lista todos os posts favoritos do usuário autenticado.

**Método**: GET  
**URL**: `{{server}}{{prefix_path}}/favoritePosts`

**Cabeçalhos**:
- `Authorization`: "Bearer {{token}}"

---

### 3. Favoritar Post
**Descrição**: Marca um post como favorito.

**Método**: POST  
**URL**: `{{server}}{{prefix_path}}/favoritePosts`

**Cabeçalhos**:
- `Authorization`: "Bearer {{token}}"

**Corpo da Solicitação**:
- `postId`: "7"
- `active` (opcional): "1"

---

### 4. Desfavoritar Post
**Descrição**: Desmarca um post como favorito.

**Método**: DELETE  
**URL**: `{{server}}{{prefix_path}}/favoritePosts/1`

**Cabeçalhos**:
- `Authorization`: "Bearer {{token}}"