<?php
if (!defined('ABSPATH')) {
    exit;
}

class Apiki_Favorites_Shortcode
{

    /**
     * @var Apiki_Favorites_Repository
     */
    private $repository;

    /**
     * @param Apiki_Favorites_Repository $repository
     */
    public function __construct(Apiki_Favorites_Repository $repository)
    {
        $this->repository = $repository;
    }

    public function registerAssets(): void
    {
        wp_register_script(
                'apiki-favorites',
                APIKI_FAVORITES_URL . 'assets/apiki-favorites.js',
                array(),
                APIKI_FAVORITES_VERSION,
                true
        );

        wp_localize_script(
                'apiki-favorites',
                'apikiFavorites',
                array(
                        'restUrl' => esc_url_raw(rest_url('apiki/v1/favorites/')),
                        'nonce' => wp_create_nonce('wp_rest'),
                        'isLoggedIn' => is_user_logged_in(),
                        'favoriteText' => __('Favoritar', 'apiki-favorites'),
                        'unfavoriteText' => __('Desfavoritar', 'apiki-favorites'),
                        'loadingText' => __('Processando...', 'apiki-favorites'),
                        'loginRequiredText' => __('Você precisa estar logado para favoritar posts.', 'apiki-favorites'),
                        'errorText' => __('Ocorreu um erro ao processar sua solicitação.', 'apiki-favorites'),
                )
        );
    }

    /**
     * @param array $attrs
     * @return string
     */
    public function showButton(array $attrs): string
    {
        $attrs = shortcode_atts(
                array(
                        'post_id' => 0,
                        'label' => '',
                ),
                $attrs,
                'apiki_favorite_button'
        );

        $post_id = absint($attrs['post_id']);

        if (!$post_id && is_singular('post')) {
            $post_id = get_the_ID();
        }

        if (!$post_id) {
            return '';
        }

        $post = get_post($post_id);

        if (!$post || 'post' !== $post->post_type || 'publish' !== $post->post_status) {
            return '';
        }

        $is_logged_in = is_user_logged_in();
        $is_favorited = false;

        if ($is_logged_in) {
            $is_favorited = $this->repository->exists(get_current_user_id(), $post_id);
        }

        $button_label = $attrs['label'];

        if ('' === $button_label) {
            $button_label = $is_favorited ? __('Desfavoritar', 'apiki-favorites') : __('Favoritar', 'apiki-favorites');
        }

        wp_enqueue_script('apiki-favorites');

        ob_start();
        ?>
        <button
                type="button"
                class="apiki-favorite-button"
                data-post-id="<?php echo esc_attr($post_id); ?>"
                data-favorited="<?php echo $is_favorited ? 'true' : 'false'; ?>"
                aria-pressed="<?php echo $is_favorited ? 'true' : 'false'; ?>"
        >
            <?php echo esc_html($button_label); ?>
        </button>
        <?php
        return ob_get_clean();
    }


    /**
     * @param array $attrs
     * @return string
     */
    public function showFavoriteList(array $attrs): string
    {
        if (!is_user_logged_in()) {
            return '';
        }

        shortcode_atts(
                array(
                        'empty_message' => __('Você ainda não possui posts favoritos.', 'apiki-favorites'),
                ),
                $attrs,
                'apiki_favorite_list'
        );

        $favorites = $this->repository->get_user_favorites(get_current_user_id());

        if (empty($favorites)) {
            return '<p>' . esc_html($attrs['empty_message']) . '</p>';
        }

        $items = array();

        foreach ($favorites as $favorite) {
            $post_id = isset($favorite['post_id']) ? absint($favorite['post_id']) : 0;

            if (!$post_id) {
                continue;
            }

            $post = get_post($post_id);

            if (!$post || 'post' !== $post->post_type || 'publish' !== $post->post_status) {
                continue;
            }

            $items[] = sprintf(
                    '<li><a href="%1$s">%2$s</a></li>',
                    esc_url(get_permalink($post_id)),
                    esc_html(get_the_title($post_id))
            );
        }

        if (empty($items)) {
            return '<p>' . esc_html($attrs['empty_message']) . '</p>';
        }

        return '<ul class="apiki-favorites-list">' . implode('', $items) . '</ul>';
    }
}