jQuery(function(){
    jQuery('.fav_btn').bind('click', function(){
        const id = jQuery(this).attr('id');

        jQuery.ajax({
            type:'POST',
            url: fav_obj.ajax_url,
            data:{
                action:'fav_click',
                id:id
            },
            success:function(result){
                const ret = result.data;
                switch(ret){
                    case 0:
                        alert("Post Favoritado");
                        jQuery('.fav_btn#'.concat(id)).text('Descurtir');
                    break;
                    case 1:
                        alert("Post Desfavoritado");
                        jQuery('.fav_btn#'.concat(id)).text('Curtir Post');
                    break;
                }
            },
            error:function(){
                alert('Erro ao gravar informações!');
            }
        })
    })
})