<?php
ob_start();
/*
  Plugin Name: Social Photo Gallery
  Plugin URI:
  Description: Social Polaroid Photo Gallery
  Version: 1.0
  Author: Infoway
  Author URI: http://www.infoway.us
  License: GPL2
 */
/* =============== HOOK/ACTION =================== */
require_once 'gallery.php';
register_activation_hook(__FILE__, 'socialphotogallery_db_install');
register_uninstall_hook(__FILE__, 'socialphotogallery_uninstall_func');
add_action('admin_enqueue_scripts', 'admin_js_activation_func');
add_action('wp_enqueue_scripts', 'user_js_activation_func');
add_action('admin_menu', 'socialphotogallery_menu_func');
add_action('admin_init', 'socialphotogallery_registersettings_func');
add_shortcode('social-photo-gallery', 'album_gallery_func');
add_action('init', 'register_gallery_post_func');
$socialphotogallery_options = get_option("socialphotogallery_options");
$url ;
function socialphotogallery_db_install() {
    global $wpdb, $table_prefix;
    $album_sql = "CREATE TABLE IF NOT EXISTS `spg_album` 
        (`album_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        `album_title` VARCHAR(255) NOT NULL, 
        `album_description` VARCHAR(500) NOT NULL, 
        `album_path` VARCHAR(500) NOT NULL, 
        `folder_name` VARCHAR(255) NOT NULL,
        `coverphoto` VARCHAR(255) NOT NULL, 
        `watermarktype` ENUM('image','text') NOT NULL, 
        `watermark` VARCHAR(255) NOT NULL, 
        `datetime` TIMESTAMP NOT NULL) ENGINE = MyISAM;";
    $albumimages_sql = "CREATE TABLE IF NOT EXISTS  `spg_albumimages` 
        (`image_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        `post_id` INT NOT NULL, 
        `album_id` INT NOT NULL, 
        `image_caption` VARCHAR(255) NOT NULL, 
        `image_name` VARCHAR(255) NOT NULL, 
        `image_path` TEXT NOT NULL, 
        `datetime` TIMESTAMP NOT NULL) ENGINE = MyISAM;";

    $alter_album_sql = "ALTER TABLE `spg_album` CHANGE `datetime` `datetime` DATE NOT NULL";
    $alter_albumimages_sql = "ALTER TABLE `spg_albumimages` CHANGE `datetime` `datetime` DATE NOT NULL";

    $wpdb->query($album_sql);
    $wpdb->query($albumimages_sql);
    $wpdb->query($alter_album_sql);
    $wpdb->query($alter_albumimages_sql);

/*-----------------------------------------------------------------------*/
    $upload_dir = wp_upload_dir();
    $album_path = $upload_dir['basedir'] . '/socialphotogallery/';
    if (!file_exists($album_path) && !is_dir($album_path)) {
        wp_mkdir_p($album_path);
        chmod($album_path, 0777);
        update_option('album_path', $album_path);
        update_option('root_album_name', 'socialphotogallery');
    }
    /*--------------------------------------------------------*/    
    $to = get_template_directory().'/single-gallery.php';
    $from = plugin_dir_path(__FILE__).'/single-gallery.php';
    if(!file_exists($to)){
        copy($from,$to);
    }
}

function socialphotogallery_uninstall_func(){
    global $wpdb;
    $to = get_template_directory().'/single-gallery.php';    
    if(!file_exists($to)){
        unlink($to);
    }
    deleteDir(get_option('album_path'));
    $sql = "DELETE TABLE IF EXISTS `spg_album`,`spg_albumimages`";
    $wpdb->query($sql);
    delete_option('album_path');
    delete_option('root_album_name');
    delete_option('socialphotogallery_options');
    
    $args = array(
    'numberposts'     => -1,
    'offset'          => 0,
    'orderby'         => 'post_date',
    'order'           => 'DESC',
    'post_type'       => 'gallery',
    'post_status'     => 'publish',
    'suppress_filters' => true ); 
    
    $posts = get_posts($args);
    for($i=0; $i<COUNT($posts); $i++){
        
         wp_delete_post($posts[$i]->ID);
    }
}

