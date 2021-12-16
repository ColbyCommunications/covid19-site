<?php
/**
 * DB upgrder class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_DB_Upgrader' ) ) {

	/**
	 * Define Jet_Popup_DB_Upgrader class
	 */
	class Jet_Popup_DB_Upgrader {

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			/**
			 * Plugin initialized on new Jet_Popup_DB_Upgrader call.
			 * Please ensure, that it called only on admin context
			 */
			$db_updater_data = jet_popup()->module_loader->get_included_module_data( 'cx-db-updater.php' );

			new CX_DB_Updater(
				array(
					'path'      => $db_updater_data['path'],
					'url'       => $db_updater_data['url'],
					'slug'      => 'jet-popup',
					'version'   => '1.5.0',
					'callbacks' => array(
						'1.5.0' => array(
							array( $this, 'update_db_1_5_0' ),
						),
					),
					'labels'    => array(
						'start_update' => esc_html__( 'Start Update', 'jet-popup' ),
						'data_update'  => esc_html__( 'Data Update', 'jet-popup' ),
						'messages'     => array(
							'error'   => esc_html__( 'Module DB Updater init error in %s - version and slug is required arguments', 'jet-popup' ),
							'update'  => esc_html__( 'We need to update your database to the latest version.', 'jet-popup' ),
							'updated' => esc_html__( 'DB Update complete, thank you for updating to the latest version!', 'jet-popup' ),
						),
					),
				)
			);
		}

		/**
		 * Update db updater 1.5.0
		 *
		 * @return void
		 */
		public function update_db_1_5_0() {

			$conditions = jet_popup()->conditions->get_site_conditions();

			if ( empty( $conditions ) ) {
				return false;
			}

			if ( empty( $conditions[ 'jet-popup' ] ) ) {
				return false;
			}

			$popups_conditions = $conditions[ 'jet-popup' ];

			foreach ( $popups_conditions as $popup_id => $popup_data ) {

				$popup_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

				if ( isset( $popup_settings['jet_role_condition'] ) ) {

					$popup_conditions = jet_popup()->conditions->get_popup_conditions( $popup_id );

					$popup_conditions[] = [
						'id'            => uniqid( '_' ),
						'include'       => 'true',
						'group'         => 'advanced',
						'subGroup'      => 'roles',
						'subGroupValue' => $popup_settings['jet_role_condition'],
					];

					jet_popup()->conditions->update_popup_conditions( $popup_id, $popup_conditions );
				}
			}

			do_action( 'jet-popup/db_updater/update' );
		}

	}

}
