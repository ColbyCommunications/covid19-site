<div class="jet-popup-library-page__item">
	<div class="jet-popup-library-page__item-inner">
		<div class="jet-popup-library-page__item-content">
			<span class="jet-popup-library-page__item-label">{{ title }}</span>

			<cx-vui-button
				button-style="default-border"
				size="mini"
			>
				<a slot="label" :href="permalink" target="_blank"><?php esc_html_e( 'Preview', 'jet-popup' ); ?></a>
			</cx-vui-button>

			<cx-vui-button
				button-style="accent-border"
				size="mini"
				@click="openModal"
			>
				<span slot="label"><?php esc_html_e( 'Install', 'jet-popup' ); ?></span>
			</cx-vui-button>

			<cx-vui-popup
				v-model="modalShow"
				body-width="520px"
				@on-ok="createPopup"
			>
				<div slot="title"><?php esc_html_e( 'You really want to create a new Popup?', 'jet-popup' ); ?></div>
				<div slot="content">
					<p><?php esc_html_e( 'A new preset will be created. You\'ll be redirected to Editing page. Also the template will be added to the popups list on "All Popups" page.', 'jet-popup' ); ?></p>
				</div>
			</cx-vui-popup>
		</div>
		<div class="jet-popup-library-page__item-thumb">
			<img :src="thumbUrl" alt="">
		</div>
		<div class="jet-popup-library-page__item-info">
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-category">
				<div class="category-info">
					<b><?php esc_html_e( 'Category:', 'jet-popup' ); ?></b>
					<span>{{categoryName}}</span>
				</div>
			</div>
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-install" v-if="install > 0">
				<div class="install-info">
					<b><?php esc_html_e( 'Installations: ', 'jet-popup' ); ?></b>
					<span style="{ display: block }">{{install}}</span>
				</div>
			</div>
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-required" v-if="requiredPlugins.length > 0">
				<b><?php esc_html_e( 'Required Plugins: ', 'jet-popup' ); ?></b>
				<div class="jet-popup-library-page__required-list">
					<div v-for="plugin in requiredPlugins" class="jet-popup-library-page__required-plugin">
						<a :href="plugin.link" target="_blank">
							<img :src="plugin.badge" alt="">
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
