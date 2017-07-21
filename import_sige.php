<?php
   /*
   Plugin Name: Import SIGE
   Plugin URI: http://my-awesomeness-emporium.com
   Description: a plugin to create awesomeness and spread joy
   Version: 1.2
   Author: Mr. Awesome
   Author URI: http://mrtotallyawesome.com
   License: GPL2
   */

add_action('admin_menu', 'test_button_menu');

function test_button_menu(){
  add_menu_page('Test Button Page', 'Test Button', 'manage_options', 'test-button-slug', 'test_button_admin_page');

}

function test_button_admin_page() {

  // This function creates the output for the admin page.
  // It also checks the value of the $_POST variable to see whether
  // there has been a form submission.

  // The check_admin_referer is a WordPress function that does some security
  // checking and is recommended good practice.

  // General check for user permissions.
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }

  // Start building the page

  echo '<div class="wrap">';

  echo '<h2>Bilder importieren</h2>';

  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['test_button']) && check_admin_referer('test_button_clicked')) {
    // the button has been pressed AND we've passed the security check
    test_button_action();
  }

  echo '<form action="options-general.php?page=test-button-slug" method="post">';

  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
  wp_nonce_field('test_button_clicked');
  echo '<input type="hidden" value="true" name="test_button" />';
  submit_button('Call Function');
  echo '</form>';

  echo '</div>';

}

function get_files_in_dir($url){
	$uploads = wp_upload_dir();
	if ($handle = opendir($uploads['basedir'].'/'.$url)) {
		echo "Verzeichnis-Handle: $handle\n";
		echo "Einträge:\n";
		$images = array();
		/* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$images[] = $file;
			}

		}
		closedir($handle);
	}
	$img_array = array();
	foreach($images as $image) {
/* 		echo '<li><img src="';
		echo $uploads['baseurl'].'/'.$url.'/'.$image;
		echo '" alt="" /></li>'; */
		$img_array[] = $uploads['baseurl'].'/'.$url.'/'.$image;
	}
	return $img_array;

}

function insert_img_to_wp($filename, $is_thumbnail, $parent_post_id) {
		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// check if attachment already exists
		global $wpdb;
		$image_src = $wp_upload_dir['url'] . '/' . basename( $filename );
		$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='".$image_src."'";
		$count = $wpdb->get_var($query);
		echo $filename;
 		/*if ( !$count ) {
			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			if ($is_thumbnail) {
				set_post_thumbnail( $parent_post_id, $attach_id );
			}
		}*/
	}


function test_button_action()
{
  echo '<div id="message" class="updated fade"><p>'
    .'The "Call Function" button was clicked.' . '</p></div>';

  //$path = WP_TEMP_DIR . '/test-button-log.txt';

  //$handle = fopen($path,"w");

/*   if ($handle == false) {
    echo '<p>Could not write the log file to the temporary directory: ' . $path . '</p>';
  }
  else {
    echo '<p>Log of button click written to: ' . $path . '</p>';

    fwrite ($handle , "Call Function button clicked on: " . date("D j M Y H:i:s", time()));
    fclose ($handle);
  } */

	//$remote_img_dir = "http://www.ff-pressbaum.at/images/stories/";
	$remote_img_dir = "<wwwroot>/images/stories/";
	$args = array(
        'numberposts' => -1,
        'category' => 0, 'orderby' => 'date',
        'order' => 'DESC', 'include' => array(),
        'exclude' => array(), 'meta_key' => '',
        'meta_value' =>'', 'post_type' => 'post',
        'suppress_filters' => true
    );

	$latest_posts = get_posts( $args );
	foreach ( $latest_posts as $post ) {
		setup_postdata( $post );
        //echo "<h2><a href=" + the_permalink() + ">" + the_title() + "</a></h2>";
        //the_content();
		$id = $post->ID;
    $title = get_the_title($post)
		$str = apply_filters( 'the_content', get_the_content() );

    echo "Beitrag mit ID <b>".$id."</b> hat folgende Files bzw. Folder:<br>";

		preg_match_all('@\{gallery\}([^,]*?)(?:,single=([^,{]+).*?)?\{/gallery\}@',$str,$out);

		// Matches array:
		//var_export($out);
		//$out = array_unique($out);

		// Path + Image files array:
		foreach(array_unique($out[2]) as $i=>$v){
			if($v){
				$result[]="{$out[1][$i]}/$v";
			}
		}

		if (!empty($result)) {
			foreach($result as $element){
				echo $element;
				echo '<br>';
				$uploaddir = wp_upload_dir();
				echo $uploaddir['baseurl'] . '/' . $element;
				echo '<hr style="border-top: 1px dashed #aa8b8b;">';
				insert_img_to_wp($uploaddir['baseurl'] . '/' . $element, 1, $id);
			}
		}


		// Folders only array:
		$folders_only = array_unique(array_filter(array_slice($out,1)[0],'strlen'));

		if (!empty($folders_only)) {
			foreach($folders_only as $folder) {
				echo $folder;
				echo '<br>';
				//$folder_url = $remote_img_dir . $folder . "/";
				echo '<hr style="border-top: 1px dashed #8c8b8b;">';
				$images_to_upload = get_files_in_dir($folder);
				foreach ($images_to_upload as $img_to_upload) {
					insert_img_to_wp($img_to_upload, 0, $id);
				}
			}
		}

		// Update post
		/*if (!empty($result) OR !empty($folders_only)) {
			$searchpattern_single = '/\{gallery\}(.*?),single=(.*?){\/gallery\}/';
			$searchpattern_normal = '/\{gallery\}(.*?){\/gallery\}/';
			$new_str = preg_replace($searchpattern_single, "", $str);
			$final_str = preg_replace($searchpattern_normal, '[gallery link="file"]', $new_str);
			$my_post = array(
				'ID'	=> $id,
				'post_content'	=> $final_str
			);

			// Update the post into the database
			wp_update_post( $my_post );
		}*/

		echo '<hr>';
		$out = "";
		$result = "";

	}

}
?>
