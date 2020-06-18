 <cx-vui-collapse
	:collapsed="false"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Options', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
		<cx-vui-select
			name="jet-menu-animation"
			label="<?php _e( 'Animation', 'jet-menu' ); ?>"
			description="<?php _e( 'Choose an animation effect for sub menu', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="pageOptions['jet-menu-animation']['options']"
			v-model="pageOptions['jet-menu-animation']['value']">
		</cx-vui-select>

		<cx-vui-switcher
			name="jet-menu-roll-up"
			label="<?php _e( 'Menu RollUp', 'jet-menu' ); ?>"
			description="<?php _e( 'Enable this option in order to reduce the menu size by groupping extra menu items and hiding them under the suspension dots.', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-menu-roll-up']['value']">
		</cx-vui-switcher>

		<cx-vui-switcher
			name="jet-menu-mega-ajax-loading"
			label="<?php _e( 'Use ajax loading', 'jet-menu' ); ?>"
			description="<?php _e( 'Use ajax loading for mega content', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-menu-mega-ajax-loading']['value']">
		</cx-vui-switcher>

		<cx-vui-select
			name="jet-menu-show-for-device"
			label="<?php _e( 'Device view', 'jet-menu' ); ?>"
			description="<?php _e( 'Choose witch menu view you want to display', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="pageOptions['jet-menu-show-for-device']['options']"
			v-model="pageOptions['jet-menu-show-for-device']['value']">
		</cx-vui-select>

		<cx-vui-input
			name="jet-menu-mouseleave-delay"
			label="<?php _e( 'Mouse leave delay(ms)', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			type="number"
			:min="0"
			:max="10000"
			:step="100"
			v-model="pageOptions['jet-menu-mouseleave-delay']['value']">
		</cx-vui-input>

		<cx-vui-select
			name="jet-mega-menu-width-type"
			label="<?php _e( 'Mega menu base width', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="pageOptions['jet-mega-menu-width-type']['options']"
			v-model="pageOptions['jet-mega-menu-width-type']['value']">
		</cx-vui-select>

		<cx-vui-input
			name="jet-mega-menu-selector-width-type"
			label="<?php _e( 'Mega menu width selector', 'jet-menu' ); ?>"
			description="<?php _e( 'Enter css selector whose width will be equal to the width of the container mega menu', 'jet-menu' ); ?>"
			size="fullwidth"
			:wrapper-css="[ 'equalwidth' ]"
			type="text"
			v-model="pageOptions['jet-mega-menu-selector-width-type']['value']"
			:conditions="[
				{
					input: this.pageOptions['jet-mega-menu-width-type']['value'],
					compare: 'equal',
					value: 'selector',
				}
			]"
		>
		</cx-vui-input>

		<cx-vui-select
			name="jet-menu-open-sub-type"
			label="<?php _e( 'Sub menu open trigger', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="pageOptions['jet-menu-open-sub-type']['options']"
			v-model="pageOptions['jet-menu-open-sub-type']['value']">
		</cx-vui-select>

		<?php

			$template = get_template();

			if ( file_exists( jet_menu()->plugin_path( "integration/themes/{$template}" ) ) ) {

				$disable_integration_option = 'jet-menu-disable-integration-' . $template;

				?><cx-vui-switcher
					name="<?php echo $disable_integration_option; ?>"
					label="<?php _e( 'Disable default theme integration file', 'jet-menu' ); ?>"
					:wrapper-css="[ 'equalwidth' ]"
					return-true="true"
					return-false="false"
					v-model="pageOptions['<?php echo $disable_integration_option; ?>']['value']">
				</cx-vui-switcher><?php
			}?>

		<cx-vui-switcher
			name="jet-menu-cache-css"
			label="<?php _e( 'Cache menu CSS', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-menu-cache-css']['value']">
		</cx-vui-switcher>
	</div>
</cx-vui-collapse>

