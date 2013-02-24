
http://kawaii-mobile.com/2013/02/high-school-dxd-2/high-school-dxd-akeno-himejima-htc-one-x-wallpaper-720x1280/custom-image/7594/320x480/ 
http://kawaii-mobile.com/2013/02/high-school-dxd-2/high-school-dxd-akeno-himejima-htc-one-x-wallpaper-720x1280/custom-image/7594/480x640/ 
http://kawaii-mobile.com/2013/02/koi-to-senkyo-to-chocolate/koi-to-senkyo-to-chocolate-ai-sarue-htc-windows-phone-8x-wallpaper-kii-monzennaka-720x1280/custom-image/7674/480x800/ 

TEST:
http://kawaii-mobile.org/2013/02/koi-to-senkyo-to-chocolate/koi-to-senkyo-to-chocolate-ai-sarue-htc-windows-phone-8x-wallpaper-kii-monzennaka-720x1280/custom-image/6088/480x800

http://kawaii-mobile.org/2013/02/koi-to-senkyo-to-chocolate/koi-to-senkyo-to-chocolate-ai-sarue-htc-windows-phone-8x-wallpaper-kii-monzennaka-720x1280/custom-image/6088/480x800/




http://www.wordpressplugins.ru/function_reference/get_header.html

-------------------------

Redirect info:
http://dev.xiligroup.com/?p=27




define('ADINJ_PATH', WP_PLUGIN_DIR.'/ad-injection');
define('ADINJ_CONFIG_FILE', WP_CONTENT_DIR . '/ad-injection-config.php');
define('ADINJ_AD_PATH', WP_PLUGIN_DIR.'/ad-injection-data');


	function start() {
		// Setup the various filters and actions that allow Redirection to h appen
		add_action( 'init',                    array( &$this, 'init' ) );
		add_action( 'send_headers',            array( &$this, 'send_headers' ) );
		add_filter( 'permalink_redirect_skip', array( &$this, 'permalink_redirect_skip' ) );
		add_filter( 'wp_redirect',             array( &$this, 'wp_redirect' ), 1, 2 );

		// Remove WordPress 2.3 redirection
		// XXX still needed?
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		remove_action( 'edit_form_advanced', 'wp_remember_old_slug' );
	}

