<?php

if (!defined("ABSPATH")) exit;



function wpfav_is_authed() {
    return is_user_logged_in();
}