<cx-vui-collapse
	:collapsed="false"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Menu container styles', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
		<cx-vui-select
			name="jet-menu-container-alignment"
			label="<?php _e( 'Menu items alignment', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="pageOptions['jet-menu-container-alignment']['options']"
			v-model="pageOptions['jet-menu-container-alignment']['value']">
		</cx-vui-select>

		<cx-vui-input
			name="jet-menu-min-width"
			label="<?php _e( 'Menu container min width (px)', 'jet-menu' ); ?>"
			description="<?php _e( 'Set 0 to automatic width detection', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			type="number"
			:min="0"
			:max="900"
			:step="1"
			v-model="pageOptions['jet-menu-min-width']['value']">
		</cx-vui-input>

		<cx-vui-dimensions
			name="jet-menu-mega-padding"
			label="<?php _e( 'Menu container padding', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:units="[
				{
					unit: 'px',
					min: 0,
					max: 500,
					step: 1
				},
				{
					unit: '%',
					min: 0,
					max: 100,
					step: 1
				}
			]"
			v-model="pageOptions['jet-menu-mega-padding']['value']"
		>
		</cx-vui-dimensions>

		<?php

		$this->render_background_options( array(
			'name'     => 'jet-menu-container',
			'label'    => esc_html__( 'Menu container', 'jet-menu' ),
			'defaults' => array(
				'color' => '#ffffff',
			),
		) );

		$this->render_border_options( array(
			'name'     => 'jet-menu-container',
			'label'    => esc_html__( 'Menu container', 'jet-menu' ),
		) );

		$this->render_box_shadow_options( array(
			'name'     => 'jet-menu-container',
			'label'    => esc_html__( 'Menu container', 'jet-menu' ),
		) );

		?><cx-vui-dimensions
			name="jet-menu-mega-border-radius"
			label="<?php _e( 'Menu container border radius', 'jet-menu' ); ?>"
			description="<?php
				echo sprintf( esc_html__( 'Read more %1$s', 'jet-menu' ),
					htmlspecialchars( "<a href='https://developer.mozilla.org/en-US/docs/Web/CSS/border-radius' target='_blank'>border radius</a>", ENT_QUOTES )
				);
			?>"
			:wrapper-css="[ 'equalwidth' ]"
			:units="[
				{
					unit: 'px',
					min: 0,
					max: 100,
					step: 1
				},
				{
					unit: '%',
					min: 0,
					max: 100,
					step: 1
				}
			]"
			v-model="pageOptions['jet-menu-mega-border-radius']['value']"
		>
		</cx-vui-dimensions>

		<cx-vui-switcher
			name="jet-menu-inherit-first-radius"
			label="<?php _e( 'First item inherit border radius', 'jet-menu' ); ?>"
			description="<?php _e( 'Inherit border radius for the first menu item from main container', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-menu-inherit-first-radius']['value']">
		</cx-vui-switcher>

		<cx-vui-switcher
			name="jet-menu-inherit-last-radius"
			label="<?php _e( 'Last item inherit border radius', 'jet-menu' ); ?>"
			description="<?php _e( 'Inherit border radius for the last menu item from main container', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-menu-inherit-last-radius']['value']">
		</cx-vui-switcher>
	</div>
</cx-vui-collapse>

<cx-vui-collapse
	:collapsed="false"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Submenu container styles', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control', 'states', 'sub-panel' ]"
		>
			<cx-vui-tabs
				class="horizontal-tabs"
				:in-panel="true"
				layout="horizontal"
			><?php

				$tabs = array(
					'simple' => array(
						'label'  => esc_html__( 'Simple Submenu Panel', 'jet-menu' ),
						'prefix' => '-simple'
					),
					'mega' => array(
						'label'  => esc_html__( 'Mega Submenu Panel', 'jet-menu' ),
						'prefix' => '-mega'
					),
				);

				foreach ( $tabs as $tab => $state ) {

					$label = $state['label'];
					$prefix = $state['prefix'];

					?><cx-vui-tabs-panel
						name="<?php echo 'sub-panel-' . $tab . '-styles'; ?>"
						label="<?php echo $label; ?>"
						key="<?php echo 'sub-panel-' . $tab . '-styles'; ?>"
					><?php

						if ( 'simple' === $tab ) {?>
							<cx-vui-input
								name="jet-menu-sub-panel-width-simple"
								label="<?php _e( 'Panel width(px)', 'jet-menu' ); ?>"
								:wrapper-css="[ 'equalwidth' ]"
								size="fullwidth"
								type="number"
								:min="100"
								:max="400"
								:step="1"
								v-model="pageOptions['jet-menu-sub-panel-width-simple']['value']">
							</cx-vui-input><?php
						}

						$this->render_background_options( array(
							'name'  => 'jet-menu-sub-panel' . $prefix,
							'label' => esc_html__( 'Panel', 'jet-menu' ),
						) );

						$this->render_border_options( array(
							'name'     => 'jet-menu-sub-panel' . $prefix,
							'label'    => esc_html__( 'Panel', 'jet-menu' ),
						) );

						$this->render_box_shadow_options( array(
							'name'     => 'jet-menu-sub-panel' . $prefix,
							'label'    => esc_html__( 'Panel', 'jet-menu' ),
						) );

					?>
					<cx-vui-dimensions
						name="<?php echo 'jet-menu-sub-panel-border-radius' . $prefix; ?>"
						label="<?php _e( 'Panel border radius', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: 0,
								max: 100,
								step: 1
							},
							{
								unit: '%',
								min: 0,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-sub-panel-border-radius' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-sub-panel-padding' . $prefix; ?>"
						label="<?php _e( 'Panel padding', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: 0,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-sub-panel-padding' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-sub-panel-margin' . $prefix; ?>"
						label="<?php _e( 'Panel margin', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: -50,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-sub-panel-margin' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

					</cx-vui-tabs-panel><?php
				}

			?></cx-vui-tabs>

		</cx-vui-component-wrapper>
	</div>
</cx-vui-collapse>

