<?php
//    Pastèque Web back office, WordPress ident module
//
//    Copyright (C) 2013 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque {
    if (@constant("\Pasteque\ABSPATH") === NULL) {
        die();
    }
    require_once(dirname(__FILE__) . "/config.php");
}

namespace WordPress {
    $api_user = NULL;
    $timezone = date_default_timezone_get();
    require_once($config['wordpress_base_path'] . "/wp-load.php");
    date_default_timezone_set($timezone);
    function logged_in() {
        return is_user_logged_in();
    }
    function log($user, $password) {
        // Dirty hack to disable WordPress magic_quotes when using API
        if ( !get_magic_quotes_gpc() ) {
            $_POST = array_map('stripslashes_deep', $_POST);
            $_GET = array_map('stripslashes_deep', $_GET);
            $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
            $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        }
        $creds = array();
        $creds['user_login'] = $user;
        $creds['user_password'] = $password;
        $creds['remember'] = FALSE;
        $user = wp_signon($creds, FALSE);
        if (!is_wp_error($user)) {
            global $api_user;
            $api_user = $user;
            return TRUE;
        } else {
            return FALSE;
        }
    }
    function show_login() {
        auth_redirect();
    }
    function get_user_id() {
        global $api_user;
        if ($api_user !== NULL) {
            return $api_user->ID;
        } else {
            return get_current_user_id();
        }
    }
}

namespace Pasteque {
    function is_user_logged_in() {
    	return \WordPress\logged_in();
    }
    function api_user_login() {
        return \WordPress\log($_GET['login'], $_GET['password']);
    }
    function show_login_page() {
        return \WordPress\show_login();
    }

    function get_user_id() {
        return \WordPress\get_user_id();
    }
}

?>
