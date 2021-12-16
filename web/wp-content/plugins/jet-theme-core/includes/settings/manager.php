<?php
namespace Jet_Theme_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Controller class
 */
class Settings {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * [$settings description]
	 * @var null
	 */
	public $settings = null;

	/**
	 * [$subpage_modules description]
	 * @var array
	 */
	public $subpage_modules = array();

	/**
	 * [$theme_module_instance description]
	 * @var boolean
	 */
	public $theme_page_instance = false;

	/**
	 * [$backups_manager_instance description]
	 * @var boolean
	 */
	public $backups_manager_instance = false;

	/**
	 * [$kava_info_url description]
	 * @var string
	 */
	public $kava_info_url = 'https://account.crocoblock.com/wp-json/croco/v1/info/';

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	// Here initialize our namespace and resource name.
	public function __construct() {

		$this->subpage_modules = apply_filters( 'jet-tabs/settings/registered-subpage-modules', array(
			'jet-theme-core-general-settings' => array(
				'class' => '\\Jet_Theme_Core\\Settings\\General',
				'args'  => array(),
			),
		) );

		$theme_module_instance = jet_theme_core()->dashboard->get( 'theme' );

		if ( $theme_module_instance ) {
			$this->subpage_modules['jet-theme-core-kava-theme'] = array(
				'class' => '\\Jet_Theme_Core\\Settings\\Kava_Theme',
				'args'  => array(),
			);
		}

		add_action( 'init', array( $this, 'register_settings_category' ) );

		add_action( 'init', array( $this, 'init_plugin_subpage_modules' ) );

		add_action( 'wp_ajax_kava_theme_action', array( $this, 'kava_theme_action' ) );

		add_action( 'wp_ajax_kava_child_theme_action', array( $this, 'kava_child_theme_action' ) );

		add_action( 'wp_ajax_backup_theme_action', array( $this, 'backup_theme_action' ) );

		// Check theme update
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_theme_update' ), 50 );
	}

	/**
	 * [init description]
	 * @return [type] [description]
	 */
	public function register_settings_category() {

		\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_module_category( array(
			'name'     => esc_html__( 'JetThemeCore', 'jet-theme-core' ),
			'slug'     => 'jet-theme-core-settings',
			'priority' => 1
		) );
	}

	/**
	 * [init_plugin_subpage_modules description]
	 * @return [type] [description]
	 */
	public function init_plugin_subpage_modules() {
		require jet_theme_core()->plugin_path( 'includes/settings/subpage-modules/general.php' );
		require jet_theme_core()->plugin_path( 'includes/settings/subpage-modules/kava-theme.php' );

		foreach ( $this->subpage_modules as $subpage => $subpage_data ) {
			\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_subpage_module( $subpage, $subpage_data );
		}
	}

	/**
	 * [get_theme_page_instance description]
	 * @return [type] [description]
	 */
	public function get_theme_page_instance() {

		if ( ! $this->theme_page_instance ) {
			$this->theme_page_instance = jet_theme_core()->dashboard->get( 'theme' );
		}

		return $this->theme_page_instance;
	}

	/**
	 * [get_backups_manager_instance description]
	 * @return [type] [description]
	 */
	public function get_backups_manager_instance() {

		if ( ! $this->backups_manager_instance ) {
			require jet_theme_core()->plugin_path( 'includes/backups.php' );
			$this->backups_manager_instance = new \Jet_Theme_Core_Backups();
		}

		return $this->backups_manager_instance;
	}

