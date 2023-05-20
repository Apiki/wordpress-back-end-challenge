##Algumas informações sobre o teste
->Requisitos: Para testar o plugin é necessário instalar no WP o Plugin "JWT Authentication for WP REST API" e gerar o token com o endpoint "/wp-json/jwt-auth/v1/token", passando em fields o "username" e o "password", e o token gerado passado na hora da requisição como Bearer Token;
->Adicionar no arquivo wp-config.php: "define('JWT_AUTH_SECRET_KEY', 'sua chave aleatoria(pode ser qualquer valor)');" e "define('JWT_AUTH_CORS_ENABLE', true);"

#EndPoint
-> '/wp-json/testewp/v1/like'
- O token Bearer é necessário.
- Parâmetros necessários: 'post_id'.

-> Lógica: Se uma curtida já tiver sido adicionada à um post por um determinado usuário, da próxima vez que esse mesmo usuário fizer a requisição será deletada a curtida, funcionando o like e o unlike.