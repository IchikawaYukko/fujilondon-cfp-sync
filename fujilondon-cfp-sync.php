<?php
/*
Plugin Name: Fuji London Property CFP Sync
Plugin URI: 
Description: Sync Property from CFP(vebra.com) and post
Version: 1.0
Author: IchikawaYukko
Author URI: http://github.com/IchikawaYukko
License: MIT
*/

/*  Copyright 2018 IchikawaYukko (email : ichikawayurikoNOSPAM@yahoo.co.jp)

MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

//Register Plugin Menu
add_action( 'admin_menu', 'fujilondon_cfp_sync_admin_menu' );

//Settings Menu Func
function fujilondon_cfp_sync_admin_menu() {
	//add_menu_page('CFP 同期設定', 'CFP/Vebra', 'manage_options' , 'fujilondon_cfp_sync_main_menu', 'fujilondon_cfp_sync_plugin_options2');
	//add_submenu_page('fujilondon_cfp_sync_main_menu', 'Sync Setting', 'sync setting', 'manage_options', 'fujilondon_cfp_sync_sync_setting_menu', 'fujilondon_cfp_sync_plugin_options2');
	//add_submenu_page('fujilondon_cfp_sync_main_menu', 'CFP/Vebra 同期ログ', '同期ログ', 'manage_options', 'fujilondon_cfp_sync_log_menu', 'fujilondon_cfp_sync_show_sync_log');
	//add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'fujilondon_cfp_sync_plugin_options2' );
}

/*
//Plugin Menu
function fujilondon_cfp_sync_plugin_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  echo <<<HEREDOC
<div class="wrap">
<p>OPTION FORM</p></div>
HEREDOC;
}
*/

function fujilondon_cfp_sync_show_sync_log() {
    $logdata = file_get_contents(dirname(__FILE__).'/wpfeed_log.txt');

    if(!$logdata) {
        echo 'ログファイルが見つかりません';
    } else {
        echo "<pre>$logdata</pre>";
    }
}

/*
function fujilondon_cfp_sync_plugin_options2() {

    // Check Users Privilege
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // fields and options
    $opt_name = 'mt_favorite_color';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'mt_favorite_color';

    // get option from DB
    $opt_val = get_option( $opt_name );

    // if POST
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // get POSTed data
        $opt_val = $_POST[ $data_field_name ];

        // Save POSTed value to DB
        update_option( $opt_name, $opt_val );

        // Show 'setting saved'
        $x = _e('settings saved.', 'menu-test' );
        echo <<<HEREDOC
<div class="updated"><p><strong>$x</strong></p></div>
HEREDOC;

    }

    $p = __( 'CFP同期設定', 'fujilondon_cfp_sync-configuration' );
    $g = _e("設定値:", 'fujilondon_cfp_sync-configuration' );
    $x = esc_attr_e('Save Changes');

    // Configuration Form
    echo <<<HEREDOC
<div class="wrap">
<h2>$p</h2>
<form name="form1" method="post" action="">
<input type="hidden" name="$hidden_field_name" value="Y">

<p>
$g
<input type="text" name="$data_field_name" value="$opt_val" size="20">
</p><hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="$x" />
</p>

</form>
</div>
HEREDOC;
}
*/