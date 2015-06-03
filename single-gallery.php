<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

    <?php
     global $wpdb,$url;    
     $socialphotogallery_options = get_option("socialphotogallery_options");
     $upload_dir =wp_upload_dir();
     $root_album_name = get_option('root_album_name');         
     $root_album_url = $upload_dir['baseurl'].'/'.$root_album_name.'/';
     
     $sql = "SELECT * FROM `spg_albumimages` WHERE post_id ='".$post->ID."'";
     $results = $wpdb->get_results($sql);     
     $foldername = albumdetails($results[0]->album_id,"foldername");    
     $image = $root_album_url.$foldername.'/'.$results[0]->image_name;     
     $album_name = albumdetails($results[0]->album_id,'albumname');
     
    ?>
<div>
    <div style="width: 630px;font-size: 15px; text-align: center; padding:5px 0 ;"><b><?= $results[0]->image_caption ?></b></div>
    <div style="width: 630px;font-size: 15px; text-align: center; padding:5px 0 ;"><b><?= $album_name ?></b></div>
    <div><img src="<?= $image ?>" /></div>
     <div style="width: 630px;text-align: center; padding: 10px 0 0 0;"><?= $socialphotogallery_options['addthiscontent'] ?></div>
</div>
<div>
    <a href="<?= add_query_arg(array('album_id'=>$results[0]->album_id,'show'=>'gallery'),$_COOKIE['url']) ?>" class="<?= $socialphotogallery_options['link_class'] ?>"><?= $socialphotogallery_options['back_to_gallery_text'] ?></a>
</div>

<?php get_footer(); ?>