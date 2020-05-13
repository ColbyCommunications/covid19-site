<cx-vui-collapse
	:collapsed="false"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Top items styles', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>

	<cx-vui-input
		name="jet-menu-item-max-width"
		label="<?php _e( 'Top level item max width (%)', 'jet-menu' ); ?>"
		description="<?php _e( 'Set 0 to automatic width detection', 'jet-menu' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		type="number"
		:min="0"
		:max="100"
		:step="1"
		v-model="pageOptions['jet-menu-item-max-width']['value']">
	</cx-vui-input>

	<?php
		$this->render_typography_options( array(
			'name'     => 'jet-top-menu',
			'label'    => esc_html__( 'Top level menu', 'jet-menu' ),
		) );
	?>

	<cx-vui-switcher
		name="jet-show-top-menu-desc"
		label="<?php _e( 'Show Item Description', 'jet-menu' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions['jet-show-top-menu-desc']['value']"
	>
	</cx-vui-switcher>

	<cx-vui-component-wrapper
		:wrapper-css="[ 'fullwidth-control', 'group' ]"
		:conditions="[
			{
				input: this.pageOptions['jet-show-top-menu-desc']['value'],
				compare: 'equal',
				value: 'true',
			}
		]"
	>
		<?php
			$this->render_typography_options( array(
				'name'     => 'jet-top-menu-desc',
				'label'    => esc_html__( 'Top level menu description', 'jet-menu' ),
			) );
		?>
	</cx-vui-component-wrapper>

	<cx-vui-component-wrapper
		:wrapper-css="[ 'fullwidth-control', 'states' ]"
	>
		<label class="cx-vui-component__label"><?php _e( 'Top Items States', 'jet-menu' ); ?></label>

		<cx-vui-tabs
			class="horizontal-tabs"
			:in-panel="true"
			layout="horizontal"
		>

		<?php
			$tabs = array(
				'default' => array(
					'label'  => esc_html__( 'Default', 'jet-menu' ),
					'prefix' => ''
				),
				'hover' => array(
					'label'  => esc_html__( 'Hover', 'jet-menu' ),
					'prefix' => '-hover'
				),
				'active' => array(
					'label'  => esc_html__( 'Active', 'jet-menu' ),
					'prefix' => '-active'
				),
			);

			foreach ( $tabs as $tab => $state ) {

				$label = $state['label'];
				$prefix = $state['prefix'];

				?><cx-vui-tabs-panel
					name="<?php echo 'menu-items-' . $tab . '-styles'; ?>"
					label="<?php echo $label; ?>"
					key="<?php echo 'menu-items-' . $tab . '-styles'; ?>"
				>
					<cx-vui-colorpicker
						name="<?php echo 'jet-menu-item-text-color' . $prefix; ?>"
						label="<?php _e( 'Item Text Color', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						v-model="pageOptions['<?php echo 'jet-menu-item-text-color' . $prefix; ?>']['value']"
					></cx-vui-colorpicker>

					<cx-vui-colorpicker
						name="<?php echo 'jet-menu-item-desc-color' . $prefix; ?>"
						label="<?php _e( 'Item Description Color', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						v-model="pageOptions['<?php echo 'jet-menu-item-desc-color' . $prefix; ?>']['value']"
					></cx-vui-colorpicker>

					<cx-vui-colorpicker
						name="<?php echo 'jet-menu-top-icon-color' . $prefix; ?>"
						label="<?php _e( 'Item Icon Color', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						v-model="pageOptions['<?php echo 'jet-menu-top-icon-color' . $prefix; ?>']['value']"
					></cx-vui-colorpicker>

					<cx-vui-colorpicker
						name="<?php echo 'jet-menu-top-arrow-color' . $prefix; ?>"
						label="<?php _e( 'Item Arrow Color', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						v-model="pageOptions['<?php echo 'jet-menu-top-arrow-color' . $prefix; ?>']['value']"
					></cx-vui-colorpicker>

					<?php

						$this->render_background_options( array(
							'name'  => 'jet-menu-item' . $prefix,
							'label' => esc_html__( 'Item', 'jet-menu' ),
						) );

						$this->render_border_options( array(
							'name'     => 'jet-menu-item' . $prefix,
							'label'    => esc_html__( 'Item', 'jet-menu' ),
						) );

						$this->render_border_options( array(
							'name'     => 'jet-menu-first-item' . $prefix,
							'label'    => esc_html__( 'First Item', 'jet-menu' ),
						) );

						$this->render_border_options( array(
							'name'     => 'jet-menu-last-item' . $prefix,
							'label'    => esc_html__( 'Last Item', 'jet-menu' ),
						) );

						$this->render_box_shadow_options( array(
							'name'     => 'jet-menu-item' . $prefix,
							'label'    => esc_html__( 'Item', 'jet-menu' ),
						) );
					?>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-item-border-radius' . $prefix; ?>"
						label="<?php _e( 'Item border radius', 'jet-menu' ); ?>"
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
						v-model="pageOptions['<?php echo 'jet-menu-item-border-radius' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-item-padding' . $prefix; ?>"
						label="<?php _e( 'Item Padding', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: 0,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-item-padding' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-item-margin' . $prefix; ?>"
						label="<?php _e( 'Item Margin', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: 0,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-item-margin' . $prefix; ?>']['value']"
					>
					</cx-vui-dimensions>

				</cx-vui-tabs-panel><?php
			}
		?>

		</cx-vui-tabs>
	</cx-vui-component-wrapper>

	</div>