	/**
	 * [generate_frontend_config_data description]
	 * @return [type] [description]
	 */
	public function get_settings_config_data() {

		$rest_api_url = apply_filters( 'jet-theme-core/rest/url', get_rest_url() );

		return array(
			'themeData'              => $this->get_theme_data(),
			'childThemeData'         => $this->get_child_theme_data(),
			'settingsApiUrl'         => $rest_api_url . 'jet-theme-core-api/v1/plugin-settings',
			'syncTemplatesApiUrl'    => $rest_api_url . 'jet-theme-core-api/v1/sync-templates',
			'appearanceThemePageUrl' => admin_url( 'themes.php' ),
			'backupList'             => $this->get_theme_backup_list(),
			'settingsData'        => array(
				'pro_relations' => array(
					'value'   => jet_theme_core()->settings->get( 'pro_relations', 'show_both' ),
					'options' => array(
						array(
							'label' => esc_html__( 'Jet Overrides', 'jet-theme-core' ),
							'value' => 'jet_override',
						),
						array(
							'label' => esc_html__( 'Pro Overrides', 'jet-theme-core' ),
							'value' => 'pro_override',
						),
						array(
							'label' => esc_html__( 'Show Both, Jet Before Pro', 'jet-theme-core' ),
							'value' => 'show_both',
						),
						array(
							'label' => esc_html__( 'Show Both, Pro Before Jet', 'jet-theme-core' ),
							'value' => 'show_both_reverse',
						),
					),
				),
				'prevent_pro_locations' => array(
					'value' => jet_theme_core()->settings->get( 'prevent_pro_locations', 'false' ),
				),
				'auto_backup' => array(
					'value' => jet_theme_core()->settings->get( 'auto_backup', 'true' ),
				),
			),

		);
	}

	/**
	 * Get remote data about Kava theme
	 *
	 * @return array
	 */
	public function get_theme_remote_data() {

		$kava_theme_data = get_transient( 'kava_theme_data' );

		if ( ! $kava_theme_data ) {

			$response = wp_remote_get( $this->kava_info_url, array(
				'timeout'   => 60,
				'sslverify' => false,
			) );

			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body, true );

			if ( ! $body || ! isset( $body['success'] ) || true !== $body['success'] ) {
				return false;
			}

			$kava_theme_data = $body;

			set_transient( 'kava_theme_data', $kava_theme_data, DAY_IN_SECONDS );
		}

