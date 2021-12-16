<div
	class="jet-popup-conditions-manager__item"
	:class="{ 'progress-state': requestLoading }"
>
	<div class="jet-popup-conditions-manager__item-control select-type">
		<cx-vui-select
			:prevent-wrap="true"
			:options-list="[
				{
					value: 'true',
					label: 'Include'
				},
				{
					value: 'false',
					label: 'Exclude'
				}
			]"
			v-model="сondition.include"
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="groupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="groupOptions"
			v-model="сondition.group"
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="subGroupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupOptions"
			v-model="сondition.subGroup"
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="subGroupValueVisible">
		<cx-vui-input
			v-if="'input' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			v-model="сondition.subGroupValue"
		></cx-vui-input>
		<cx-vui-select
			v-if="'select' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupValueOptions"
			v-model="сondition.subGroupValue"
		></cx-vui-select>
		<cx-vui-f-select
			v-if="'f-select' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupValueOptions"
			:multiple="true"
			v-model="сondition.subGroupValue"
		></cx-vui-f-select>
	</div>
	<div class="jet-popup-conditions-manager__item-delete">
		<span @click="deleteCondition" class="dashicons dashicons-trash"></span>
	</div>
</div>
