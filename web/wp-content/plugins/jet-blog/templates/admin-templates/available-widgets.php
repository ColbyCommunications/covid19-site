<div class="jet-blog-settings-page jet-blog-settings-page__available-widgets">
	<div class="jet-blog-settings-page__available-controls">
		<div
			class="jet-blog-settings-page__available-control"
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
</div>

