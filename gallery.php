<?php

function album_gallery_func($atts = array()) {
    global $wpdb,$socialphotogallery_options;
    if(is_array($atts)) extract($atts);
    $pageurl = get_permalink();
    $upload_dir = wp_upload_dir();
    $root_album_name = get_option('root_album_name');
    $album_path = get_option('album_path');
    $root_album_url = $upload_dir['baseurl'] . '/' . $root_album_name . '/';
    setcookie('url', $url, time()+3600*60*24, COOKIEPATH, COOKIE_DOMAIN, false);
    if (isset($_GET['album_id']) && $_GET['album_id'] != "" && $_GET['show'] == 'gallery') {
        gallery_image_func($_GET['album_id'], $root_album_url,$url);
    } else {
        $album_sql = "SELECT * FROM `spg_album` ORDER BY datetime DESC";
        $album_all = $wpdb->get_results($album_sql);
        ?><ul class="gallery clearfix"><?php
        for ($i = 0; $i < COUNT($album_all); $i++) {
            $coverphoto = $root_album_url . $album_all[$i]->folder_name . "/" . $album_all[$i]->coverphoto;
            ?>
                <li> 
                    <a href="<?= add_query_arg(array('album_id' => $album_all[$i]->album_id, 'show' => 'gallery'), $pageurl) ?>">    
                        <img src="<?= $coverphoto ?>" width="200" height="150" class="img" id="<?= $album_all[$i]->album_id ?>"/>
                        <p class="title" id="caption_<?= $album_all[$i]->album_id ?>" >
            <?= $album_all[$i]->album_title ?><br />
            <?= $album_all[$i]->datetime ?>
                        </p>
                    </a>
                </li>
        <?php } ?>
        </ul>

                        <?php
                    }
                }
                function gallery_image_func($album_id, $root_album_url,$url) {
                    global $wpdb,$socialphotogallery_options;
                    $pageURL .= 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
                    $foldername = albumdetails($_GET['album_id'], "foldername");
                    $total_images_sql = "SELECT * FROM `spg_albumimages` WHERE album_id='" . $_GET['album_id'] . "'";
                    $total_images = $wpdb->get_results($total_images_sql);
                    $album_description = stripslashes(albumdetails($album_id, 'description'));
                    $album_name = albumdetails($album_id, 'albumname');
                    if (count($total_images) > 0) {
                        $perpageview = $socialphotogallery_options['paginationrange'];
                        $start = $_GET['start'];
                        $index = ceil(COUNT($total_images) / $perpageview);
                        if ((COUNT($total_images) / $perpageview) == $index)
                            $end = $index;
                        else
                            $end = $index - 1;
                        if ($start == "") {
                            $start = 0;
                        }
                        $images_sql = "SELECT * FROM `spg_albumimages` WHERE album_id='" . $_GET['album_id'] . "' ORDER BY datetime DESC LIMIT " . $start . "," . $perpageview;
                        $images_all = $wpdb->get_results($images_sql);
                        ?>
        <div style="width: 630px;font-size: 15px; text-align: center; padding:5px 0 ;">
        <b><?= $album_name ?></b>
        </div>            
<div class="<?= $socialphotogallery_options['description_class'] ?>"><?= $album_description ?></div>
       
<div style="margin-top: 120px;" >
        <ul class="gallery clearfix"><?php
        for ($j = 0; $j < COUNT($images_all); $j++) {
            $image = $root_album_url . $foldername . '/' . $images_all[$j]->image_name;
            $album_name = albumdetails($album_id, 'albumname');
                            ?>            
                <li >
                    <div class="g_link">
                        <a href="<?= get_permalink($images_all[$j]->post_id) ?>" rel="" title="<?= $images_all[$j]->image_caption ?>"  >
                            <img  src="<?= $image ?>" width="180px" height="166px"  class="img" id="<?= $images_all[$j]->image_id ?>"  />
                            <p class="title" id="caption_<?= $images_all[$j]->image_id ?>" >
            <?= $images_all[$j]->image_caption ?>                        
                            </p>
                        </a>
                    </div>
                </li>
        <?php } ?>
        </ul>
</div>
        <input type="hidden" name="url" id="url" value="<?= get_bloginfo('url') ?>" />
        <input type="hidden" name="facebook_image" id="facebook_image" value="<?= plugins_url('/images/facebook.png', __FILE__) ?>" />
            <?php if (COUNT($total_images) > $perpageview) { ?>
            <div style="float: left; width:100%; text-align: left; margin-top:100px;font-size: 16px;margin-bottom: 15px;">
                <!-- ================= PREVIOUS LINK ================== -->
            <?php
            if ($start == 0)
                $start_prev = $start;
            else
                $start_prev = $start - $perpageview;
            ?>
                <a href="<?= add_query_arg(array('start' => $start_prev), $pageURL) ?>"><?= $socialphotogallery_options['previous_link_text'] ?></a>
                <!-- ================================================ --> 
                <?php
                $start_pos = 0;
                for ($c = 1; $c <= $index; $c++) {
                    if ($c > 1)
                        $start_pos = $start_pos + $perpageview;
                    ?>
                    <a href="<?= add_query_arg(array('start' => $start_pos), $pageURL) ?>"><?= $c ?></a>
            <?php } ?>
                <!-- ================= PREVIOUS LINK ================== -->
                <?php
                if ($start == $end * $perpageview)
                    $start_next = $start;
                else
                    $start_next = $start + $perpageview;
                ?>
                <a href="<?= add_query_arg(array('start' => $start_next), $pageURL) ?>"><?= $socialphotogallery_options['next_link_text'] ?></a>
                <!-- ================================================ -->
            </div>
            <?php } ?>
        <?php } else { ?>
        <div><strong>No Images Available</strong></div>
    <?php
    } ?><br clear="all" />
        <div style="float: left;margin:20px 0 0 0;">
        <a href="<?= $url ?>" class="<?= $socialphotogallery_options['link_class'] ?>"><?= $socialphotogallery_options['back_to_album_text'] ?></a>
        </div>
        <?php } ?>