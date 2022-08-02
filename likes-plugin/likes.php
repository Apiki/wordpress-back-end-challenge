<?php

/*
Plugin Name: Likes Plugin
Description: Botão de like para o desafio backend de apiki
Version: 1.0
Author: Nick Granados
Author URI: https://github.com/internick2017
Text Domain: likespl
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}
include_once __DIR__ . '\inc\likeRoute.php';

use inc\LikeRoute;

$likesRoute = new LikeRoute();


/**
 * Class LikePlugin
 *
 * @category Core class
 * @package  Back-end
 * @author   Nick Granados <internickbr@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/internick2017/wordpress-back-end-challenge
 */
class LikePlugin
{
    /**
     * @var string Charset da base de dados
     */
    private string $charset;
    /**
     * @var string Nome da tabela com prefixo
     */
    private string $tableName;

    /**
     * Função inicia os actions e hooks
     *
     */
    public function __construct()
    {
        global $wpdb;
        $this->charset = $wpdb->get_charset_collate();
        $this->tableName = $wpdb->prefix . "likes";
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));
        add_action('activate_likes-plugin/likes.php', array($this, 'createTableDb'));
        add_action('admin_post_createLike', array($this, 'createLike'));
        add_action('admin_post_nopriv_createLike', array($this, 'createLike'));
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
    }

    /**
     * Carrega os arquivos css e javascript
     *
     * @return void
     */
    public function load_assets(): void
    {
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('lp-css', plugins_url('/css/main.css', __FILE__));
        wp_enqueue_script('lp-js', plugins_url('/js/main.js', __FILE__), array('jquery'), '0.0.1', true);
        wp_localize_script('lp-js', 'themeData', array(
            'rootUrl' => get_site_url(),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    /**
     * Cria a tabela likes na base de dados
     *
     * @return void
     */
    public function createTableDb(): void
    {
        global $plDbVersion;
        $plDbVersion = '1.0';

        $sql = "CREATE TABLE $this->tableName (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          post_id mediumint(9) NOT NULL,
          user_id mediumint(9) NOT NULL,
          PRIMARY KEY  (id),
          UNIQUE KEY (post_id,user_id)
        ) $this->charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        add_option('plDbVersion', $plDbVersion);
    }

    /**
     * Função para adicionar o html do botão de like no post
     *
     * @param $content
     *
     * @return mixed|void
     */
    public function ifWrap($content)
    {
        if (is_main_query() && is_single() && (get_option('lpp_active', '1'))) {
            return $this->createHTML($content);
        }
        return $content;
    }

    /**
     * Criar o conteúdo do HTML Butão de like
     *
     * @param $content
     *
     * @return void
     */
    public function createHTML($content): void
    {
        global $wpdb;
        global $post;
        $countQuery = "SELECT COUNT(*) FROM $this->tableName WHERE post_id = $post->ID ";
        $userID = get_current_user_id();
        $likeQuery = "SELECT * FROM $this->tableName WHERE user_id = $userID ";
        $contentTop = '';
        $contentBottom = '';
        if (get_option('lpp_location', '0') == '0') {
            $contentBottom = $content;
        } else {
            $contentTop = $content;
        }
        $likes = $wpdb->get_var($wpdb->prepare($countQuery, ''));
        $likeID = $wpdb->get_var($wpdb->prepare($likeQuery, 'user_id'));
        if ($likes) {
            $existStatus = 'yes';
        } else {
            $existStatus = 'no';
        }
        echo $contentTop;
        if (get_option('lpp_active', '1') && is_user_logged_in()) {
            ?>
          <div class="generic-content">
            <div class="two-thirds">
          <span
            class="like-box" data-like="<?php
          echo $likeID; ?>" data-user="<?php
          echo
          $userID; ?>" data-post="<?php
          echo
          $post->ID; ?>" data-exists="<?php
          echo $existStatus; ?>"
          >
              <i class="fa fa-heart-o" aria-hidden="true"></i>
              <i class="fa fa-heart" aria-hidden="true"></i>
              <span class="like-count"><?php
                  echo $likes; ?></span>
            </span>
            </div>
          </div>
            <?php
        }
        echo $contentBottom;
    }

    /**
     * Registrar e adicionar os campos para as configurações plugin
     *
     * @return void
     */
    public function settings(): void
    {
        add_settings_section('lpp_first_section', null, null, 'like-post-settings-page');

        register_setting(
            'likepostplugin',
            'lpp_location',
            array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0')
        );
        add_settings_field(
            'lpp_location',
            'Local de exibição',
            array($this, 'locationHTML'),
            'like-post-settings-page',
            'lpp_first_section'
        );


        add_settings_field(
            'lpp_buttontext',
            'Texto do botão',
            array($this, 'buttonTextHTML'),
            'like-post-settings-page',
            'lpp_first_section'
        );
        register_setting(
            'likepostplugin',
            'lpp_buttontext',
            array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics')
        );

        add_settings_field(
            'lpp_active',
            'Ativo?',
            array($this, 'checkboxHTML'),
            'like-post-settings-page',
            'lpp_first_section',
        );
        register_setting(
            'likepostplugin',
            'lpp_active',
            array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')
        );
    }

    /**
     * Validação personalizada do campo do checkbox
     *
     * @param $input
     *
     * @return false|mixed|void
     */
    public function sanitizeLocation($input)
    {
        if ($input != '0' && $input != '1') {
            add_settings_error(
                'lpp_location',
                'lpp_location_error',
                'O local de exibição deve ser no começo ou fim da postagem'
            );
            return get_option('lpp_location');
        }
        return $input;
    }

    /**
     * Função que gera o HTML do campo checkbox das configurações
     *
     * @return void
     */
    public function checkboxHTML(): void
    { ?>
      <input
        type="checkbox" name="lpp_active" value="1" <?php
      checked(get_option('lpp_active', '1')) ?>>
        <?php
    }

    /**
     *Função que gera o HTML do campo texto das configurações
     *
     * @return void
     */
    public function buttonTextHTML(): void
    { ?>
      <input
        type="text" name="lpp_buttontext" value="<?php
      echo esc_attr(get_option('lpp_buttontext')) ?>"
      >
        <?php
    }

    /**
     * Função que gera o HTML do campo select das configurações
     *
     * @return void
     */
    public function locationHTML(): void
    { ?>
      <select name="lpp_location">
        <option
          value="0" <?php
        selected(get_option('lpp_location'), '0') ?>>Começo da postagem
        </option>
        <option
          value="1" <?php
        selected(get_option('lpp_location'), '1') ?>>Fim da postagem
        </option>
      </select>
        <?php
    }

    /**
     * Adiciona a página de opções do plugin no painel
     *
     * @return void
     */
    public function adminPage(): void
    {
        add_menu_page(
            'Configurações Like Post',
            __('Like Post', 'likespl'),
            'manage_options',
            'like-settings-page',
            array($this, 'likesSettingsHTML'),
            'dashicons-thumbs-up',
            66
        );
    }

    /**
     * Adiciona o Form. da página opções
     *
     * @return void
     */
    public function likesSettingsHTML(): void
    { ?>
      <div class="wrap">
        <h1>Configurações de Like Post </h1>
        <form action="options.php" method="POST">
            <?php
            settings_fields('likepostplugin');
            do_settings_sections('like-post-settings-page');
            submit_button();
            ?>
        </form>
      </div>
        <?php
    }


}

$likesPlugin = new LikePlugin();

