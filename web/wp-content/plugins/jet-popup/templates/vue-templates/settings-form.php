<div>
	<h1 class="cs-vui-title"><?php _e( 'JetPopup Settings', 'jet-popup' ); ?></h1>
	<div class="cx-vui-panel">
		<cx-vui-tabs
			value="mailchimp-settings"
			layout="vertical">

			<cx-vui-tabs-panel
				name="mailchimp-settings"
				label="<?php _e( 'Mailchimp Settings', 'jet-tabs' ); ?>"
				key="mailchimp-settings">

				<div class="cx-vui-title"><?php esc_html_e( 'API key', 'jet-popup' ); ?></div>

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

					<span>Input your MailChimp API key <a href="http://kb.mailchimp.com/integrations/api-integrations/about-api-keys">About API Keys</a></span>
				</div>

				<div class="mailchimp-account-data" v-if="isMailchimpAccountData">
					<div class="cx-vui-title"><?php esc_html_e( 'Account Data', 'jet-popup' ); ?></div>
					<div class="jet-popup-settings-page__account">
						<div class="jet-popup-settings-page__account-avatar">
							<img :src="mailchimpAccountData.avatar_url" alt="">
						</div>
						<div class="jet-popup-settings-page__account-info">
							<div><b><?php esc_html_e( 'Account ID: ', 'jet-popup' ); ?></b>{{mailchimpAccountData.account_id}}</div>
							<div><b><?php esc_html_e( 'First Name: ', 'jet-popup' ); ?></b>{{mailchimpAccountData.first_name}}</div>
							<div><b><?php esc_html_e( 'Last Name: ', 'jet-popup' ); ?></b>{{mailchimpAccountData.last_name}}</div>
							<div><b><?php esc_html_e( 'Username: ', 'jet-popup' ); ?></b>{{mailchimpAccountData.username}}</div>
						</div>
					</div>
				</div>

				<div class="mailchimp-lists-data" v-if="isMailchimpListsData">
					<div class="cx-vui-title"><?php esc_html_e( 'MailChimp Lists', 'jet-popup' ); ?></div>
					<div class="jet-popup-settings-page__lists">
						<mailchimp-list-item
							v-for="(list, key) in mailchimpListsData"
							:key="list.id"
							:list="list"
							:apikey="settingsData.apikey"
						></mailchimp-list-item>
					</div>
				</div>

			</cx-vui-tabs-panel>
		</cx-vui-tabs>
	</div>
	<div class="jet-popup-settings-page__action">
		<cx-vui-button
			class="remove-license"
			button-style="accent-border"
			size="mini"
			:loading="saveStatusLoading"
			@click="saveSettings"
		>
			<span slot="label"><?php esc_html_e( 'Save Settings', 'jet-popup' ); ?></span>
		</cx-vui-button>
	</div>
</div>
