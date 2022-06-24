<?php
//classe responsavel por realizar operações nas tabelas do banco 
class Operacoes
{

    //função responsavel por gerenciar e atualizar as tabelas do banco 
    public function operacoesTabela()
    {
        include("conectabanco.php");   //estabelece conexão com o banco 

        $ID = $_GET['ID'];             //captura ID do post que usuario selecionou para favoritar 

        //declaração de variaveis e estrutura de repetição para verificar se a tabela que armazenas os posts favoritados foi criada 
        $table = 'favoritado';  
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        $tableExists = $result && $result->num_rows > 0;

        if ($tableExists) {                                 //caso a tabela já exista, prossegue com as operações 

            $consulta = mysqli_query($conn, "SELECT * FROM favoritado WHERE id = $ID");

            $cont = 0;

            if (mysqli_num_rows($consulta) > 0) {
                
                while ($rowData = mysqli_fetch_array($consulta)) {
                    $cont =  $rowData["id"] . '<br>';
                    $statT =  $rowData["stat"] . '<br>';
                     
                    //verifica se o ID encontrado na consulta corresponde com o informado pelo usuario 
                    if (intval($ID) == intval($cont)) {

                        if ((str_contains($statT, 'sim'))) {          //caso o status do post esteja como fdavoritado ('sim'), o mesmo é alterado para desfavoritado ('não')
                            echo ("Post Desfavoritado");
                            $operacao = mysqli_query($conn, "UPDATE favoritado SET stat = 'não' WHERE id = $ID");
                        } else {
                            echo ("Post Favoritado");                 // caso esteja como desfavoritado ('não'), ele é favoritado e atualizado no banco 
                            $operacao = mysqli_query($conn, "UPDATE favoritado SET stat = 'sim' WHERE id = $ID");
                        }
                    }
                }
            } else {                              //caso o ID não seja encontrado na tabela favoritado, ele é adicionado e recebe status de favoritado 
                $operacao = mysqli_query($conn, "INSERT INTO favoritado (id, stat) VALUES ($ID, 'sim')");
                echo ("Post Favoritado");
            }
        } else {                                   //caso a tabela 'favoritado' não exista, a mesma é criada | o post em questão é então adicionado a tabela como favoritado


            $sql = mysqli_query($conn, "CREATE TABLE favoritado (
                id INT,
                stat VARCHAR(6)
                )");

            $operacao = mysqli_query($conn, "INSERT INTO favoritados (id, stat) VALUES ($ID, 'sim')");
            echo ("Post Favoritado");
        }
    }
}
//instacia o objeto da classe e chama a função que realiza as operações 
$obj2 = new Operacoes();
$obj2->operacoesTabela();

?>