<?php
/*
Plugin Name: Drop-zone
Plugin URI: http://www.fosseus.se
Description: Drag drop frontend widget
Author: Johannes Fosseus
Version: 1.1
*/

include(ABSPATH . 'wp-includes/pluggable.php');
include('drop-zone.class.php');

if (class_exists('DropZone')){
	$DropZone = new DropZone();
}

add_action('widgets_init', 'drop_zone_init');

get_currentuserinfo();
if ($userdata->wp_user_level > 0) {

	wp_enqueue_script('drop_zone_js', WP_PLUGIN_URL.'/drop-zone/drop-zone.js',array('jquery'));
	wp_enqueue_style('drop_zone_css', WP_PLUGIN_URL.'/drop-zone/drop-zone.css');

	add_action('wp_ajax_drop-zone-submit', 'drop_zone_drop'); // Register ajax actions
	add_action('wp_ajax_drop-zone-remove', 'drop_zone_remove'); // Register ajax actions

	// add the ajaxurl+nonce to the front
	wp_localize_script('drop_zone_js','DropZoneFront', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('drop-zone-nonce')
		)
	);

}

function drop_zone_init(){
	register_widget('DropZone');
}

// ajax callback
function drop_zone_drop() {
	global $wpdb,$DropZone;

	// secutity
	if (!wp_verify_nonce($_GET['nonce'],'drop-zone-nonce')){
		die();
	}

	// find the "widgets id"
	$widget_id = substr($_GET['infoBlock']['position'], strpos($_GET['infoBlock']['position'], "-") + 1);

	// use it to get the specific widget options
	$instance = get_option('widget_drop_zone');
	$show_excerpt = $instance[$widget_id]['show_excerpt'];
	$show_post_thumbnails = $instance[$widget_id]['show_post_thumbnails'];
	$thumbnail_max_width = $instance[$widget_id]['thumbnail_max_width'];
	$thumbnail_max_height = $instance[$widget_id]['thumbnail_max_height'];

	$postID = url_to_postid($_GET['infoBlock']['url']); // get the postID

	// only get thumbnail_id if we need to and height/width is set
	if($show_post_thumbnails && $thumbnail_max_width && $thumbnail_max_height){
		$post_thumbnail_id = get_post_thumbnail_id($postID);
	}

	if($postID){
		$post = get_post($postID); // get the post, post_excerpt
		$drop_zone_excerpt = $DropZone->trim_excerpt($post->post_content,20); // the drop-zone excerpt

		echo "<div class=\"widget-container\" data-edited=\"true\" data-url=\"".$_GET['infoBlock']['url']."\" data-index=\"0\" data-removable=\"true\" data-droppable=\"true\" data-position=\"".$_GET['infoBlock']['position']."\">";

		if($post_thumbnail_id){
			$img_url = $DropZone->get_image_url($post_thumbnail_id, $thumbnail_max_width, $thumbnail_max_height);
			echo "<a href=\"".$link."\"><img src=\"".$img_url."\"></a>";
		}

		echo "<h3 class=\"widget-title\"><a href=\"".$_GET['infoBlock']['url']."\">".$post->post_title."</a></h3>";

		if($show_excerpt){
		echo "<a href=\"".$_GET['infoBlock']['url']."\">".$drop_zone_excerpt."</a>";
		}

		echo "</div>";

		// and svave
		$DropZone->save_on_drop($_GET['infoBlock']['position'],$postID);
	}

	die;
}

// save the dropped zones
function drop_zone_remove(){
	global $wpdb,$DropZone;
	if (!wp_verify_nonce($_POST['nonce'],'drop-zone-nonce')){
		die();
	}
	$postID = url_to_postid($_POST['url']);
	$DropZone->remove($_POST['position'],$postID);
	die;
}