</cx-vui-collapse>


<cx-vui-collapse
	:collapsed="false"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Sub items styles', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
		<?php
			$this->render_typography_options( array(
				'name'     => 'jet-sub-menu',
				'label'    => esc_html__( 'Sub level menu', 'jet-menu' ),
			) );
		?>
		<cx-vui-switcher
			name="jet-show-sub-menu-desc"
			label="<?php _e( 'Show submenu item description', 'jet-menu' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions['jet-show-sub-menu-desc']['value']"
		>
		</cx-vui-switcher>

		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control', 'group' ]"
			:conditions="[
				{
					input: this.pageOptions['jet-show-sub-menu-desc']['value'],
					compare: 'equal',
					value: 'true',
				}
			]"
		>
			<?php
				$this->render_typography_options( array(
					'name'     => 'jet-sub-menu-desc',
					'label'    => esc_html__( 'Sub level menu description', 'jet-menu' ),
				) );
			?>
		</cx-vui-component-wrapper>

		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control', 'states' ]"
		>
			<label class="cx-vui-component__label"><?php _e( 'Sub items states', 'jet-menu' ); ?></label>

			<cx-vui-tabs
				class="horizontal-tabs"
				:in-panel="true"
				layout="horizontal"
			>

			<?php
				$tabs = array(
					'default' => array(
						'label'  => esc_html__( 'Default', 'jet-menu' ),
						'prefix' => ''
					),
					'hover' => array(
						'label'  => esc_html__( 'Hover', 'jet-menu' ),
						'prefix' => '-hover'
					),
					'active' => array(
						'label'  => esc_html__( 'Active', 'jet-menu' ),
						'prefix' => '-active'
					),
				);

				foreach ( $tabs as $tab => $state ) {

					$label = $state['label'];
					$prefix = $state['prefix'];

					?><cx-vui-tabs-panel
						name="<?php echo 'sub-menu-items-' . $tab . '-styles'; ?>"
						label="<?php echo $label; ?>"
						key="<?php echo 'sub-menu-items-' . $tab . '-styles'; ?>"
					>
						<cx-vui-colorpicker
							name="<?php echo 'jet-menu-sub-text-color' . $prefix; ?>"
							label="<?php _e( 'Sub item text color', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-text-color' . $prefix; ?>']['value']"
						></cx-vui-colorpicker>

						<cx-vui-colorpicker
							name="<?php echo 'jet-menu-sub-desc-color' . $prefix; ?>"
							label="<?php _e( 'Sub item description color', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-desc-color' . $prefix; ?>']['value']"
						></cx-vui-colorpicker>

						<cx-vui-colorpicker
							name="<?php echo 'jet-menu-sub-icon-color' . $prefix; ?>"
							label="<?php _e( 'Sub item icon color', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-icon-color' . $prefix; ?>']['value']"
						></cx-vui-colorpicker>

						<cx-vui-colorpicker
							name="<?php echo 'jet-menu-sub-arrow-color' . $prefix; ?>"
							label="<?php _e( 'Sub item arrow color', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-arrow-color' . $prefix; ?>']['value']"
						></cx-vui-colorpicker>

						<?php

							$this->render_background_options( array(
								'name'  => 'jet-menu-sub' . $prefix,
								'label' => esc_html__( 'Sub item', 'jet-menu' ),
							) );

							$this->render_border_options( array(
								'name'     => 'jet-menu-sub' . $prefix,
								'label'    => esc_html__( 'Sub item', 'jet-menu' ),
							) );

							$this->render_border_options( array(
								'name'     => 'jet-menu-sub-first' . $prefix,
								'label'    => esc_html__( 'First sub item', 'jet-menu' ),
							) );

							$this->render_border_options( array(
								'name'     => 'jet-menu-sub-last' . $prefix,
								'label'    => esc_html__( 'Last sub item', 'jet-menu' ),
							) );

							$this->render_box_shadow_options( array(
								'name'     => 'jet-menu-sub' . $prefix,
								'label'    => esc_html__( 'Sub item', 'jet-menu' ),
							) );
						?>

						<cx-vui-dimensions
							name="<?php echo 'jet-menu-sub-border-radius' . $prefix; ?>"
							label="<?php _e( 'Sub item border radius', 'jet-menu' ); ?>"
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
							v-model="pageOptions['<?php echo 'jet-menu-sub-border-radius' . $prefix; ?>']['value']"
						>
						</cx-vui-dimensions>

						<cx-vui-dimensions
							name="<?php echo 'jet-menu-sub-padding' . $prefix; ?>"
							label="<?php _e( 'Sub item padding', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:units="[
								{
									unit: 'px',
									min: 0,
									max: 100,
									step: 1
								}
							]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-padding' . $prefix; ?>']['value']"
						>
						</cx-vui-dimensions>

						<cx-vui-dimensions
							name="<?php echo 'jet-menu-sub-margin' . $prefix; ?>"
							label="<?php _e( 'Sub item margin', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:units="[
								{
									unit: 'px',
									min: 0,
									max: 100,
									step: 1
								}
							]"
							v-model="pageOptions['<?php echo 'jet-menu-sub-margin' . $prefix; ?>']['value']"
						>
						</cx-vui-dimensions>

					</cx-vui-tabs-panel><?php
				}
			?>

			</cx-vui-tabs>
		</cx-vui-component-wrapper>

	</div>
</cx-vui-collapse>
