<?php
//classe responsavel por exibir informações na tela 
class Exibicao
{

    function printar()
    {
        include("conectabanco.php");       //estabelece conexão com o banco de dados 

        $dados = filter_input(INPUT_POST, 'array', FILTER_SANITIZE_SPECIAL_CHARS);        //captura as informações obtidas na função do js
        $dados = explode(",", $dados);
        $in = '(' . implode(',', $dados) . ')';

        //consulta para capturar as informações dos posts nas tabelas do wordpress
        $consulta = mysqli_query($conn, "SELECT DISTINCT wp.id, wp.post_title, wp.guid
        FROM wp_posts as wp
        INNER JOIN favoritado as fav ON wp.id IN $in AND wp.post_status = 'publish'");

        $idT = 0;

        if (mysqli_num_rows($consulta) > 0) {
            while ($rowData = mysqli_fetch_array($consulta)) {
                //pega os dados retornados pela consulta
                $idT =  $rowData["id"] . '<br>';
                $postT =  $rowData["post_title"] . '<br>';
                $link =  $rowData["guid"] . '<br>';
                 
                //nova consulta para verificar se o post já está favoritado 
                $consulta2 = mysqli_query($conn, "SELECT *
                FROM wp_posts as wp
                INNER JOIN favoritado as fav ON '$idT' = fav.id AND fav.stat = 'sim'");
                
                //caso o post esteja na tabela favoritado e esteja com status de favoritadom, imprime a informação | caso contrario printa que não está 
                if (mysqli_num_rows($consulta2) > 0) {
                    echo ("ID do Post: $idT  Favoritado: sim") . '<br>';
                    echo ("Titulo do Post: $postT  link: <a href='{$link}' class='buttonLink'>Acesssar Post</a><br> ") . '<br>';
                } else {
                    echo ("ID do Post: $idT  Favoritado: não") . '<br>';
                    echo ("Titulo do Post: $postT   link:  <a href='{$link}' class='buttonLink'>Acesssar Post</a><br> ") . '<br>';
                }
            }
        }
    }
}
//instaciação e incialização do objeto da classe
$obj = new Exibicao();
$obj->printar();

?>