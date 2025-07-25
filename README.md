# WordPress Back-end Challenge

Desafio para os futuros programadores back-end em WordPress da Apiki.

## Introdução

Desenvolva um Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a [WP REST API](https://developer.wordpress.org/rest-api/).

**Especifícações**:

* Possibilidade de favoritar e desfavoritar um post;
* Persistir os dados em uma [tabela a parte](https://codex.wordpress.org/Creating_Tables_with_Plugins);

## Instruções

1. Efetue o fork deste repositório e crie um branch com o seu nome e sobrenome. (exemplo: fulano-dasilva)
2. Após finalizar o desafio, crie um Pull Request.
3. Aguarde algum contribuidor realizar o code review.

## Pré-requisitos

* PHP >= 5.6
* Orientado a objetos

## Como Usar o Plugin

### 1. Instalação
- Faça upload do plugin para `/wp-content/plugins/`
- Ative o plugin no painel administrativo

### 2. Adicionar Botão de Favorito
No arquivo `single.php` do seu tema, adicione:
```php
<?php do_action('wp_favorites_button', get_the_ID()); ?>
```

### 3. Exibir Lista de Favoritos
Use o shortcode em qualquer página:
```
[wp_favorites_list]
```

### 4. API REST
- **Favoritar**: `POST /wp-json/wp-favorites/v1/favorite`
- **Desfavoritar**: `POST /wp-json/wp-favorites/v1/unfavorite`
- **Listar**: `GET /wp-json/wp-favorites/v1/favorites`
