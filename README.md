# Favoritar Posts com WordPress Plugin

## Descrição
Este plugin permite que usuários logados favoritem e desfavoritem posts no WordPress utilizando a WP REST API.

## Justificativa para Uso de Frameworks/Bibliotecas
Optamos por utilizar o PHP nativo e a WP REST API para manter a simplicidade e compatibilidade total com o WordPress.

## Decisões Técnicas e Arquiteturais
- **Banco de Dados**: Usamos uma tabela separada no banco de dados para armazenar os favoritos dos usuários.
- **API REST**: Implementação de endpoints para adicionar e remover favoritos, garantindo uma interface robusta e escalável.
- **Permissões**: Apenas usuários logados podem favoritar e desfavoritar posts, garantindo a segurança e integridade dos dados.
- **Testes**: Utilizamos PHPUnit para garantir a qualidade do código e PHP_CodeSniffer para manter padrões de codificação.

## Instruções de Compilação e Execução
### Requisitos
- PHP >= 5.6
- Composer

### Passos
1. Clone o repositório para o diretório `wp-content/plugins` do seu WordPress.
2. Execute `composer install` para instalar as dependências.
3. Ative o plugin no painel de administração do WordPress.
4. Para rodar os testes, execute `composer test`.
5. Para verificar os padrões de codificação, execute `composer lint`.

## Notas Adicionais
- Certifique-se de que o WordPress está configurado corretamente com o ambiente de desenvolvimento.
- O plugin foi desenvolvido considerando a simplicidade e facilidade de uso, mantendo o código limpo e bem documentado.

## Exemplo de Uso
Após ativar o plugin, você pode interagir com os endpoints REST:
- **Adicionar Favorito**: `POST /wp-json/favoritos/v1/add` com `{"post_id": <ID do Post>}`
- **Remover Favorito**: `POST /wp-json/favoritos/v1/remove` com `{"post_id": <ID do Post>}`

## Contato
Para mais informações, entre em contato com o desenvolvedor: [Rene Ballesteros Machado Junior](mailto:renebmjr@gmail.com)
