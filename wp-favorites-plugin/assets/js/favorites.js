/**
 * WP Favorites Plugin — front-end
 *
 * Usa wpFavSettings (definido via wp_localize_script) para obter:
 *   wpFavSettings.apiUrl  — URL base da REST API do plugin
 *   wpFavSettings.nonce   — nonce gerado pelo WordPress para autenticação REST
 */

( function () {
    'use strict';

    /**
     * Alterna o estado de favorito de um post.
     *
     * @param {number} postId  - ID do post.
     * @param {HTMLElement} [btn] - Botão que disparou a ação (opcional, para feedback visual).
     * @returns {Promise<void>}
     */
    async function toggleFavorite( postId, btn ) {
        if ( ! postId || postId <= 0 ) {
            console.error( 'wpfav: post_id inválido.', postId );
            return;
        }

        // Feedback visual imediato: desabilita o botão durante a requisição.
        if ( btn ) {
            btn.disabled = true;
            btn.setAttribute( 'aria-busy', 'true' );
        }

        try {
            const response = await fetch( wpFavSettings.apiUrl + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpFavSettings.nonce,
                },
                body: JSON.stringify( { post_id: parseInt( postId, 10 ) } ),
            } );

            // Trata erros HTTP (401, 404, 500, etc.)
            if ( ! response.ok ) {
                const errorData = await response.json().catch( () => ( {} ) );
                const message = errorData.message || `Erro HTTP ${ response.status }`;
                console.error( 'wpfav toggle error:', message );

                if ( btn ) {
                    btn.setAttribute( 'data-wpfav-error', message );
                }

                return;
            }

            const data = await response.json();

            // Atualiza o estado visual do botão conforme a resposta da API.
            if ( btn ) {
                const isFavorited = data.status === 'added';
                btn.classList.toggle( 'wpfav--active', isFavorited );
                btn.setAttribute( 'aria-pressed', String( isFavorited ) );
                btn.setAttribute( 'title', isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos' );
                btn.removeAttribute( 'data-wpfav-error' );
            }

            // Dispara um evento customizado para que o tema/outros scripts possam reagir.
            document.dispatchEvent(
                new CustomEvent( 'wpfav:toggled', {
                    detail: { postId: data.post_id, status: data.status },
                } )
            );
        } catch ( error ) {
            // Trata falhas de rede (sem conexão, timeout, etc.)
            console.error( 'wpfav: falha na requisição.', error );

            if ( btn ) {
                btn.setAttribute( 'data-wpfav-error', 'Falha na conexão. Tente novamente.' );
            }
        } finally {
            // Reabilita o botão independentemente do resultado.
            if ( btn ) {
                btn.disabled = false;
                btn.removeAttribute( 'aria-busy' );
            }
        }
    }

    /**
     * Delega o clique em botões com data-wpfav-post-id dentro do document.
     * Compatível com conteúdo carregado dinamicamente (infinite scroll, etc.).
     *
     * Uso no tema:
     *   <button data-wpfav-post-id="<?php the_ID(); ?>" aria-pressed="false">
     *     Favoritar
     *   </button>
     */
    document.addEventListener( 'click', function ( event ) {
        const btn = event.target.closest( '[data-wpfav-post-id]' );

        if ( ! btn ) {
            return;
        }

        const postId = parseInt( btn.dataset.wpfavPostId, 10 );
        toggleFavorite( postId, btn );
    } );

    // Expõe a função globalmente para uso programático se necessário.
    window.wpFavToggle = toggleFavorite;
} )();
