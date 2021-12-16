<div class="jet-popup-conditions-manager wp-admin">
	<div class="jet-popup-conditions-manager__container">
		<div class="jet-popup-conditions-manager__blank">
			<div class="jet-popup-conditions-manager__blank-title"><?php echo __( 'Set the Pages where to Display the Popup', 'jet-popup' ); ?></div>
			<div class="jet-popup-conditions-manager__blank-message">
				<span><?php echo __( 'Here you can define the specific pages where you want to show the popup, as well as specify the pages where the popup shouldn’t be displayed, using multiple conditions', 'jet-popup' ); ?></span>
			</div>
		</div>
		<div class="jet-popup-conditions-manager__list">
			<div class="jet-popup-conditions-manager__add-condition">
				<cx-vui-button
					button-style="default"
					class="cx-vui-button--style-accent-border"
					size="mini"
					@click="addCondition"
				>
					<span slot="label" v-if="emptyConditions"><?php echo __( 'Add Condition', 'jet-popup' ); ?></span>
					<span slot="label" v-else><?php echo __( 'Add Additional Condition', 'jet-popup' ); ?></span>
				</cx-vui-button>
			</div>
			<div class="jet-popup-conditions-manager__list-inner" v-if="!emptyConditions">
				<transition-group name="conditions-list-anim" tag="div">
					<conditions-item
						v-for="сondition in popupConditions"
						:key="сondition.id"
						:id="сondition.id"
						:rawCondition="сondition"
					></conditions-item>
				</transition-group>
			</div>
		</div>
	</div>
	<div class="jet-popup-conditions-manager__controls">
		<cx-vui-button
			button-style="default"
			class="cx-vui-button--style-accent-border"
			:loading="saveStatusLoading"
			size="mini"
			@click="saveCondition"
		>
			<span slot="label"><?php echo __( 'Save Conditions', 'jet-popup' ); ?></span>
		</cx-vui-button>
	</div>
</div>
