<?php
   /*
   Plugin Name: Import SIGE
   Plugin URI: https://github.com/alkn87
   Description: Imports pictures with SIGE markup from Joomla
   Version: 1.1
   Author: Alexander Knapp
   Author URI: https://github.com/alkn87
   License: GPL2
   */

add_action('admin_menu', 'test_button_menu');

function test_button_menu(){
  add_menu_page('Import SIGE', 'Start Import', 'manage_options', 'test-button-slug', 'test_button_admin_page');

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
		//echo "Verzeichnis-Handle: $handle\n";
		//echo "Eintraege:\n";
		$images = array();
		/* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
		while (false !== ($file = readdir($handle))) {
      $extension = pathinfo($file);
			if ($file != "." && $file != ".." && ($extension['extension'] == "jpg" || $extension['extension'] == "png")  ) {
				$images[] = $file;
			}

		}
		closedir($handle);
	}
	$img_array = array();
	foreach($images as $image) {
		//echo '<li><img src="';
		//echo $uploads['baseurl'].'/'.$url.'/'.$image;
		//echo '" alt="" /></li>';
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
    echo 'Ausgegeben wird: '.$filename. '<br>';
		// check if attachment already exists
    global $wpdb;
		$image_src = $wp_upload_dir['url'] . '/' . basename( $filename );
		$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='".$image_src."'";
		$count = $wpdb->get_var($query);

 		if ( !$count ) {
			echo 'working';
      /*
			// Insert the attachment.
      $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
			echo '<h1>Bild erfolgreich hochgeladen: '.$filename.'</h1><br>';

      if (!is_wp_error($attach_id)) {
      // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
      require_once( ABSPATH . 'wp-admin/includes/file.php' );
      require_once( ABSPATH . 'wp-admin/includes/media.php' );
      echo "laeuft<br>";
			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
      echo "generate meta laeuft<br>";
			$attach_metadata = wp_update_attachment_metadata( $attach_id, $attach_data );
      echo "update meta laeuft<br>";
  			if ($is_thumbnail) {
  				$thumb_data = set_post_thumbnail( $parent_post_id, $attach_id );
          echo "set thumb laeuft<br>";
  			}

      }*/

              // Need to require these files
              if ( !function_exists('media_handle_upload') ) {
              require_once(ABSPATH . "wp-admin" . '/includes/image.php');
              require_once(ABSPATH . "wp-admin" . '/includes/file.php');
              require_once(ABSPATH . "wp-admin" . '/includes/media.php');
              }

              $url = $filename;
              $tmp = download_url( $url );
              if( is_wp_error( $tmp ) ){
              // download failed, handle error
              }
              $post_id = $parent_post_id;
              $desc = "";
              $file_array = array();

              // Set variables for storage
              // fix file filename for query strings
              preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
              $file_array['name'] = basename($matches[0]);
              $file_array['tmp_name'] = $tmp;

              // If error storing temporarily, unlink
              if ( is_wp_error( $tmp ) ) {
              @unlink($file_array['tmp_name']);
              $file_array['tmp_name'] = '';
              }

              // do the validation and storage stuff
              $id = media_handle_sideload( $file_array, $parent_post_id, $desc );

              // If error storing permanently, unlink
              if ( is_wp_error($id) ) {
              @unlink($file_array['tmp_name']);
              return $id;
              echo "irgendein Fehler";
              }

              $src = wp_get_attachment_url( $id );

              if ($is_thumbnail) {
        				$thumb_data = set_post_thumbnail( $parent_post_id, $id );
                echo "set thumb laeuft<br>";
        			}

		}

	}


function test_button_action() {
  echo '<div id="message" class="updated fade"><p>'
    .'import started' . '</p></div>';

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
		echo $id;
		//$str = apply_filters( 'the_content', get_the_content() );
    $str = apply_filters('the_content', $post->post_content);
    //echo $str;

		preg_match_all('@\{gallery\}([^,]*?)(?:,single=([^,{]+).*?)?\{/gallery\}@',$str,$out);

		// Matches array:
		//var_export($out);
		//$out = array_unique($out);

		echo "Result folder and thumbnail picture:<br>";

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

		echo "Result folder:<br>";

		// Folders only array:
		$folders_only = array_unique(array_filter(array_slice($out,1)[0],'strlen'));

		if (!empty($folders_only)) {
			foreach($folders_only as $folder) {
				echo $folder;
				echo '<br>';
				//echo $remote_img_dir . $folder;
				//$folder_url = $remote_img_dir . $folder . "/";
				echo '<hr style="border-top: 1px dashed #8c8b8b;">';
				$images_to_upload = get_files_in_dir($folder);
				foreach ($images_to_upload as $img_to_upload) {
					insert_img_to_wp($img_to_upload, 0, $id);
				}
			}

		// Update post
		if (!empty($result) OR !empty($folders_only)) {


			$searchpattern_single = '/\{gallery\}(.*?),single=(.*?){\/gallery\}/';
			$searchpattern_normal = '/\{gallery\}(.*?){\/gallery\}/';
			$new_str = preg_replace($searchpattern_single, "", $str);
			$newer_str = preg_replace($searchpattern_normal, '', $new_str);

      $final_str = $newer_str.'<br>[gallery link="file"]';
      $my_post = array(
				'ID'	=> $id,
				'post_content'	=> $final_str
			);

			// Update the post into the database
			wp_update_post( $my_post );
		}

		echo '<hr>';
		unset($out);
		unset($result);

	}
}
echo "DONE!";

}
?>
