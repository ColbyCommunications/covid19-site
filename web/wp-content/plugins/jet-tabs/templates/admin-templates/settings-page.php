<?php
/**
 * Main dashboard template
 */
?><div id="jet-tabs-settings-page">
	<div class="jet-tabs-settings-page">
		<h1 class="cs-vui-title"><?php _e( 'JetTabs Settings', 'jet-tabs' ); ?></h1>
		<div class="cx-vui-panel">
			<cx-vui-tabs
				:in-panel="false"
				value="general-settings"
				layout="vertical">

				<?php do_action( 'jet-tabs/settings-page-template/tabs-start' ); ?>

				<cx-vui-tabs-panel
					name="general-settings"
					label="<?php _e( 'General settings', 'jet-tabs' ); ?>"
					key="general-settings">

					<cx-vui-select
						name="widgets_load_level"
						label="<?php _e( 'Editor Load Level', 'jet-tabs' ); ?>"
						description="<?php _e( 'Choose a certain set of options in the widget’s Style tab by moving the slider, and improve your Elementor editor performance by selecting appropriate style settings fill level (from None to Full level)', 'jet-tabs' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:options-list="pageOptions.widgets_load_level.options"
						v-model="pageOptions.widgets_load_level.value">
					</cx-vui-select>

				</cx-vui-tabs-panel>

				<cx-vui-tabs-panel
					name="available-widgets"
					label="<?php _e( 'Available Widgets', 'jet-tabs' ); ?>"
					key="available-widgets">

					<div class="jet-tabs-settings-page__disable-all-widgets">
						<div class="cx-vui-component__label">
							<span v-if="disableAllWidgets"><?php _e( 'Disable All Widgets', 'jet-tabs' ); ?></span>
							<span v-if="!disableAllWidgets"><?php _e( 'Enable All Widgets', 'jet-tabs' ); ?></span>
						</div>

						<cx-vui-switcher
							name="disable-all-avaliable-widgets"
							:prevent-wrap="true"
							:return-true="true"
							:return-false="false"
							@input="disableAllWidgetsEvent"
							v-model="disableAllWidgets">
						</cx-vui-switcher>
					</div>

					<div class="jet-tabs-settings-page__avaliable-controls">
						<div
							class="jet-tabs-settings-page__avaliable-control"
							v-for="(option, index) in pageOptions.avaliable_widgets.options">
							<cx-vui-switcher
								:key="index"
								:name="`avaliable-widget-${option.value}`"
								:label="option.label"
								:wrapper-css="[ 'equalwidth' ]"
								return-true="true"
								return-false="false"
								v-model="pageOptions.avaliable_widgets.value[option.value]"
							>
							</cx-vui-switcher>
						</div>
					</div>

				</cx-vui-tabs-panel>

				<?php do_action( 'jet-tabs/settings-page-template/tabs-end' ); ?>
			</cx-vui-tabs>
		</div>
	</div>
</div>
