# WordPress Back-end Challenge

Desafio para os futuros programadores back-end em WordPress da Apiki.

## Resolução

Inicialmente para configurar o ambiente eu criei um servidor local com docker compose (pois no README original não possuia nenhuma especificação de ambiente) utilizando os seguintes pré-requisitos

* PHP: 8.0
* Wordpress: 5.9
* Banco de Dados MySql: 5.7
* PhpMyAdmin: 5.2

Como se trata de um plugin que cria suas próprias dependências (como a sua tabela no banco de dados) não é necessário utilizar o ambiente por mim construido porém recomendo que utilizem ao longo dos testes. * É valido relembrar que caso o mesmo seja instalado em outro projeto wordpress, será necessário acessar a url: http://localhost:8000/wp-admin/options-permalink.php e marcar a opção "Nome do Post" para que o REST API do wordpress funcione como esperado.


## Funcionamento

O funcionamento foi pensado utilizando o tema base do wordpress Twenty Twenty-Two versão 1.1 onde o próprio plugin cria um opção (em forma de coração ao lado do nome do post) que serve como marcação de "Favorito". Esse dado é carregado na view assim que a mesma é renderizada, o mesmo vem da tabela "wp_favorite_post". As requisições ajax criadas para a inserção dos registros utilizam como base a REST API disponibilizada pelo próprio wordpress. Para fazer o mesmo funcionar basta ativar o plugin e adicionar posts, o novo botão será renderizado na lista imediatamente sem a necessidade de shortcodes.


