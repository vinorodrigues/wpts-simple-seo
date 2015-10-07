<?php
/*
 * Plugin Name: TS Simple SEO Tools
 * Plugin URI: http://tecsmith.com.au
 * Description: Set of tools to aid SEO
 * Author: Vino Rodrigues
 * Version: 0.0.9
 * Author URI: http://vinorodrigues.com
 *
 * @author Vino Rodrigues
 * @package TS-Simple-SEO
 * @since TS-Simple-SEO 0.9
 *
 * Tools include:
 * --------------
 *
 * META Discription's
 * @see http://codex.wordpress.org/Meta_Tags_in_WordPress
 *
**/


// TODO : Minimum requirement WP2.9


function ts_simple_seo_wp_head() {
	global $post;

	if( is_single() || is_page() ) {
		$meta_description = get_post_meta( $post->ID, 'meta_description', true );
		if (empty($meta_description) && ('post' == $post->post_type))
			$meta_description = the_excerpt();
	} else $meta_description = '';

	if (empty($meta_description)) $meta_description = get_bloginfo('description');

	if (!empty($meta_description))
		echo '<meta name="description" content="' . $meta_description . '" />' . PHP_EOL;
}

function ts_simple_seo_meta_box_callback() {
	global $post;
	$meta_description = get_post_meta( $post->ID, 'meta_description', true );

	?>
	<label class="screen-reader-text" for="tsss_meta_description"><?php _e('Meta Description'); ?></label>
	<div class="wp-editor-container">
		<textarea class="wp-editor-area" style="width:100%;resize:none;" rows="1" cols="40" name="tsss_meta_description" id="tsss_meta_description"><?php echo $meta_description; ?></textarea>
	</div>
	<table id="post-status-info" cellspacing="0"><tbody><tr>
		<td style="padding:0 0.5em;"><?php printf(__('Characters left: <strong>%s</strong>', 'ts_simple_seo'), '<span class="tsss-char-count">0</span>'); ?></td>
		<td style="text-align:right;padding:0 0.5em;"><em><?php _e('Google shows 156 Characters (Including Spaces) for Meta Description', 'ts_simple_seo'); ?></em></td>
	</tr></tbody></table>
	<?php
		wp_nonce_field( plugin_basename( __FILE__ ), 'tsss_meta_description_nonce' );
		wp_enqueue_script('jquery');
	?>
	<script type='text/javascript'>
		jQuery(document).ready( function($) {
			$('#tsss_meta_description').each(function() {
				$('.tsss-char-count').html( -($(this).val().length - 156) );
				$(this).keyup(function() {
					$('.tsss-char-count').html( -($(this).val().length - 156) );
				});
			});
		});
	</script>
	<?php
}

function ts_simple_seo_add_meta_boxes() {
	add_meta_box('tsss_post_meta_box',
		__('Meta Description (SEO)', 'ts_simple_seo' ),
		'ts_simple_seo_meta_box_callback',
		'post',
		'advanced',
		'high' );
	add_meta_box('tsss_page_meta_box',
		__('Meta Description (SEO)', 'ts_simple_seo' ),
		'ts_simple_seo_meta_box_callback',
		'page',
		'advanced',
		'high' );
}

function ts_simple_seo_save_post($post_id = 0) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return 0;
	if ( !isset($_POST['tsss_meta_description_nonce']) ||
		!wp_verify_nonce( $_POST['tsss_meta_description_nonce'], plugin_basename( __FILE__ ) ) ) return 0;
	if (!is_admin()) return 0;
	if ($post_id === 0) return 0;

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) return 0;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) ) return 0;
	}

	$value = strip_tags($_POST['tsss_meta_description']);
	$value = preg_replace('/\s\s+/', ' ', $value);
	$value = str_replace('"', '`', $value);

	if ( update_post_meta( $post_id, 'meta_description', esc_attr( $_POST['tsss_meta_description'] ) ) )
		return 1;
	return 0;
}

add_action('add_meta_boxes', 'ts_simple_seo_add_meta_boxes', 5);
if (is_admin()) add_action('save_post', 'ts_simple_seo_save_post', 10, 1);
add_action('wp_head', 'ts_simple_seo_wp_head', 5);

/* eof */
