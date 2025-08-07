/**
 * WordPress Backend Challenge - Favorites JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Manipula o clique no botão de favorito
     */
    $('.wpb-favorite-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var postId = button.data('post-id');
        
        // Previne cliques múltiplos
        if (button.hasClass('loading')) {
            return;
        }
        
        // Adiciona estado de loading
        button.addClass('loading').text('Carregando...');
        
        // Faz a requisição AJAX
        $.ajax({
            url: wpbFavorites.ajax_url,
            type: 'POST',
            data: {
                action: 'wpb_toggle_favorite',
                post_id: postId,
                nonce: wpbFavorites.nonce
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Atualiza o botão
                    button.text(response.data.button_text);
                    
                    // Atualiza as classes
                    if (response.data.favorited) {
                        button.addClass('favorited');
                    } else {
                        button.removeClass('favorited');
                    }
                    
                    // Mostra feedback visual
                    showFeedback(
                        response.data.favorited ? 'Post favoritado!' : 'Post removido dos favoritos!',
                        'success'
                    );
                } else {
                    showFeedback('Erro: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showFeedback('Erro de conexão. Tente novamente.', 'error');
            },
            complete: function() {
                // Remove estado de loading
                button.removeClass('loading');
            }
        });
    });
    
    /**
     * Mostra feedback visual para o usuário
     *
     * @param {string} message Mensagem a ser exibida
     * @param {string} type Tipo da mensagem (success|error)
     */
    function showFeedback(message, type) {
        // Remove feedbacks anteriores
        $('.wpb-feedback').remove();
        
        // Cria novo feedback
        var feedback = $('<div class="wpb-feedback wpb-feedback-' + type + '">' + message + '</div>');
        
        // Adiciona à página
        $('body').prepend(feedback);
        
        // Anima entrada
        feedback.slideDown(300);
        
        // Remove após 3 segundos
        setTimeout(function() {
            feedback.slideUp(300, function() {
                feedback.remove();
            });
        }, 3000);
    }
    
    /**
     * Atualiza contador de favoritos se existir
     */
    function updateFavoritesCount(postId, increment) {
        var counter = $('.wpb-favorites-count[data-post-id="' + postId + '"]');
        if (counter.length) {
            var currentCount = parseInt(counter.text()) || 0;
            var newCount = increment ? currentCount + 1 : currentCount - 1;
            counter.text(Math.max(0, newCount));
        }
    }
});
