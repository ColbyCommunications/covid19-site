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
