<div class="wrap">
    <h1>Configurações Gerais</h1>
    <form action="options.php" method='POST'>
        <?php
            settings_errors();
            settings_fields('jvaflikepost');
            do_settings_sections('jvaf_like_post_settings_page');
            submit_button();
        ?>
    </form>
</div>