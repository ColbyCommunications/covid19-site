<?php
$crate_action = add_query_arg(
	array(
		'action' => 'jet_popup_create_from_library_preset',
	),
	esc_url( admin_url( 'admin.php' ) )
);
?>
<div class="jet-popup-library-page__inner">
	<h1 class="jet-popup-library-page__title"><?php esc_html_e( 'JetPopup Presets Library', 'jet-popup' ); ?></h1>

	<div class="cx-vui-panel">

		<div class="jet-popup-library-page__spinner" v-if="spinnerShow">Loading...</div>

		<div class="cx-vui-alert error-type" v-if="presetsLoadedError">
			<div class="cx-vui-alert__icon">
				<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 20.5C10.3672 20.5 11.4609 19.4062 11.4609 18H6.5C6.5 19.4062 7.59375 20.5 9 20.5ZM17.3984 14.6797C16.6562 13.8594 15.2109 12.6484 15.2109 8.625C15.2109 5.61719 13.1016 3.19531 10.2109 2.57031V1.75C10.2109 1.08594 9.66406 0.5 9 0.5C8.29688 0.5 7.75 1.08594 7.75 1.75V2.57031C4.85938 3.19531 2.75 5.61719 2.75 8.625C2.75 12.6484 1.30469 13.8594 0.5625 14.6797C0.328125 14.9141 0.210938 15.2266 0.25 15.5C0.25 16.1641 0.71875 16.75 1.5 16.75H16.4609C17.2422 16.75 17.7109 16.1641 17.75 15.5C17.75 15.2266 17.6328 14.9141 17.3984 14.6797Z"/>
				</svg>
			</div>
			<div class="cx-vui-alert__message"><?php esc_html_e( 'This license will activate licenses for all plugins included in this set.', 'jet-popup' ); ?></div>
		</div>

		<form
			id="jet-popup-library-page-form"
			class="jet-popup-library-page__form"
			ref="jetPopupLibraryForm"
			method="POST"
			action="<?php echo $crate_action; ?>"
			v-if="presetsLoaded"
		>
			<input type="hidden" name="preset" :value="preset">

			<div v-if="categoriesLoaded" class="jet-popup-library-page__filters">
				<div class="jet-popup-library-page__filters-category">
					<span><b><?php esc_html_e( 'Categories: ', 'jet-popup' ); ?></b></span>
					<ul>
						<li
							v-for="category in categoryData"
						>
							<cx-vui-switcher
								:prevent-wrap="true"
								return-true="true"
								return-false="false"
								v-model="category.state"
								@on-change="filterByCategory"
							>
							</cx-vui-switcher>
							<span>{{ category.label }}</span>
						</li>
					</ul>
				</div>
				<div class="jet-popup-library-page__filters-misc">
					<span><b><?php esc_html_e( 'Filter By: ', 'jet-popup' ); ?></b></span>
					<cx-vui-select
						:prevent-wrap="true"
						:options-list="filterByOptions"
						v-model="filterBy"
						@on-change="filterByHandler"
					>
					</cx-vui-select>
				</div>
			</div>

			<preset-list
				:presets='presetList'
			>
			</preset-list>

			<div class="cx-vui-alert info-type" v-if="presetsLength==0">
				<div class="cx-vui-alert__icon">
					<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 20.5C10.3672 20.5 11.4609 19.4062 11.4609 18H6.5C6.5 19.4062 7.59375 20.5 9 20.5ZM17.3984 14.6797C16.6562 13.8594 15.2109 12.6484 15.2109 8.625C15.2109 5.61719 13.1016 3.19531 10.2109 2.57031V1.75C10.2109 1.08594 9.66406 0.5 9 0.5C8.29688 0.5 7.75 1.08594 7.75 1.75V2.57031C4.85938 3.19531 2.75 5.61719 2.75 8.625C2.75 12.6484 1.30469 13.8594 0.5625 14.6797C0.328125 14.9141 0.210938 15.2266 0.25 15.5C0.25 16.1641 0.71875 16.75 1.5 16.75H16.4609C17.2422 16.75 17.7109 16.1641 17.75 15.5C17.75 15.2266 17.6328 14.9141 17.3984 14.6797Z"/>
					</svg>
				</div>
				<div class="cx-vui-alert__message"><?php esc_html_e( 'No matches found', 'jet-popup' ); ?></div>
			</div>

			<div class="jet-popup-library-page__pagination" v-if="isShowPagination">
				<cx-vui-pagination
					:total="presetsLength"
					:page-size="perPage"
					@on-change="changePage"
				></cx-vui-pagination>
			</div>
		</form>
	</div>
</div>
