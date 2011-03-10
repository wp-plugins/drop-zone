<?php
class DropZone extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_drop_zone', 'description' => 'Add draggable areas to your front-end');
		parent::__construct( 'drop_zone', 'Drop-zone', $widget_ops );
	}

	// show the widget frontend drop-zone
	function widget($args, $instance){

		$show_excerpt = $instance['show_excerpt'];
		$show_post_thumbnails = $instance['show_post_thumbnails'];
		$thumbnail_max_width = $instance['thumbnail_max_width'];
		$thumbnail_max_height = $instance['thumbnail_max_height'];

		$postID = get_option($args['id']);

		// check if we have a thumb and the height/width is set
		if($show_post_thumbnails && $thumbnail_max_width && $thumbnail_max_height){
			$post_thumbnail_id = get_post_thumbnail_id($postID);
		}

		if($postID){

			$post = get_post($postID); // get the post, post_excerpt
			$link = get_permalink($postID);
			$drop_zone_excerpt = $this->trim_excerpt($post->post_content,20); // the drop-zone excerpt
			$the_widgets_pos_id = substr($args['widget_id'], strpos($args['widget_id'], "-") + 1); // we need the id

			echo "<div class=\"widget-container\" data-url=\"".$link."\" data-index=\"0\" data-removable=\"true\" data-droppable=\"true\" data-position=\"".$args['id'].":".$the_widgets_pos_id."\">";

			if($post_thumbnail_id){
				$img_url = $this->get_image_url($post_thumbnail_id, $thumbnail_max_width, $thumbnail_max_height);
				echo "<a href=\"".$link."\"><img src=\"".$img_url."\"></a>"; // size/width missing, and image is not cropped...
			}

			echo $args['before_title']."<a href=\"".$link."\">".$post->post_title."</a>".$args['after_title'];

			if($show_excerpt){
			echo "<a href=\"".$link."\">".$drop_zone_excerpt."</a>";
			}

			echo "</div>";
		} else {
			echo "<div class=\"droppable widget-container\" data-url=\"".$post->guid."\" data-index=\"0\" data-droppable=\"true\" data-position=\"".$args['id'].":".$the_widgets_pos_id."\">";
			echo "</div>";
		}
    }

    // the backend widget settings
    function form($instance) {

		// get the values
		$show_excerpt = esc_attr($instance['show_excerpt']);
		$show_post_thumbnails = esc_attr($instance['show_post_thumbnails']);
		$thumbnail_max_width = esc_attr($instance['thumbnail_max_width']);
		$thumbnail_max_height = esc_attr($instance['thumbnail_max_height']);

		// set the checkboxes
		if($show_excerpt){ $show_excerpt = " checked=\"checked\""; }
		if($show_post_thumbnails){ $show_post_thumbnails = " checked=\"checked\""; }

		// print the stuff
    	echo "<p>";
	    	echo "<input type=\"checkbox\" name=\"".$this->get_field_name('show_excerpt')."\" id=\"".$this->get_field_id('show_excerpt')."\"".$show_excerpt."> ";
			echo "<label for=\"".$this->get_field_id('show_excerpt')."\">".__('Show excerpt')."</label><br />";
			echo "<input type=\"checkbox\" name=\"".$this->get_field_name('show_post_thumbnails')."\" id=\"".$this->get_field_id('show_post_thumbnails')."\"".$show_post_thumbnails."> ";
			echo "<label for=\"".$this->get_field_id('show_post_thumbnails')."\">".__('Show Post Thumbnails')."</label>";
		echo "</p>";

		echo "<p>";
			echo "<label for=\"".$this->get_field_id('thumbnail_max_width')."\">".__('Thumbnail max width')."</label>";
			echo "<input class=\"widefat\" id=\"".$this->get_field_id('thumbnail_max_width')."\" name=\"".$this->get_field_name('thumbnail_max_width')."\" type=\"text\" value=\"".$thumbnail_max_width."\"/>";
		echo "</p>";

    	echo "<p>";
			echo "<label for=\"".$this->get_field_id('thumbnail_max_height')."\">".__('Thumbnail max height')."</label>";
			echo "<input class=\"widefat\" id=\"".$this->get_field_id('thumbnail_max_height')."\" name=\"".$this->get_field_name('thumbnail_max_height')."\" type=\"text\" value=\"".$thumbnail_max_height."\"/>";
		echo "</p>";

    }

    // Update the widget data
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['show_excerpt'] = strip_tags($new_instance['show_excerpt']);
		$instance['show_post_thumbnails'] = strip_tags($new_instance['show_post_thumbnails']);
		$instance['thumbnail_max_width'] = strip_tags($new_instance['thumbnail_max_width']);
		$instance['thumbnail_max_height'] = strip_tags($new_instance['thumbnail_max_height']);
		return $instance;
    }


    // strip the post to a functional "excerpt"
    function trim_excerpt($text, $length = 55) {
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_more = '[...]';
		$words = preg_split("/[\n\r\t ]+/", $text, $length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
		return $text;
	}

	// save on drop, the new stuff
	function save_on_drop($position,$post_id){
		if($post_id != 0 && !empty($post_id)){
			update_option($position,$post_id);
		}
	}

	// remove from db
	function remove($position,$postID){
		if($postID != 0 && !empty($postID)){
			delete_option($position,$postID);
		}
	}

	// downsize the image
	function get_image_url($id, $width = false, $height = false) {

		$attachment = wp_get_attachment_metadata( $id );
		$attachment_url = wp_get_attachment_url( $id );

		if (isset($attachment_url)) {
			if ($width && $height) {
				$uploads = wp_upload_dir();
				$imgpath = $uploads['basedir'].'/'.$attachment['file'];
				$image = image_resize( $imgpath, $width, $height );
				if ( $image && !is_wp_error( $image ) ) {
					$image = path_join( dirname($attachment_url), basename($image) );
				} else {
					$image = $attachment_url;
				}
			} else {
				$image = $attachment_url;
			}
			if (isset($image)) {
				return $image;
			}
		}
	}

}