		return $kava_theme_data;
	}

	/**
	 * [get_theme_data description]
	 * @return [type] [description]
	 */
	public function get_theme_data() {

		$remote_data = $this->get_theme_remote_data();

		$theme_status = $this->get_theme_page_instance()->get_theme_status( $remote_data['theme_slug'] );

		return array(
			'name'            => $remote_data['theme_name'],
			'slug'            => $remote_data['theme_slug'],
			'thumb'           => $remote_data['theme_thumb'],
			'path'            => $remote_data['theme_path'],
			'latestVersion'   => $remote_data['theme_version'],
			'version'         => $theme_status['version'],
			'status'          => $theme_status['code'],
			'statusMessage'   => $theme_status['message'],
			'updateAvaliable' => ( $theme_status['version'] && version_compare( $remote_data['theme_version'], $theme_status['version'], '>' ) ),
		);
	}

	/**
	 * [get_child_theme_data description]
	 * @return [type] [description]
	 */
	public function get_child_theme_data() {

		$remote_data = $this->get_theme_remote_data();

		$theme_status = $this->get_theme_page_instance()->get_child_status( $remote_data['theme_slug'] );

		return array(
			'status'          => $theme_status['code'],
			'statusMessage'   => $theme_status['message'],
		);
	}

	/**
	 * [get_theme_backup_list description]
	 * @return [type] [description]
	 */
	public function get_theme_backup_list() {

		return array_map( function( $backup_item ) {
			$backup_item['download'] = add_query_arg(
				array(
					'jet_action' => 'theme',
					'handle'     => 'download_backup',
					'file'       => urlencode( $backup_item['name'] ),
					'_nonce'     => wp_create_nonce( 'download_backup' ),
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			return $backup_item;
		}, $this->get_backups_manager_instance()->get_backups() );
	}

	/**
	 * [install_kava_theme description]
	 * @return [type] [description]
	 */
	public function kava_theme_action() {

		$data = ( ! empty( $_POST['data'] ) ) ? $_POST['data'] : false;

		if ( ! $data ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Server error', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		$action_type = $data['actionType'];

		switch ( $action_type ) {

			case 'install':
				$remote_data = $this->get_theme_remote_data();

				$kava_theme_url = $remote_data['theme_path'];
				$install_process_data = $this->install_theme_by_url( $kava_theme_url );
				$install_process_data['data'] = $this->get_theme_data();

				wp_send_json( $install_process_data );
			break;

			case 'activate':
				$install_process_data = $this->activate_theme( 'kava' );
				$install_process_data['data'] = $this->get_theme_data();

				wp_send_json( $install_process_data );
			break;

			case 'checkUpdate':

				if ( ! current_user_can( 'update_themes' ) ) {
					wp_send_json( array(
						'status'  => 'error',
						'code'    => false,
						'message' => __( 'You have not permissions to theme updating', 'jet-theme-core' ),
						'data'    => $this->get_theme_data(),
					) );
				}

				set_site_transient( 'update_themes', null );
				set_transient( 'kava_theme_data', null );

				$theme_data = $this->get_theme_data();

				if ( ! $theme_data['updateAvaliable'] ) {
					$message = __( 'You already have the latest theme installed', 'jet-theme-core' );
				} else {
					$message = __( 'New update available', 'jet-theme-core' );
				}

				wp_send_json( array(
					'status'  => 'success',
					'code'    => false,
					'message' => $message,
					'data'    => $this->get_theme_data(),
				) );
			break;

			case 'update':
				$auto_backup = jet_theme_core()->settings->get( 'auto_backup', 'true' );

				if ( filter_var( $auto_backup, FILTER_VALIDATE_BOOLEAN ) ) {
					$theme_data = $this->get_theme_data();
					$this->get_backups_manager_instance()->make_backup( $theme_data['slug'], $theme_data['version'] );
				}

				$install_process_data = $this->update_theme( 'kava' );
				$install_process_data['data'] = $this->get_theme_data();

				wp_send_json( $install_process_data );
			break;

		}

		return false;
	}

	/**
	 * [install_kava_theme description]
	 * @return [type] [description]
	 */
	public function kava_child_theme_action() {

		$data = ( ! empty( $_POST['data'] ) ) ? $_POST['data'] : false;

		if ( ! $data ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Server error', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		$action_type = $data['actionType'];

		switch ( $action_type ) {

			case 'install':
				$remote_data = $this->get_theme_remote_data();

				$kava_child_theme_url = $remote_data['child_theme_path'];
				$install_process_data = $this->install_theme_by_url( $kava_child_theme_url );
				$install_process_data['data'] = $this->get_child_theme_data();

				wp_send_json( $install_process_data );
			break;

			case 'activate':
				$install_process_data = $this->activate_theme( 'kava-child' );
				$install_process_data['data'] = $this->get_child_theme_data();

				wp_send_json( $install_process_data );
			break;

		}

		return false;
	}

	/**
	 * [backup_theme_action description]
	 * @return [type] [description]
	 */
	public function backup_theme_action() {

		$data = ( ! empty( $_POST['data'] ) ) ? $_POST['data'] : false;

		if ( ! $data ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Server error', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		$action_type = $data['actionType'];
		$file        = $data['file'];

		switch ( $action_type ) {

			case 'create':
				$theme_data = $this->get_theme_data();
				$this->get_backups_manager_instance()->make_backup( $theme_data['slug'], $theme_data['version'] );

				wp_send_json( array(
					'status'  => 'success',
					'code'    => false,
					'message' => __( 'Theme backup have been created', 'jet-theme-core' ),
					'data'    => $this->get_theme_backup_list(),
				) );

			break;

			case 'delete':
				global $wp_filesystem;

				$path     = $this->get_backups_manager_instance()->prepare_path( $this->get_backups_manager_instance()->path );
				$filepath = $path . '/' . $file;

				if ( ! $wp_filesystem->exists( $filepath ) ) {
					return array(
						'status'  => 'error',
						'code'    => false,
						'message' => __( 'File not exists', 'jet-theme-core' ),
						'data'    => [],
					);
				}

				$delete = $wp_filesystem->delete( $filepath );

				if ( false === $delete ) {
					return array(
						'status'  => 'error',
						'code'    => false,
						'message' => __( 'Backup deleting error', 'jet-theme-core' ),
						'data'    => [],
					);
				}

				wp_send_json( array(
					'status'  => 'success',
					'code'    => false,
					'message' => __( 'Backup deleting completed', 'jet-theme-core' ),
					'data'    => $this->get_theme_backup_list(),
				) );
			break;

			case 'download':
				$this->get_backups_manager_instance()->download_backup( $file );

				wp_send_json( array(
					'status'  => 'success',
					'code'    => false,
					'message' => __( 'Theme backup have been created', 'jet-theme-core' ),
					'data'    => $this->get_theme_backup_list(),
				) );
			break;
		}

		return false;
	}

	/**
	 * [install_theme_by_url description]
	 * @return [type] [description]
	 */
	public function install_theme_by_url( $url = false ) {

		$status = array();

		if ( ! current_user_can( 'install_themes' ) ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'You are not allowed to install themes.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		if ( ! $url ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Theme URL not found.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Theme_Upgrader( $skin );
		$result   = $upgrader->install( $url );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			return array(
				'status'  => 'error',
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message(),
				'data'    => [],
			);
		} elseif ( is_wp_error( $skin->result ) ) {
			return array(
				'status'  => 'error',
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message(),
				'data'    => [],
			);
		} elseif ( $skin->get_errors()->get_error_code() ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => $result->get_error_message(),
				'data'    => [],
			);
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$errorMessage = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'jet-theme-core' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$errorMessage = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			return array(
				'status'  => 'error',
				'code'    => 'unable_to_connect_to_filesystem',
				'message' => $errorMessage,
				'data'    => [],
			);
		}

		return array(
			'status'  => 'success',
			'code'    => false,
			'message' => __( 'Theme have been installed.', 'jet-theme-core' ),
			'data'    => [],
		);
	}

	/**
	 * [activate_theme description]
	 * @param  boolean $slug [description]
	 * @return [type]        [description]
	 */
	public function activate_theme( $slug = false ) {

		if ( ! current_user_can( 'switch_themes' ) ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'You are not allowed to install themes.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		if ( ! $slug ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Theme slug not found.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		$themes = wp_get_themes();

		if ( ! isset( $themes[ $slug ] ) ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Theme not found.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		switch_theme( $slug );

		return array(
			'status'  => 'success',
			'code'    => false,
			'message' => __( 'Theme have been activated.', 'jet-theme-core' ),
			'data'    => [],
		);
	}

	/**
	 * Update theme handler
	 *
	 * @return void
	 */
	public function update_theme( $slug = false ) {

		if ( ! current_user_can( 'update_themes' ) ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'You are not allowed to update themes.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		if ( ! $slug ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => __( 'Theme slug not found.', 'jet-theme-core' ),
				'data'    => [],
			);
		}

		$stylesheet = preg_replace( '/[^A-z0-9_\-]/', '', wp_unslash( $slug ) );

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$current = get_site_transient( 'update_themes' );

		if ( empty( $current ) ) {
			wp_update_themes();
		}

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Theme_Upgrader( $skin );
		$result   = $upgrader->bulk_upgrade( array( $stylesheet ) );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $skin->result ) ) {
			return array(
				'status'  => 'error',
				'code'    => $skin->result->get_error_code(),
				'message' => $skin->result->get_error_message(),
				'data'    => [],
			);
		} elseif ( $skin->get_errors()->get_error_code() ) {
			return array(
				'status'  => 'error',
				'code'    => false,
				'message' => $skin->get_error_messages(),
				'data'    => [],
			);
		} elseif ( is_array( $result ) && ! empty( $result[ $stylesheet ] ) ) {

			// Theme is already at the latest version.
			if ( true === $result[ $stylesheet ] ) {
				return array(
					'status'  => 'error',
					'code'    => false,
					'message' => $upgrader->strings['up_to_date'],
					'data'    => [],
				);
			}

			return array(
				'status'  => 'success',
				'code'    => false,
				'message' => __( 'Theme have been updated.', 'jet-theme-core' ),
				'data'    => [],
			);
		} elseif ( false === $result ) {
			global $wp_filesystem;

			$error_message = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'jet-theme-core' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$error_message = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			return array(
				'status'  => 'error',
				'code'    => 'unable_to_connect_to_filesystem',
				'message' => $error_message,
				'data'    => [],
			);
		}

		return array(
			'status'  => 'error',
			'code'    => false,
			'message' => __( 'Update failed.', 'jet-theme-core' ),
			'data'    => [],
		);
	}

	/**
	 * Check theme updates
	 *
	 * @param  array $data
	 * @return array
	 */
	public function check_theme_update( $data ) {

		$theme_data = $this->get_theme_data();

		if ( ! $theme_data['version'] || ! $theme_data['latestVersion'] ) {
			return $data;
		}

		if ( ! $theme_data['updateAvaliable'] ) {
			return $data;
		}

		$update = array();

		$update['theme']       = $theme_data['slug'];
		$update['new_version'] = $theme_data['latestVersion'];
		$update['url']         = '';
		$update['package']     = $theme_data['path'];

		$data->response[ $theme_data['slug'] ] = $update;

		return $data;
	}

}

