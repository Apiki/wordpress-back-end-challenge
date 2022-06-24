<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

<form method="GET" action=plugin3.php >
    
Informe o ID do post que deseja favoritar/desfavoritar:
<input type="text" name="ID" id="ID">
<button>Enviar</button>

        
</form>

<form action='plugin2.php' nmethod="POST" id='form1' onsubmit="return validacao(this)">
       
        <div>
            <h3>Clique para exibir os posts</h3>
            <button onclick= >Mostrar</button>
           
        </div>
        <div id="temp"></div>

</form>


</body>

</html>


<script>
    //função javascript responsavel por pegar os dados do endpoint do wordpress, e passa-los para serem tratados via php
    function validacao() {
        let idPosts = []
        let tituloPost = []


        fetch('http://localhost/wordpress/wp-json/wp/v2/posts').then(function(response) {
            return response.json()
        }).then(function(posts) {
            for (var i in posts) {
                idPosts.push(posts[i].id)
                tituloPost.push(posts[i].title.rendered)

                // cria objeto XMLHttpRequest
                const xhttp = new XMLHttpRequest();
                // chama a função quando a requisição é recebida
                xhttp.onload = function() {
                    document.querySelector("#temp").innerHTML = this.responseText;
                }
                // faz a requisição AJAX - método POST
                xhttp.open("POST", "plugin2.php");
                // adiciona um header para a requisição HTTP
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                // especifica os dados que deseja enviar 
                xhttp.send("array="+idPosts);
               

            }
        })
        return false
    }
</script>

