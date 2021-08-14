<?php
/**
 * Plugin Name: WPSE 393178
 * Plugin URI: https://wordpress.stackexchange.com/a/393178
 * Description: Setting locale before retrieving gettext translations using <code>switch_to_locale()</code> :)
 * Author: Sally CJ
 * Version: 1.0
 * Text Domain: wpse-393178
 * Domain Path: /languages
 */

add_action( 'init', 'wpse_393178_load_textdomain' );
function wpse_393178_load_textdomain() {
// verify if the text domain load succeeded
	if ( ! load_plugin_textdomain( 'wpse-393178', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ) ) { echo "ohoh!"; } 
}

// Helper function to output a notice div.
function wpse_393178_admin_notice( $message, $type = 'info' ) {
	$type = $type ? $type : 'info';
	?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo $message; ?></p>
		</div>
	<?php
}

add_action( 'admin_notices', 'wpse_393178_admin_notices' );
function wpse_393178_admin_notices() {
	// For testing the AJAX stuff below.
	$div = '<div class="wpse-393178-tmp hide-if-js"><p><a href="#" class="button">Load AJAX text</a></p></div>';

	wpse_393178_admin_notice(
		__( 'Sample info text (before switching locale)', 'wpse-393178' ) . $div .
		'<i>Add <code>?lc=en_US</code> or <code>&lc=en_US</code> (i.e. <code>lc=LOCALE</code>) ' .
		'to this page URL to test <code>switch_to_locale()</code> and <code>restore_previous_locale()</code>, ' .
		'or otherwise then <code>get_user_locale()</code> will be used. (** <b><i>this text is ' .
		'intentionally not localized</i></b>)'
	);

	if ( $switched_locale = switch_to_locale( $_GET['lc'] ?? get_user_locale() ) ) {
		wpse_393178_admin_notice(
			sprintf(
				/* translators: %s: Locale like de_DE */
				__( 'Sample success text (after switching locale to <b>%s</b>)', 'wpse-393178' ),
				get_locale()
			),
			'success'
		);

		restore_previous_locale();     // switch back to the original/prevous locale
		wpse_393178_load_textdomain(); // and then re-load the plugin's translations

		wpse_393178_admin_notice(
			__( 'Sample info text 2 (after locale restored)', 'wpse-393178' ) . $div
		);
	} else {
		wpse_393178_admin_notice(
			( determine_locale() === get_user_locale() ) ?
				__( 'Locale was not switched because user and the site locale are identical', 'wpse-393178' ) :
				__( 'Locale was not switched because: <b>Reason not known</b>..', 'wpse-393178' ),
			'warning'
		);
	}
}

// Just a dummy function for testing the plugin translation in admin AJAX. (Just for
// a quick test, I deliberately didn't use the REST API..
add_action( 'wp_ajax_wpse-393178', function () {
	printf(
		/* translators: %d: An mt_rand() value */
		__( 'Success! Random number: %d', 'wpse-393178' ),
		mt_rand()
	);

	wp_die();
} );

add_action( 'admin_print_footer_scripts', function () {
	if ( wp_script_is( 'jquery' ) ) :
	?>
		<script>
			jQuery( '.wpse-393178-tmp' ).fadeIn().on( 'click', 'a', function ( e ) {
				e.preventDefault();

				const btn = this;
				btn.disabled = true;

				jQuery.get( ajaxurl + '?action=wpse-393178&t=' + Date.now() ).done(
					res => jQuery( this ).replaceWith( res )
				// the text below is intentionally not localized
				).fail( () => alert( 'Request failed! Try again later..' ) ).always(
					() => btn.disabled = false
				);
			} );
		</script>
	<?php
	endif;
} );
