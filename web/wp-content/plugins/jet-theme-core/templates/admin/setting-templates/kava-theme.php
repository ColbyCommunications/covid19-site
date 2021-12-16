<div
	class="jet-theme-core-settings-page jet-theme-core-settings-page__kava-theme"
>
	<div class="kava-theme">
		<div class="kava-theme__thumb">
			<img :src="themeData.thumb" alt=""/>
		</div>
		<div class="kava-theme__details">
			<div class="main-theme">
				<div class="theme-details">
					<div class="theme-name">{{ themeData.name }}</div>
					<div class="theme-version" v-if="!themeData.updateAvaliable">{{ themeData.version }}</div>
					<div class="theme-version update-avaliable" v-if="themeData.updateAvaliable">{{ themeData.version }}</div>
				</div>
				<div class="theme-status">
					<b><?php _e( 'Status:', 'jet-theme-core' ); ?></b>
					<span>{{ themeData.statusMessage }}</span>
					<span v-if="themeData.updateAvaliable">| <b>{{ themeData.latestVersion }}</b><?php _e( ' version avaliable', 'jet-theme-core' ); ?></span>
				</div>
				<div class="theme-actions">
					<cx-vui-button
						button-style="link-accent"
						size="link"
						:loading="ajaxMainThemeProcessing"
						@click="mainThemeActionHandle"
					>
						<span slot="label">
							<span v-if="'install' === mainThemeAction"><?php _e( 'Install', 'jet-theme-core' ); ?></span>
							<span v-if="'activate' === mainThemeAction || 'active_child' === mainThemeAction"><?php _e( 'Activate', 'jet-theme-core' ); ?></span>
							<span v-if="'checkUpdate' === mainThemeAction"><?php _e( 'Check Updates', 'jet-theme-core' ); ?></span>
							<span v-if="'update' === mainThemeAction"><?php _e( 'Update Now', 'jet-theme-core' ); ?></span>
						</span>
					</cx-vui-button>
				</div>
			</div>
			<div class="child-theme">
				<div class="theme-details">
					<div class="theme-name"><?php _e( 'Child Theme', 'jet-theme-core' ); ?></div>
				</div>
				<div class="theme-status"><b><?php _e( 'Status:', 'jet-theme-core' ); ?></b>{{ childThemeData.statusMessage }}</div>
				<div class="theme-actions">
					<cx-vui-button
						button-style="link-accent"
						size="link"
						:loading="ajaxChildThemeProcessing"
						@click="childThemeActionHandle"
					>
						<span slot="label">
							<span v-if="'install' === childThemeAction"><?php _e( 'Install', 'jet-theme-core' ); ?></span>
							<span v-if="'activate' === childThemeAction"><?php _e( 'Activate', 'jet-theme-core' ); ?></span>
						</span>
					</cx-vui-button>
				</div>
			</div>
		</div>
	</div>

	<div class="kava-theme-backup"
		:class="{ 'proccesing-state': ajaxBackupProcessing }"
	>
		<div class="cx-vui-subtitle"><?php _e( 'Backup Manager', 'jet-theme-core' ); ?></div>

		<cx-vui-switcher
			name="auto_backup"
			label="<?php _e( 'Enable automatic backup', 'jet-theme-core' ); ?>"
			description="<?php _e( 'New theme backup will be automatically created before each update', 'jet-theme-core' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions.auto_backup.value">
		</cx-vui-switcher>

		<cx-vui-list-table
			:is-empty="0 === backupList.length"
			empty-message="<?php _e( 'No backups found', 'jet-theme-core' ); ?>"
			class="kava-theme-backup__table"
		>
			<cx-vui-list-table-heading
				:slots="[ 'file', 'created', 'actions' ]"
				slot="heading"
			>
				<div slot="file"><?php _e( 'File', 'jet-theme-core' ); ?></div>
				<div slot="created"><?php _e( 'Created', 'jet-theme-core' ); ?></div>
				<div slot="actions"><?php _e( 'Actions', 'jet-theme-core' ); ?></div>
			</cx-vui-list-table-heading>
			<cx-vui-list-table-item
				:slots="[ 'file', 'created', 'actions' ]"
				slot="items"
				v-for="( item, index ) in backupList"
				:key="index"
			>
				<div slot="file">{{ item.name }}</div>
				<div slot="created">{{ item.date }}</div>
				<div slot="actions">
					<cx-vui-button
						button-style="link-accent"
						size="link"
						:url="item.download"
						tag-name="a"
					>
						<span slot="label">
							<span><?php _e( 'Download', 'jet-theme-core' ); ?></span>
						</span>
					</cx-vui-button>

					<cx-vui-button
						button-style="link-accent"
						size="link"
						@click="backupHandler( 'delete', item.name )"
					>
						<span slot="label">
							<span><?php _e( 'Delete', 'jet-theme-core' ); ?></span>
						</span>
					</cx-vui-button>
				</div>
			</cx-vui-list-table-item>
		</cx-vui-list-table>

		<div class="kava-theme-backup__actions">
			<cx-vui-button
				class="cx-vui-button--style-accent"
				button-style="default"
				size="mini"
				@click="backupHandler( 'create' )"
			>
				<span slot="label">
					<span><?php _e( 'Create backup', 'jet-theme-core' ); ?></span>
				</span>
			</cx-vui-button>
		</div>

	</div>

</div>
