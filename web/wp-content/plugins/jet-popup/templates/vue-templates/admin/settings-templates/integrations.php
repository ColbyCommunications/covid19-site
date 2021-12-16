<div
	class="jet-popup-settings-page jet-popup-settings-page__integration"
>
	<div class="cx-vui-title-header">
		<div class="cx-vui-title"><?php esc_html_e( 'Mailchimp API key', 'jet-popup' ); ?></div>
		<div class="cx-vui-subtitle"><?php esc_html_e( 'Input your MailChimp API key. Learn more about ', 'jet-popup' ); ?> <a href="http://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank"><?php esc_html_e( 'API Keys', 'jet-popup' ); ?></a></div>
	</div>

	<div class="mailchimp-apikey-sync">

		<cx-vui-input
			size="fullwidth"
			:prevent-wrap="true"
			v-model="settingsData.apikey"
			placeholder="<?php esc_html_e( 'API key', 'jet-popup' ); ?>"
		>
		</cx-vui-input>

		<cx-vui-button
			button-style="accent-border"
			size="mini"
			:loading="syncStatusLoading"
			@click="mailchimpSync($event)"
		>
			<span slot="label"><?php esc_html_e( 'Sync', 'jet-popup' ); ?></span>
		</cx-vui-button>

	</div>

	<div
		class="mailchimp-account-data"
		:class="{ 'proccesing-state': syncStatusLoading }"
		v-if="isMailchimpAccountData"
	>
		<div class="cx-vui-title-header">
			<div class="cx-vui-title"><?php esc_html_e( 'Mailchimp Account', 'jet-popup' ); ?></div>
			<div class="cx-vui-subtitle"><?php esc_html_e( 'Your mailchimp profile short data', 'jet-popup' ); ?></div>
		</div>
		<div class="jet-popup-settings-page__account">
			<div class="jet-popup-settings-page__account-avatar">
				<img :src="mailchimpAccountData.avatar_url" alt="">
			</div>
			<div class="jet-popup-settings-page__account-info">
				<div><b><?php esc_html_e( 'Account ID: ', 'jet-popup' ); ?></b><span>{{ mailchimpAccountData.account_id }}</span></div>
				<div><b><?php esc_html_e( 'First Name: ', 'jet-popup' ); ?></b><span>{{ mailchimpAccountData.first_name }}</span></div>
				<div><b><?php esc_html_e( 'Last Name: ', 'jet-popup' ); ?></b><span>{{ mailchimpAccountData.last_name }}</span></div>
				<div><b><?php esc_html_e( 'Username: ', 'jet-popup' ); ?></b><span>{{ mailchimpAccountData.username }}</span></div>
			</div>
		</div>
	</div>

	<div
		class="mailchimp-lists-data"
		:class="{ 'proccesing-state': syncStatusLoading }"
		v-if="isMailchimpListsData"
	>
		<div class="cx-vui-title-header">
			<div class="cx-vui-title"><?php esc_html_e( 'MailChimp Audiences', 'jet-popup' ); ?></div>
			<div class="cx-vui-subtitle"><?php esc_html_e( 'MailChimp Audiences List', 'jet-popup' ); ?></div>
		</div>
		<div class="jet-popup-settings-page__lists">
			<mailchimp-list-item
				v-for="(list, key) in mailchimpListsData"
				:key="list.id"
				:list="list"
				:apikey="settingsData.apikey"
			></mailchimp-list-item>
		</div>
	</div>
</div>
