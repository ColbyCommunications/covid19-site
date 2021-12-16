<div class="jet-popup-library-page__list">
	<preset-item
		v-for="preset in presets"
		:key="preset.id"
		:presetId="preset.id"
		:title="preset.title"
		:category="preset.category"
		:categoryNames="preset.categoryNames"
		:thumbUrl="preset.thumb"
		:install="preset.install"
		:required="preset.required"
		:excerpt="preset.excerpt"
		:details="preset.details"
		:permalink="preset.permalink"
		>
	</preset-item>
</div>
