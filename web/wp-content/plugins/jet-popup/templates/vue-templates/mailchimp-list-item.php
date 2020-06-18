<div class="jet-popup-settings-page__list">
	<p><b><?php esc_html_e( 'Name: ', 'jet-popup' ); ?></b>{{ list.name }}</p>
	<p><b><?php esc_html_e( 'List ID: ', 'jet-popup' ); ?></b>{{ list.id }}</p>
	<p><b><?php esc_html_e( 'Date Created: ', 'jet-popup' ); ?></b>{{ list.dateCreated }}</p>
	<p><b><?php esc_html_e( 'Member Count: ', 'jet-popup' ); ?></b>{{ list.memberCount }}</p>
	<p>
		<b><?php esc_html_e( 'DoubleOptin: ', 'jet-popup' ); ?></b>
		<span class="dashicons dashicons-yes" v-if="list.doubleOptin == true"></span>
		<span class="dashicons dashicons-no-alt" v-if="list.doubleOptin == false"></span>
	</p>
	<p class="merge-fields" v-if="isMergeFields">
		<b><?php esc_html_e( 'Merge Fields: ', 'jet-popup' ); ?></b>
		<span v-for="(name, key) in list.mergeFields" :key="key">{{ key }} ({{ name }})</span>
	</p>
	<cx-vui-button
		class="remove-license"
		button-style="accent-border"
		size="mini"
		@click="getMergeFields( list.id, $event )"
	>
		<span slot="label"><?php esc_html_e( 'Get Merge Fields', 'jet-popup' ); ?></span>
	</cx-vui-button>
</div>