function register_gallery_post_func() {

    $labels = array(
        'name' => 'Gallery',
        'singular_name' => 'Gallery',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Gallery',
        'edit_item' => 'Edit Gallery',
        'new_item' => 'New Gallery',
        'all_items' => 'All Gallery',
        'view_item' => 'View Gallery',
        'search_items' => 'Search Gallery',
        'not_found' => 'No gallery found',
        'not_found_in_trash' => 'No gallery found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Gallery Images'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => false,
        'query_var' => true,
        'rewrite' => array('slug' => 'gallery'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
    );

    register_post_type('gallery', $args);
}

/* -------------- ADMIN JS/CSS ACTIVATION ------------------- */

function admin_js_activation_func() {

    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_script('admin-socialphotogallery-js', plugins_url('/admin/js/socialphotogallery.js', __FILE__));
    wp_enqueue_script('admin-socialphotogallery-js');
    wp_register_style('admin-socialphotogallery-css', plugins_url('/admin/css/socialphotogallery.css', __FILE__));
    wp_enqueue_style('admin-socialphotogallery-css');
    wp_register_style('datetimepicker-all', plugins_url('/admin/css/datetimepicker-all.css', __FILE__));
    wp_enqueue_style('datetimepicker-all');
}
/* -------------- USER JS/CSS ACTIVATION ------------------- */
function user_js_activation_func() {

    wp_register_style('user-socialphotogallery-css', plugins_url('/css/socialphotogallery.css', __FILE__));
    wp_enqueue_style('user-socialphotogallery-css');

    //wp_enqueue_script( 'jquery' );
    wp_enqueue_script('jquery-ui-core');
    wp_localize_script('socialphotogallery', 'ajaxurl', admin_url('admin-ajax.php'));
    wp_register_script('user-jquery-1.6.1.min', plugins_url('/js/jquery-1.6.1.min.js', __FILE__));
    wp_enqueue_script('user-jquery-1.6.1.min');
    wp_register_script('jquery-js', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js');
    wp_enqueue_script('jquery-js');
    wp_register_script('jquery-ui-js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js');
    wp_enqueue_script('jquery-ui-js');
    wp_register_script('user-socialphotogallery-js', plugins_url('/js/socialphotogallery.js', __FILE__));
    wp_enqueue_script('user-socialphotogallery-js');
}

function socialphotogallery_menu_func() {
    add_menu_page('Social Photo Gallery', 'Social Photo Gallery', 10, 'social-photo-gallery', '', '', 30);
    add_submenu_page('social-photo-gallery', 'Show Album', 'Show Album', 10, 'social-photo-gallery', 'show_album_func');
    add_submenu_page('social-photo-gallery', 'Add Album', 'Add Album', 10, 'add-album', 'add_album_func');
    add_submenu_page('social-photo-gallery', 'Add Images', 'Add Images', 10, 'add-imgae', 'add_image_func');
    add_submenu_page('social-photo-gallery', 'Gallery Settings', 'Gallery Settings', 10, 'gallery-settings', 'gallery_settings_func');
}

function socialphotogallery_registersettings_func() {
    register_setting('socialphotogallery_settings', 'socialphotogallery_options', 'socialphotogallery_validate');
}

function socialphotogallery_validate($input) {
    global $wpdb;
    return $input;
}

function gallery_settings_func() {
    ?>
    <form action="options.php" method="post" enctype="multipart/form-data">
    <?php
    settings_fields('socialphotogallery_settings');
    $socialphotogallery_options = get_option("socialphotogallery_options");
    settings_errors();
    ?>
        <h2>Search Page Settings</h2>
        <table cellspacing="0" class="widefat theme-options-table">
            <thead>
                <tr>
                    <th scope="row" colspan="3" class="ttl_table">Social Photo Gallery Options</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row" class="lft_row">Image per page</th>
                    <td>
                        <input type="text" style="width:100px; height:30px; margin-top:5px;" name="socialphotogallery_options[paginationrange]" value="<?= empty($socialphotogallery_options['paginationrange']) ? '21': $socialphotogallery_options['paginationrange']; ?>" />
                    </td>
                </tr>
                <tr>
                    <th  scope="row" class="lft_row">Social Media</th>
                    <td>
                        <textarea style="width: 500px; height: 100px" name="socialphotogallery_options[addthiscontent]" ><?= $socialphotogallery_options['addthiscontent'] ?></textarea>                           
                    </td>
                </tr>
                <tr>
                    <th  scope="row" class="lft_row">Watermark Transparency</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[watermark_opacity]" value="<?= empty($socialphotogallery_options['watermark_opacity'])? '50': $socialphotogallery_options['watermark_opacity'] ?>" />
                    </td>
                </tr>
                <tr>
                    <th  scope="row" class="lft_row">Description Class</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[description_class]" value="<?= $socialphotogallery_options['description_class'] ?>" />
                    </td>
                </tr>
                <tr>
                    <th  scope="row" class="lft_row">Previous Link Text</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[previous_link_text]" value="<?= empty($socialphotogallery_options['previous_link_text'])? 'Previous page':$socialphotogallery_options['previous_link_text'] ?>" />
                    </td>
                </tr>
                <tr>
                    <th  scope="row" class="lft_row">Next Link Text</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[next_link_text]" value="<?= empty($socialphotogallery_options['next_link_text'])? 'Next page': $socialphotogallery_options['next_link_text'] ?>" />
                    </td>
                </tr>                
                <tr>
                    <th  scope="row" class="lft_row">Back to list of albums</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[back_to_album_text]" value="<?= empty($socialphotogallery_options['back_to_album_text'])? 'Back to Album': $socialphotogallery_options['back_to_album_text'] ?>" />
                    </td>
                </tr>               
                <tr>
                    <th  scope="row" class="lft_row">Back to gallery</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[back_to_gallery_text]" value="<?= empty($socialphotogallery_options['back_to_gallery_text'])? 'Back to Gallery': $socialphotogallery_options['back_to_gallery_text'] ?>" />
                    </td>
                </tr>               
                <tr>
                    <th  scope="row" class="lft_row">Link Class</th>
                    <td>
                        <input type="text" name="socialphotogallery_options[link_class]" value="<?= empty($socialphotogallery_options['link_class'])? 'buttom-primary': $socialphotogallery_options['link_class'] ?>" />
                    </td>
                </tr>
                
                <tr><th></th><td><p class="submit"><input type="submit" name="save_theme_options" class="button-primary autowidth" value="Save Changes"></p></td></tr>
            </tbody>
        </table>
    </form>
    <?php
}

function show_album_func() {
    global $wpdb;
    $showalbum_url = get_bloginfo('url') . "/wp-admin/admin.php?page=social-photo-gallery";
    $editalbum_url = get_bloginfo('url') . "/wp-admin/admin.php?page=add-album";
    $showalbumimage_url = get_bloginfo('url') . "/wp-admin/admin.php?page=social-photo-gallery&show=image";
    $success_message = get_transient('success_message');
    $upload_url = wp_upload_dir();
    $album_path = get_option('album_path');
    $root_album_name = get_option('root_album_name');
    if (isset($_GET['album_id']) && $_GET['album_id'] != "" && $_GET['show'] == 'image') {
        if (isset($_GET['delete_image']) && $_GET['delete_image'] != "") {
            $post_id_sql = "SELECT post_id FROM `` WHERE image_id='" . $_GET['delete_image'] . "'";
            $post_id = $wpdb->get_var($post_id_sql);
            wp_delete_post($post_id);

            $delete_image_sql = "DELETE FROM `spg_albumimages` WHERE image_id='" . $_GET['delete_image'] . "'";
            $album_id = albumimagedetails($_GET['delete_image'], 'albumid');
            $imagename = albumimagedetails($_GET['delete_image'], 'imagename');
            $foldername = albumdetails($album_id, 'foldername');
            $targetfilename = $album_path . $foldername . '/' . $imagename;
            unlink($targetfilename);
            $wpdb->query($delete_image_sql);
            set_transient('success_message', 'Image Deleted successfully');
            wp_redirect(add_query_arg(array('album_id' => $_GET['album_id']), $showalbumimage_url));
        }
        echo gallery_images($_GET['album_id']);
    } else {
        if (isset($_GET['delete_id']) && $_GET['delete_id'] != "") {
            $delete_album_sql = "DELETE FROM `spg_album` WHERE album_id='" . $_GET['delete_id'] . "'";
            $delete_albumimage_sql = "DELETE FROM `spg_albumimages` WHERE album_id='" . $_GET['delete_id'] . "'";
            $foldername = albumdetails($_GET['delete_id'], 'foldername');
            deleteDir($album_path . $foldername . '/');
            $wpdb->query($delete_album_sql);
            $wpdb->query($delete_albumimage_sql);
            set_transient('success_message', 'Album deleted successfuly');
            wp_redirect($showalbum_url);
        }
        $album_sql = "SELECT * FROM `spg_album`";
        $album_all = $wpdb->get_results($album_sql);
        $album_url = $upload_url['baseurl'] . '/' . $root_album_name . '/';
        ?>
        <h3 class="success_message"><?= $success_message ?></h3> 
        <?php if ($success_message != "") delete_transient('success_message'); ?>

        <table class="widefat fixed show_album_table">
            <tr>
                <th class="table_title">Name</th>
                <th class="table_title">Description</th>
                <th class="table_title">Cover Photo</th>
                <th class="table_title">Watermark</th>
                <th class="table_title">Action</th>
            </tr>
        <?php for ($i = 0; $i < COUNT($album_all); $i++) { ?>
                <tr>
                    <th><?= $album_all[$i]->album_title ?></th>
                    <th><?= substr($album_all[$i]->album_description, 0, 100) ?></th>
                    <th><img width="100px" height="100px" src="<?= $album_url . $album_all[$i]->folder_name . '/' . $album_all[$i]->coverphoto ?>"  /></th>
                    <th><img width="55px" height="55px" src="<?= $album_url . $album_all[$i]->folder_name . '/' . $album_all[$i]->watermark ?>" /></th>
                    <th>
                        <a style="padding:5px;" href="<?= add_query_arg(array('album_id' => $album_all[$i]->album_id, 'show' => 'image'), $showalbum_url) ?>">Show</a>
                        <a style="padding:5px;" href="<?= add_query_arg(array('edit_id' => $album_all[$i]->album_id), $editalbum_url) ?>">Edit</a>
                        <a style="padding:5px;" onclick="return confirm('Do you want to delete this album?')" href="<?= add_query_arg(array('delete_id' => $album_all[$i]->album_id), $showalbum_url) ?>">Delete</a>
                    </th>
                </tr>
        <?php } ?>
        </table>
    <?php
    }
}

function add_album_func() {
    global $wpdb, $table_prefix;
    $success_message = get_transient('success_message');
    $error_message = get_transient('error_message');
    $datetime = date('Y-m-d');
    $album_url = get_bloginfo('url') . "/wp-admin/admin.php?page=add-album";
    $showalbum_url = get_bloginfo('url') . "/wp-admin/admin.php?page=social-photo-gallery";
    $file = plugin_dir_path(__FILE__) . 'socialphotogallery.php';
    //chmod($file, 0777);
    $album_path = get_option('album_path');
    $album_table = 'spg_album';
    if (isset($_POST['album_submit'])) {
        $album_details = $_POST['album_details'];
        $album_cover_iamge = $_FILES['album_cover_image'];
        $album_watermark_image = $_FILES['album_watermark_image'];
        $folder_name = str_replace(" ", "-", strtolower($album_details['title']));
        $targetfilename = $album_path . $folder_name . '/';
        if (isset($_GET['edit_id']) && $_GET['edit_id'] != "") {
            $old_name = albumdetails($_GET['edit_id'], "foldername");
            if (strcmp($old_name, $folder_name) != 0) {
                $old_name = $album_path . $old_name . '/';
                $new_name = $targetfilename;
                rename($old_name, $new_name);
            }
            if ($album_cover_iamge['name'] != "") {
                $coverphoto = albumdetails($_GET['edit_id'], 'coverphoto');
                unlink($targetfilename . $coverphoto);
                move_uploaded_file($album_cover_iamge['tmp_name'], $targetfilename . $album_cover_iamge['name']);
            } else {
                $album_cover_iamge['name'] = albumdetails($_GET['edit_id'], 'coverphoto');
            }
            if ($album_watermark_image['name'] != "") {
                $watermark = albumdetails($_GET['edit_id'], 'watermark');
                unlink($targetfilename . $watermark);
                move_uploaded_file($album_watermark_image['tmp_name'], $targetfilename . $album_watermark_image['name']);
            } else {
                $album_watermark_image['name'] = albumdetails($_GET['edit_id'], 'watermark');
            }
            $album_data = array('album_title' => $album_details['title'],
                'album_description' => stripslashes($album_details['description']),
                'folder_name' => $folder_name,
                'album_path' => $targetfilename,
                'coverphoto' => $album_cover_iamge['name'],
                'watermarktype' => 'image',
                'watermark' => $album_watermark_image['name'],
                'datetime' => $album_details['date']
            );
            $album_where = array('album_id' => $_GET['edit_id']);
            $wpdb->update($album_table, $album_data, $album_where);
            set_transient('success_message', 'Album Updated Successfully');
            wp_redirect($showalbum_url);
        } else {
            /* -------------- ALBUM FOLDER CREATE --------------- */
            if (!file_exists($targetfilename) && !is_dir($$targetfilename)) {
                wp_mkdir_p($targetfilename);
                chmod($targetfilename, 0777);
                $flag = true;
            } else {
                set_transient('error_message', 'This Album is already exists');
                $flag = false;
            }
            /* -------- UPLOAD COVER AND WATERMARK IMAGE --------- */
            if ($flag == true) {
                move_uploaded_file($album_cover_iamge['tmp_name'], $targetfilename . $album_cover_iamge['name']);
                move_uploaded_file($album_watermark_image['tmp_name'], $targetfilename . $album_watermark_image['name']);
                $album_data = array('album_title' => $album_details['title'],
                    'album_description' => stripslashes($album_details['description']),
                    'folder_name' => $folder_name,
                    'album_path' => $targetfilename,
                    'coverphoto' => $album_cover_iamge['name'],
                    'watermarktype' => 'image',
                    'watermark' => $album_watermark_image['name'],
                    'datetime' => $album_details['date']
                );
                $wpdb->insert($album_table, $album_data);
                set_transient('success_message', 'Album Created Successfully');
                wp_redirect($album_url);
            }
        }
    }
    if (isset($_GET['edit_id']) && $_GET['edit_id'] != "") {
        $album_sql = "SELECT * FROM `spg_album` WHERE album_id='" . $_GET['edit_id'] . "'";
        $album = $wpdb->get_results($album_sql);
        $datetime = $album[0]->datetime;
    }
    ?>
    <h3 class="success_message"><?= $success_message ?></h3> 
    <h3 class="error_message"><?= $error_message ?></h3> 
    <?php if ($success_message != "") delete_transient('success_message'); ?>
    <?php if ($error_message != "") delete_transient('error_message'); ?>
    <form name="album_details" method="post" enctype="multipart/form-data">
        <table class="wp-list-table widefat add_album_sec">
            <tr><th colspan="2">Add Album Details</th></tr>
            <tr><td class="add_album">Album Title</td><td><input style="width:500px; height:30px; margin-top:10px;" type="text" name="album_details[title]" value="<?= $album[0]->album_title ?>" /></td></tr>
            <tr><td class="add_album">Album Description</td><td><textarea name="album_details[description]" style="width:500px; height:100px;"><?= stripslashes($album[0]->album_description) ?></textarea></td></tr>
            <tr><td class="add_album">Cover Photo</td><td><input style="margin-top:10px;" type="file" name="album_cover_image" /></td></tr>
            <tr><td class="add_album">Watermark</td><td><input style="margin-top:10px;" type="file" name="album_watermark_image" />(upload .png image)</td></tr>
            <tr><td class="add_album">Date</td><td><input type="text" name="album_details[date]" id="album_date" value="<?= $datetime ?>" /></td></tr>
            <input type="hidden" name="" id="calendar_image_path" value="<?= plugins_url('images/calendar.png', __FILE__) ?>" />
            <tr><td class="add_album"></td><td><input style="margin-top:5px;" type="submit" name="album_submit" value="Submit" class="button-primary" /></td></tr>    
        </table>
    </form>
    <?php
}

function add_image_func() {
    global $wpdb;
    $current_user = wp_get_current_user();
    $albumimage_url = get_bloginfo('url') . "/wp-admin/admin.php?page=add-imgae";
    $multiple_albumimage_url = get_bloginfo('url') . "/wp-admin/admin.php?page=add-imgae&upload=bulk";
    $showalbum_url = get_bloginfo('url') . "/wp-admin/admin.php?page=social-photo-gallery";
    $success_message = get_transient('success_message');
    $error_message = get_transient('error_message');
    $table = "spg_albumimages";
    $upload_dir = wp_upload_dir();
    $root_album_name = get_option('root_album_name');
    $foldername = albumdetails($_GET['album_id'], 'foldername');
    $album_url = $upload_dir['baseurl'] . '/' . $root_album_name . '/' . $foldername . '/';
    if (isset($_POST['multiple_image_submit'])) {
        $album_id = $_POST['album_id'];
        $images = $_FILES['images'];
        $captions = explode(",", $_POST['captions']);
        for ($i = 0; $i < COUNT($images['name']); $i++) {
            $image['name'] = '';
            $image['type'] = '';
            $image['tmp_name'] = '';
            $image['error'] = '';
            $image['size'] = '';
            $image['name'] = $images['name'][$i];
            $image['type'] = $images['type'][$i];
            $image['tmp_name'] = $images['tmp_name'][$i];
            $image['error'] = $images['error'][$i];
            $image['size'] = $images['size'][$i];
            $album_path = albumdetails($album_id, $type = "path");
            $foldername = albumdetails($album_id, $type = "foldername");
            $watermark = albumdetails($album_id, $type = "watermark");
            $results = process_image_upload('images', $album_path, $watermark, $i);
            $image['name'] = $results;
            $post_title = explode(".", $image['name']);
            $post = array(
                'post_author' => $current_user->ID,
                'post_date' => date('Y-m-d H:i:s'),
                'post_date_gmt' => date('Y-m-d H:i:s'),
                'post_name' => $post_title[0],
                'post_parent' => 0,
                'post_status' => 'publish',
                'post_title' => $post_title[0],
                'post_type' => 'gallery',
                'post_category' => array(get_option('gallery_category'))
            );
            $post_id = wp_insert_post($post);
            $data = array(
                'post_id' => $post_id,
                'album_id' => $album_id,
                'image_caption' => $captions[$i],
                'image_name' => $image['name'],
                'image_path' => $album_path . $image['name'],
            );

            $wpdb->insert($table, $data);
        }
        set_transient('success_message', 'Images are uploaded successfully');

        wp_redirect($multiple_albumimage_url);
    }

    if (isset($_POST['image_submit'])) {

        $album_id = $_POST['album_id'];

        $image = $_FILES['image'];

        $image_caption = $_POST['image_caption'];

        if (isset($_GET['album_id']) && isset($_GET['image_id'])) {

            $album_id = $_GET['album_id'];

            //$album_path = albumdetails($album_id,$type="path");

            $foldername = albumdetails($album_id, $type = "foldername");

            $watermark = albumdetails($album_id, $type = "watermark");

            if ($image['name'] != "") {

                $album_path = get_option('album_path');

                $targetfilename = $album_path . $foldername . '/';

                unlink($targetfilename . $_POST['image_name']);

                //$album_path = albumdetails($album_id,$type="path");

                $results = process_image_upload('image', $targetfilename, $watermark, "");

                $image['name'] = $results;
            } else {
                $image['name'] = $_POST['image_name'];
            }

            $data = array('album_id' => $album_id,
                'image_caption' => $image_caption,
                'image_name' => $image['name'],
                'image_path' => $album_path . $image['name'],
            );

            $where = array('image_id' => $_GET['image_id']);

            $wpdb->update($table, $data, $where);

            wp_redirect(add_query_arg(array('album_id' => $_GET['album_id'], 'show' => 'image'), $showalbum_url));
            exit;
        } else {
            if ($album_id == "") {
                set_transient('error_message', 'Please select Album');
            } elseif ($image['name'] == "") {
                set_transient('error_message', 'Please select a image');
            } else {
                $album_path = albumdetails($album_id, $type = "path");
                $foldername = albumdetails($album_id, $type = "foldername");
                $watermark = albumdetails($album_id, $type = "watermark");
                $results = process_image_upload('image', $album_path, $watermark, "");
                $image['name'] = $results;
                $post_title = explode(".", $image['name']);
                $post = array(
                    'post_author' => $current_user->ID,
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_date_gmt' => date('Y-m-d H:i:s'),
                    'post_name' => $post_title[0],
                    'post_parent' => 0,
                    'post_status' => 'publish',
                    'post_title' => $post_title[0],
                    'post_type' => 'gallery',
                    'post_category' => array(get_option('gallery_category'))
                );
                $post_id = wp_insert_post($post);
                $data = array('album_id' => $album_id,
                    'post_id' => $post_id,
                    'image_caption' => $image_caption,
                    'image_name' => $image['name'],
                    'image_path' => $album_path . $image['name'],
                );

                $wpdb->insert($table, $data);

                set_transient('success_message', 'Image is uploaded successfully');

                wp_redirect($albumimage_url);
            }
        }
    }
    if (isset($_GET['album_id']) && isset($_GET['image_id']) && $_GET['album_id'] != "" && $_GET['image_id'] != "") {
        $image_sql = "SELECT * FROM `spg_albumimages` WHERE image_id='" . $_GET['image_id'] . "' AND album_id='" . $_GET['album_id'] . "'";
        $image = $wpdb->get_results($image_sql);
    }
    $albums = $wpdb->get_results("SELECT * FROM `spg_album`");
    ?>
    <h3 class="success_message"><?= $success_message ?></h3> 
    <h3 class="error_message"><?= $error_message ?></h3> 
    <?php if ($success_message != "") delete_transient('success_message'); ?>
    <?php if ($error_message != "") delete_transient('error_message'); ?>
    <form name="albumimages" method="post" enctype="multipart/form-data">
        <table class="wp-list-table widefat add_album_sec"> 
            <tr>
                <td colspan="2" class="add_img">Add Album image</td>
            </tr>       
            <tr>
            <tr><td>Upload Type </td>
                <td class="add_album"> 
    <?php
    if ($_GET['upload'] == 'bulk') {
        $checked = 'checked="checked"';
        $checked1 = '';
    } else {
        $checked = '';
        $checked1 = 'checked="checked"';
    }
    ?>
                    <input type="radio" name="upload" value="single" <?php echo $checked1 ?> onclick="window.location='<?php echo $albumimage_url ?>'" /> &nbsp; Single
                    <input type="radio" name="upload" value="balk" <?php echo $checked ?> onclick="window.location='<?php echo add_query_arg('upload', 'bulk', $albumimage_url) ?>'" />  &nbsp; Bulk  </td>
            </tr>
            <td class="add_album">Album Name</td>
            <td class="add_album"><select name="album_id">
                    <option value="">Please select a Album</option>
    <?php
    for ($i = 0; $i < COUNT($albums); $i++) {
        if ($_GET['album_id'] == $albums[$i]->album_id)
            $selected = 'selected="selected"';
        else
            $selected = '';
        ?>
                        <option <?php echo $selected; ?>  value="<?= $albums[$i]->album_id ?>"><?= $albums[$i]->album_title ?></option>
    <?php } ?>                    
                </select></td>
            </tr>
    <?php if (isset($_GET['upload']) && $_GET['upload'] == 'bulk') { ?>
                <tr>
                    <td class="add_album">Image</td>
                    <td class="add_album"><input type="file" name="images[]" multiple="multiple" /></td>
                </tr>
                <tr>
                    <td class="add_album">Caption:(separated by comma)</td>
                    <td class="add_album"><textarea style="width: 500px; height: 100px" name="captions" /></textarea></td>
                </tr>
                <tr><td class="add_album"></td><td class="add_album"><input type="submit" name="multiple_image_submit" value="Submit" class="button-primary" /></td></tr>
    <?php } else { ?>
                <tr><td class="add_album">Image</td><td class="add_album"><input type="file" name="image" />
        <?php if (isset($_GET['album_id']) && isset($_GET['image_id'])) { ?>
                            <img src="<?= $album_url . $image[0]->image_name ?>" width="150px" height="150px" />
                            <input type="hidden" name="image_name" value="<?= $image[0]->image_name ?>" />
        <?php }
        ?>
                    </td></tr>
                <tr><td class="add_album">Caption</td><td class="add_album"><input type="text" name="image_caption" value="<?= $image[0]->image_caption ?>" /></td></tr>
                <tr><td class="add_album"></td><td class="add_album"><input type="submit" name="image_submit" value="Submit" class="button-primary" /></td></tr>
    <?php } ?>
        </table>
    </form>
    <?php
}

function gallery_images($album_id) {
    global $wpdb;
    $upload_dir = wp_upload_dir();
    $root_album_name = get_option('root_album_name');
    $foldername = albumdetails($album_id, 'foldername');
    $album_path = $upload_dir['baseurl'] . '/' . $root_album_name . '/' . $foldername . '/';
    $showalbumimage_url = get_bloginfo('url') . "/wp-admin/admin.php?page=social-photo-gallery&show=image";
    $albumimage_url = get_bloginfo('url') . "/wp-admin/admin.php?page=add-imgae";
    $str = "";
    $albumimages_sql = "SELECT * FROM `spg_albumimages` WHERE album_id='" . $album_id . "'";
    $albumimages_all = $wpdb->get_results($albumimages_sql);
    $str .= '<table class="widefat fixed show_album_table">';
    $str .= '<tr><th class="sp_title">Image</th><th class="sp_title">Caption</th><th class="sp_title">Action</th></tr>';
    for ($j = 0; $j < COUNT($albumimages_all); $j++) {
        $str .= '<tr>';
        $str .= '<td><img src="' . $album_path . $albumimages_all[$j]->image_name . '" width="100px" height="100px" /></td>';
        $str .= '<td>' . $albumimages_all[$j]->image_caption . '</td>';
        $str .= '<td>';
        $str .= '<a href="' . add_query_arg(array('album_id' => $_GET['album_id'], 'image_id' => $albumimages_all[$j]->image_id), $albumimage_url) . '">Edit</a>&nbsp; | &nbsp;';
        $str .= '<a onclick="return confirm(\'Do you want to dlete this? \')" href="' . add_query_arg(array('album_id' => $album_id, 'delete_image' => $albumimages_all[$j]->image_id), $showalbumimage_url) . '" >Delete</a>';
        $str .= '</td>';
        $str .= '</tr>';
    }
    $str .= '</table>';
    return $str;
}

function albumdetails($album_id, $type) {
    global $wpdb;
    $album_details = $wpdb->get_results("SELECT * FROM `spg_album` WHERE album_id='" . $album_id . "'");
    if ($type == "path")
        return $album_details[0]->album_path;
    elseif ($type == "coverphoto")
        return $album_details[0]->coverphoto;
    elseif ($type == "watermark")
        return $album_details[0]->watermark;
    elseif ($type == "foldername")
        return $album_details[0]->folder_name;
    elseif ($type == "albumname")
        return $album_details[0]->album_title;
    elseif ($type == "description") {
        return $album_details[0]->album_description;
    }
}

function albumimagedetails($image_id, $type) {
    global $wpdb;
    $albumimage_details = $wpdb->get_results("SELECT * FROM `spg_albumimages` WHERE image_id='" . $image_id . "'");
    if ($type == "albumid")
        return $albumimage_details[0]->album_id;
    elseif ($type == "imagename")
        return $albumimage_details[0]->image_name;
}

//define('WATERMARK_OVERLAY_IMAGE', 'watermark.png');
define('WATERMARK_OVERLAY_OPACITY', $socialphotogallery_options['watermark_opacity']);
define('WATERMARK_OUTPUT_QUALITY', 90);

function create_watermark($source_file_path, $output_file_path, $watermark) {

    list($source_width, $source_height, $source_type) = getimagesize($source_file_path);
    if ($source_type === NULL) {
        return false;
    }
    switch ($source_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_file_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_file_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_file_path);
            break;
        default:
            return false;
    }

    $overlay_gd_image = imagecreatefrompng($watermark);
    $overlay_width = imagesx($overlay_gd_image);
    $overlay_height = imagesy($overlay_gd_image);
    $watermark_pos_width = ($source_width - $overlay_width) / 2;
    imagecopymerge($source_gd_image, $overlay_gd_image, $watermark_pos_width, $source_height - $overlay_height, 0, 0, $overlay_width, $overlay_height, WATERMARK_OVERLAY_OPACITY);
    switch ($source_type) {
        case IMAGETYPE_GIF:            
            imagegif($source_gd_image, $output_file_path, WATERMARK_OUTPUT_QUALITY);
            break;
        case IMAGETYPE_JPEG:            
            imagejpeg($source_gd_image, $output_file_path, WATERMARK_OUTPUT_QUALITY);
            break;
        case IMAGETYPE_PNG:            
            imagepng($source_gd_image, $output_file_path, WATERMARK_OUTPUT_QUALITY);
            break;
        default:
            return false;
    }    
    imagedestroy($source_gd_image);
    imagedestroy($overlay_gd_image);
}

function process_image_upload($Field, $album_path, $watermark, $multiple = '') {

    $upload_dir = wp_upload_dir();
    $root_album_name = get_option('root_album_name');
    $album_dir = $upload_dir['basedir'] . '/' . $root_album_name . '/test/';
    $regx_num = '/^[0-9]+$/';
    if (preg_match($regx_num, $multiple)) {
        $temp_file_path = $_FILES[$Field]['tmp_name'][$multiple];
        $temp_file_name = $_FILES[$Field]['name'][$multiple];
    } else {
        $temp_file_path = $_FILES[$Field]['tmp_name'];
        $temp_file_name = $_FILES[$Field]['name'];
    }

    list(,, $temp_type) = getimagesize($temp_file_path);
    if ($temp_type === NULL) {
        return false;
    }
    switch ($temp_type) {
        case IMAGETYPE_GIF:
            break;
        case IMAGETYPE_JPEG:
            break;
        case IMAGETYPE_PNG:
            break;
        default:
            return false;
    }
    $uploaded_file_path = $album_path . $temp_file_name;    
    $image = explode('.',basename($temp_file_name));
    $length = COUNT($image);
    if($image[$length-1] == 'jpg')
        $processed_file_path = $album_path . preg_replace('/\\.[^\\.]+$/', '.jpg', $temp_file_name);
    if($image[$length-1] == 'png')
        $processed_file_path = $album_path . preg_replace('/\\.[^\\.]+$/', '.png', $temp_file_name);
    if($image[$length-1] == 'gif')
        $processed_file_path = $album_path . preg_replace('/\\.[^\\.]+$/', '.gif', $temp_file_name);
    
    move_uploaded_file($temp_file_path, $uploaded_file_path);
    
    $result = create_watermark($uploaded_file_path, $processed_file_path, $album_path . '/' . $watermark);
    if ($result === false) {
        return false;
    } else {
        //return array($uploaded_file_path, $processed_file_path);
        return $temp_file_name;
    }
}

function deleteDir($dir) {
    if (substr($dir, strlen($dir) - 1, 1) != '/')
        $dir .= '/';

    if ($handle = opendir($dir)) {
        while ($obj = readdir($handle)) {
            if ($obj != '.' && $obj != '..') {
                if (is_dir($dir . $obj)) {
                    if (!deleteDir($dir . $obj))
                        return false;
                }
                elseif (is_file($dir . $obj)) {
                    if (!unlink($dir . $obj))
                        return false;
                }
            }
        }
        closedir($handle);
        if (!@rmdir($dir))
            return false;
        return true;
    }
    return false;
}
?>