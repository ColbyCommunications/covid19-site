<div
	class="jet-theme-core-settings-page jet-theme-core-settings-page__general"
>
	<div class="cx-vui-component cx-vui-component--equalwidth">
		<div class="cx-vui-component__meta">
			<label class="cx-vui-component__label" for="cx_pro_relations"><?php _e( 'MagicButton Templates', 'jet-theme-core' ); ?></label>
			<div class="cx-vui-component__desc"><?php _e( 'MagicButton templates synchronization', 'jet-theme-core' ); ?></div>
		</div>
		<div class="cx-vui-component__control">
			<cx-vui-button
				button-style="accent-border"
				size="mini"
				:loading="syncTemplatesProcessing"
				@click="syncTemplatesLibrary"
			>
				<span slot="label"><?php _e( 'Sync Templates Library', 'jet-theme-core' ); ?></span>
			</cx-vui-button>
		</div>
	</div>

	<cx-vui-select
		name="pro_relations"
		label="<?php _e( 'Locations relations', 'jet-theme-core' ); ?>"
		description="<?php _e( 'Define relations before Jet and Pro templates attached to the same locations', 'jet-theme-core' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.pro_relations.options"
		v-model="pageOptions.pro_relations.value">
	</cx-vui-select>

	<cx-vui-switcher
		name="prevent_pro_locations"
		label="<?php _e( 'Prevent Pro locations registration', 'jet-theme-core' ); ?>"
		description="<?php _e( 'Prevent Elementor Pro locations registration from JetThemeCore. Enable this if your headers/footers disappear when JetThemeCore is active', 'jet-theme-core' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions.prevent_pro_locations.value">
	</cx-vui-switcher>
</div>
