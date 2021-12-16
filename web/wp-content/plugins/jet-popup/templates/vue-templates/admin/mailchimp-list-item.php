<div
	class="jet-popup-settings-page__list"
	:class="{ 'proccesing-state': mergeFieldsStatusLoading }"
>
	<div class="jet-popup-settings-page__list-details">
		<div><b><?php esc_html_e( 'Name: ', 'jet-popup' ); ?></b><span>{{ list.name }}</span></div>
		<div><b><?php esc_html_e( 'List ID: ', 'jet-popup' ); ?></b><span>{{ list.id }}</span></div>
		<div><b><?php esc_html_e( 'Date Created: ', 'jet-popup' ); ?></b><span>{{ list.dateCreated }}</span></div>
		<div><b><?php esc_html_e( 'Member Count: ', 'jet-popup' ); ?></b><span>{{ list.memberCount }}</span></div>
		<div>
			<b><?php esc_html_e( 'DoubleOptin: ', 'jet-popup' ); ?></b>
			<span class="dashicons dashicons-yes" v-if="list.doubleOptin == true"></span>
			<span class="dashicons dashicons-no-alt" v-if="list.doubleOptin == false"></span>
		</div>
		<div class="merge-fields" v-if="isMergeFields">
			<b><?php esc_html_e( 'Merge Fields: ', 'jet-popup' ); ?></b>
			<span v-for="(name, key) in list.mergeFields" :key="key">{{ key }} ({{ name }})</span>
		</div>
	</div>

	<cx-vui-button
		button-style="link-accent"
		size="link"
		@click="getMergeFields( list.id, $event )"
	>
		<span slot="label"><?php esc_html_e( 'Sync Fields', 'jet-popup' ); ?></span>
	</cx-vui-button>
</div>
