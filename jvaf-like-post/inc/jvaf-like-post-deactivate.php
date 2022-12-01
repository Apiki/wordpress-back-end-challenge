<?php
/**
 * @package JvafLikePost
 */

class JvafLikePostDeactivate {
    function onDeactivate(){  
        flush_rewrite_rules();
    }
}