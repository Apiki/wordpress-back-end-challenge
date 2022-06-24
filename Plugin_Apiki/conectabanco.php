<?php
class MySqlC
{
    //Configurações da conexão      || Os dados de conexão com o banco devem ser informados 
    private $servername = "localhost";
    private $username = "root";
    private $password = "root";
    private $dbname = "wpjean";


    function conexao()
    {
        // Create connection
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        return $conn;
    }
}

//instacia o objeto da classe e chama a função que realiza as operações 
$conect = new MySqlC();
$conn = $conect->conexao();


?>