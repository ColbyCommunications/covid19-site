<div class="jet-blog-settings-page jet-blog-settings-page__general">
	<cx-vui-select
		name="widgets_load_level"
		label="<?php _e( 'Editor Load Level', 'jet-blog' ); ?>"
		description="<?php _e( 'Choose a certain set of options in the widget’s Style tab by moving the slider, and improve your Elementor editor performance by selecting appropriate style settings fill level (from None to Full level)', 'jet-blog' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.widgets_load_level.options"
		v-model="pageOptions.widgets_load_level.value">
	</cx-vui-select>

	<cx-vui-input
		name="youtube_api_key"
		label="<?php _e( 'YouTube API Key', 'jet-blog' ); ?>"
		description="<?php echo sprintf( esc_html__( 'Create own API key %s', 'jet-blog' ), htmlspecialchars( '<a href="https://console.developers.google.com/apis/dashboard" target="_blank">here</a>', ENT_QUOTES ) );?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.youtube_api_key.value"
	></cx-vui-input>

	<cx-vui-f-select
		name="allow_filter_for"
		label="<?php _e( 'Smart Posts List: allow filters for post types', 'jet-blog' ); ?>"
		description="<?php _e( 'Select post types supports Filter by Terms feature', 'jet-blog' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		placeholder="<?php _e( 'Select types...', 'jet-blog' ); ?>"
		:multiple="true"
		:options-list="pageOptions.allow_filter_for.options"
		v-model="pageOptions.allow_filter_for.value"
	></cx-vui-f-select>
</div>
