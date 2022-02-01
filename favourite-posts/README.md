# Reactions for WordPress

Permite favoritar (ou marcar outras reações customizadas) um post usando a rest-api

## Introdução

Desafio da Apiki para candidatos a desenvolvedor

**Instruções**:

* Ao ativar o plugin serão criadas
	* Tabela wp_reactions
	* Tabela wp_reactions_types
	* Tipo de reação padrão 'favourite'
	* Namespace REST-API /reactions/v1
	* Endpoint REST-API /types, que lista os tipos de reação cadastrados
	* Endpoint REST-API /post, que pode ser usado tanto com GET para consultar as reações feitas a um post (os totais e a reação do usuário corrente) como com POST para registrar uma nova reação

**Endpoints**
- /types (GET): lista os tipos de reação cadastrados
- /post/<post_id> (GET): Lista os totais de reações do post selecionado por tipo de reação, além da reação marcada pelo usuário atual/selecionado
- /post/<post_id> (POST <reaction>[<user_id>]): Adiciona/atualiza uma reação do tipo selecionado ao post selecionado em nome do usuário atual/selecionado
- /post/<post_id> (DELETE) : Remove o registro atual de reação para o post selecionado pelo usuário atual/selecionado (se houver algum)