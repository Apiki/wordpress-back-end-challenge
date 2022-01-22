

# Wordpress Back-End Challenge - Bruno Andrade



## O Desafio

>Desafio para os futuros programadores back-end em WordPress da Apiki.
>
>### Introdução
>
>Desenvolva um Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a [WP REST API](https://developer.wordpress.org/rest-api/).
>
>**Especifícações**:
>
>* Possibilidade de favoritar e desfavoritar um post;
>* Persistir os dados em uma [tabela a parte](https://codex.wordpress.org/Creating_Tables_with_Plugins);
>
>### Instruções
>
>1. Efetue o fork deste repositório e crie um branch com o seu nome e sobrenome. (exemplo: fulano-dasilva)
>2. Após finalizar o desafio, crie um Pull Request.
>3. Aguarde algum contribuidor realizar o code review.
>
>### Pré-requisitos
>
>* PHP >= 5.6
>* Orientado a objetos
>
>### Dúvidas
>
>Em caso de dúvidas, crie uma issue.



## A Solução

1. Clone o repositório na pasta de plugins de uma instalação padrão do Wordpress
2. Ative o plugin para que a tabela seja criada
3. Envie uma requisição _POST_ com o corpo contendo as informações de **ID do Post**, **ID do usuário**, **Like ou Deslike**

Exemplo de requisição:

```

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => '{BASE_URL}/wp-json/api/v1/post',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "user_id": 5,
    "post_id": 10,
    "status": "1"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

```

* {BASE_URL} deve ser alterado para o dominio da instalação Wordpress
* No campo "status", 1 equivale a curtido, 0 equivale a não curtido.