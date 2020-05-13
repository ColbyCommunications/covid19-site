<cx-vui-collapse
	class="jet-menu-advanced-collapse"
	:collapsed="true"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Item icon', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
		<?php

			$levels = array(
				'top' => array(
					'label'  => esc_html__( 'Top level icon', 'jet-menu' ),
					'prefix' => 'top'
				),
				'sub' => array(
					'label'  => esc_html__( 'Sub level icon', 'jet-menu' ),
					'prefix' => 'sub'
				)
			);

			foreach ( $levels as $key => $level ) {
				?><cx-vui-collapse
					:collapsed="true"
				>
					<div
						class="cx-vui-subtitle"
						slot="title"><?php
							echo $level['label'];
					?></div>
					<div
						class="cx-vui-panel"
						slot="content"
					>
						<cx-vui-input
							name="<?php echo 'jet-menu-' . $level['prefix'] . '-icon-size'; ?>"
							label="<?php _e( 'Icon size', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							type="number"
							:min="10"
							:max="50"
							:step="1"
							v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-size'; ?>']['value']">
						</cx-vui-input>

						<cx-vui-dimensions
							name="<?php echo 'jet-menu-' . $level['prefix'] . '-icon-margin'; ?>"
							label="<?php _e( 'Icon margin', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-margin'; ?>']['value']"
						>
						</cx-vui-dimensions>

						<cx-vui-select
							name="<?php echo 'jet-menu-' . $level['prefix'] . '-icon-ver-position'; ?>"
							label="<?php echo _e( 'Icon vertical position', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-ver-position'; ?>']['options']"
							v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-ver-position'; ?>']['value']"
						>
						</cx-vui-select>

						<cx-vui-select
							name="<?php echo 'jet-menu-' . $level['prefix'] . '-icon-hor-position'; ?>"
							label="<?php echo _e( 'Icon horizontal position', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-hor-position'; ?>']['options']"
							v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-hor-position'; ?>']['value']"
						>
						</cx-vui-select>

						<cx-vui-input
							name="<?php echo 'jet-menu-' . $level['prefix'] . '-icon-order'; ?>"
							label="<?php _e( 'Icon order', 'jet-menu' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							type="number"
							:min="-10"
							:max="10"
							:step="1"
							v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-icon-order'; ?>']['value']">
						</cx-vui-input>

					</div>
				</cx-vui-collapse><?php
			}

		?>
	</div>
</cx-vui-collapse>

<cx-vui-collapse
	class="jet-menu-advanced-collapse"
	:collapsed="true"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Item badge', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
	<?php

		$levels = array(
			'top' => array(
				'label'  => esc_html__( 'Top level badge', 'jet-menu' ),
				'prefix' => 'top'
			),
			'sub' => array(
				'label'  => esc_html__( 'Sub level badge', 'jet-menu' ),
				'prefix' => 'sub'
			)
		);

		foreach ( $levels as $key => $level ) {
			?><cx-vui-collapse
				:collapsed="true"
			>
				<div
					class="cx-vui-subtitle"
					slot="title"><?php
						echo $level['label'];
				?></div>
				<div
					class="cx-vui-panel"
					slot="content"
				>

					<cx-vui-colorpicker
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-text-color'; ?>"
						label="<?php _e( 'Badge Text color', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-text-color'; ?>']['value']"
					></cx-vui-colorpicker><?php

					$this->render_typography_options( array(
						'name'     => 'jet-menu-' . $level['prefix'] . '-badge',
						'label'    => esc_html__( 'Badge', 'jet-menu' ),
					) );

					$this->render_background_options( array(
						'name'  => 'jet-menu-' . $level['prefix'] . '-badge-bg',
						'label' => esc_html__( 'Badge', 'jet-menu' ),
					) );

					$this->render_border_options( array(
						'name'     => 'jet-menu-' . $level['prefix'] . '-badge',
						'label'    => esc_html__( 'Badge', 'jet-menu' ),
					) );

					$this->render_box_shadow_options( array(
						'name'     => 'jet-menu-' . $level['prefix'] . '-badge',
						'label'    => esc_html__( 'Badge', 'jet-menu' ),
					) );

					?><cx-vui-dimensions
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-border-radius'; ?>"
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
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-border-radius'; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-padding'; ?>"
						label="<?php _e( 'Badge padding', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: 0,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-padding'; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-margin'; ?>"
						label="<?php _e( 'Badge margin', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: -50,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-margin'; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-select
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-ver-position'; ?>"
						label="<?php echo _e( 'Badge vertical position (may be overridden with order)', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-ver-position'; ?>']['options']"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-ver-position'; ?>']['value']"
					>
					</cx-vui-select>

					<cx-vui-select
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-hor-position'; ?>"
						label="<?php echo _e( 'Badge horizontal position', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-hor-position'; ?>']['options']"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-hor-position'; ?>']['value']"
					>
					</cx-vui-select>

					<cx-vui-input
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-order'; ?>"
						label="<?php _e( 'Badge order', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						type="number"
						:min="-10"
						:max="10"
						:step="1"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-order'; ?>']['value']">
					</cx-vui-input>

					<cx-vui-switcher
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-badge-hide'; ?>"
						label="<?php _e( 'Hide badge on mobile', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						return-true="true"
						return-false="false"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-badge-hide'; ?>']['value']"
					>
					</cx-vui-switcher>

				</div>
			</cx-vui-collapse><?php
		}
	?>
	</div>
