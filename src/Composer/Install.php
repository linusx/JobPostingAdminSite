<?php
/**
 * Install Script For Composer Requirements
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace Composer;

class Install {

	private static $_our_scss_dir = '/../../assets/scss';
	private static $_our_web_dir = '/../../web';
	private static $_our_js_dir = '/../../assets/js';

	/**
	 * After install is ran on composer copy over
	 * the needed files from the vendor directory.
	 */
	public static function postInstall() {
		self::installBootstrap();
		self::installJquery();
		self::installDataTables();
		self::installFontAwesome();
		self::installFroala();
	}

	/**
	 * Copy over bootstrap scss and js files.
	 */
	private static function installBootstrap() {
		$bs_real_path = realpath( dirname( __FILE__ ) . '/../../vendor/twbs/bootstrap-sass' );
		$our_scss_real_path = realpath( dirname( __FILE__ ) . self::$_our_scss_dir );
		$our_js_real_path = realpath( dirname( __FILE__ ) . self::$_our_js_dir );

		if ( file_exists( $bs_real_path ) && !file_exists( $our_scss_real_path . '/_bootstrap.scss' ) ) {
			self::recurseCopy( $bs_real_path . '/assets/stylesheets', $our_scss_real_path );
		}

		if ( file_exists( $bs_real_path ) && !file_exists( $our_js_real_path . '/bootstrap.js' ) ) {
			self::recurseCopy( $bs_real_path . '/assets/javascripts', $our_js_real_path );
		}

		if ( file_exists( $bs_real_path ) && !file_exists( dirname( __FILE__ ) . '/../../web/fonts' ) ) {
			self::recurseCopy( $bs_real_path . '/assets/fonts', dirname( __FILE__ ) . '/../../web/fonts' );
		}
	}

	/**
	 * Install jQuery files.
	 */
	private static function installJquery() {
		$jq_real_path = realpath( dirname( __FILE__ ) . '/../../vendor/components/jquery' );
		$our_js_real_path = realpath( dirname( __FILE__ ) . self::$_our_js_dir );

		if ( file_exists( $jq_real_path ) && !file_exists( $our_js_real_path . '/jquery.js' ) ) {
			copy( $jq_real_path . '/jquery.js', $our_js_real_path . '/jquery.js' );
		}
	}

	/**
	 * Install jQuery DatTables files.
	 */
	private static function installDataTables() {
		$dt_real_path = realpath( dirname( __FILE__ ) . '/../../vendor/datatables/datatables/media' );
		$our_js_real_path = realpath( dirname( __FILE__ ) . self::$_our_js_dir );
		$our_web_real_path = realpath( dirname( __FILE__ ) . self::$_our_web_dir );

		if ( file_exists( $dt_real_path ) && !file_exists( $our_js_real_path . '/jquery.dataTables.js' ) ) {
			copy( $dt_real_path . '/js/jquery.dataTables.js', $our_js_real_path . '/jquery.dataTables.js' );
		}

		if ( file_exists( $dt_real_path ) && !file_exists( $our_web_real_path . '/css/dataTables.bootstrap.css' ) ) {
			copy( $dt_real_path . '/css/dataTables.bootstrap.css', $our_web_real_path . '/css/dataTables.bootstrap.css' );
		}

		if ( file_exists( $dt_real_path ) ) {
			self::recurseCopy( $dt_real_path . '/images', $our_web_real_path . '/images' );
		}
	}

	private static function installFontAwesome() {
		$dt_real_path = realpath( dirname( __FILE__ ) . '/../../vendor/components/font-awesome' );
		$our_web_real_path = realpath( dirname( __FILE__ ) . self::$_our_web_dir );
		$our_scss_real_path = realpath( dirname( __FILE__ ) . self::$_our_scss_dir );

		if ( file_exists( $dt_real_path ) && !file_exists( $our_scss_real_path . '/font-awesome' ) ) {
			self::recurseCopy( $dt_real_path . '/scss', $our_scss_real_path . '/font-awesome' );
		}

		if ( file_exists( $dt_real_path ) ) {
			self::recurseCopy( $dt_real_path . '/fonts', $our_web_real_path . '/fonts' );
		}
	}

	private static function installFroala() {
		$dt_real_path = realpath( dirname( __FILE__ ) . '/../../vendor/froala/wysiwyg-editor' );
		$our_web_real_path = realpath( dirname( __FILE__ ) . self::$_our_web_dir );

		if ( file_exists( $dt_real_path ) && !file_exists( $our_web_real_path . '/css/froala' ) ) {
			self::recurseCopy( $dt_real_path . '/css', $our_web_real_path . '/css/froala' );
		}

		if ( file_exists( $dt_real_path ) && !file_exists( $our_web_real_path . '/js/froala' ) ) {
			self::recurseCopy( $dt_real_path . '/js', $our_web_real_path . '/js/froala' );
		}
	}

	/**
	 * Recursive file copy.
	 *
	 * @param $src
	 * @param $dst
	 */
	private static function recurseCopy( $src, $dst ) {
		$dir = opendir($src);

		if ( ! file_exists( $dst ) ) {
			mkdir($dst);
		}

		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir($src . '/' . $file) ) {
					self::recurseCopy( $src . '/' . $file,$dst . '/' . $file );
				}
				else {
					copy( $src . '/' . $file,$dst . '/' . $file );
				}
			}
		}
		closedir($dir);
	}

}