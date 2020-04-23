<div class="jet-popup-conditions-manager__item">
	<div class="jet-popup-conditions-manager__item-control select-type">
		<cx-vui-select
			:prevent-wrap="true"
			:options-list="includeList"
			v-model="сondition.include"
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="groupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="groupList"
			v-model="сondition.group"
			@on-change="groupChange">
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="subGroupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupList"
			v-model="сondition.subGroup"
			@on-change="subGroupChange">
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-control select-type" v-if="subGroupOptionsVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupOptionsList"
			v-model="сondition.subGroupValue"
		>
		></cx-vui-select>
	</div>
	<div class="jet-popup-conditions-manager__item-delete">
		<span @click="deleteCondition" class="dashicons dashicons-trash"></span>
	</div>
</div>