</cx-vui-collapse>

<cx-vui-collapse
	class="jet-menu-advanced-collapse"
	:collapsed="true"
>
	<div
		class="cx-vui-subtitle"
		slot="title"><?php _e( 'Drop-down arrow', 'jet-menu' ) ?></div>
	<div
		class="cx-vui-panel"
		slot="content"
	>
	<?php

		$levels = array(
			'top' => array(
				'label'  => esc_html__( 'Top level arrow', 'jet-menu' ),
				'prefix' => 'top'
			),
			'sub' => array(
				'label'  => esc_html__( 'Sub level arrow', 'jet-menu' ),
				'prefix' => 'sub'
			)
		);

		foreach ( $levels as $key => $level ) {
			?><cx-vui-collapse
				:collapsed="true"
			>
				<div
					class="cx-vui-subtitle"
					slot="title"><?php
						echo $level['label'];
				?></div>
				<div
					class="cx-vui-panel"
					slot="content"
				>
					<cx-vui-iconpicker
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow'; ?>"
						label="<?php _e( 'Arrow icon', 'jet-menu' ); ?>"
						icon-base="fa"
						:icons="pageConfig.arrowsIcons"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow'; ?>']['value']"
					></cx-vui-iconpicker>

					<cx-vui-input
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-size'; ?>"
						label="<?php _e( 'Arrow size', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						type="number"
						:min="10"
						:max="150"
						:step="1"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-size'; ?>']['value']">
					</cx-vui-input>

					<cx-vui-dimensions
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-margin'; ?>"
						label="<?php _e( 'Arrow margin', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:units="[
							{
								unit: 'px',
								min: -50,
								max: 100,
								step: 1
							}
						]"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-margin'; ?>']['value']"
					>
					</cx-vui-dimensions>

					<cx-vui-select
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-ver-position'; ?>"
						label="<?php echo _e( 'Arrow vertical position', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-ver-position'; ?>']['options']"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-ver-position'; ?>']['value']"
					>
					</cx-vui-select>

					<cx-vui-select
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-hor-position'; ?>"
						label="<?php echo _e( 'Arrow horizontal position', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:options-list="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-hor-position'; ?>']['options']"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-hor-position'; ?>']['value']"
					>
					</cx-vui-select>

					<cx-vui-input
						name="<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-order'; ?>"
						label="<?php _e( 'Arrow order', 'jet-menu' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						type="number"
						:min="-10"
						:max="10"
						:step="1"
						v-model="pageOptions['<?php echo 'jet-menu-' . $level['prefix'] . '-arrow-order'; ?>']['value']">
					</cx-vui-input>

				</div>
			</cx-vui-collapse><?php
		}
	?>
	</div>
</cx-vui-collapse>

