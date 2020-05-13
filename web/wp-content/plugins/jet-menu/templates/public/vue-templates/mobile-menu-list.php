<div
	class="jet-mobile-menu__list"
>
	<div class="jet-mobile-menu__items">
		<mobilemenuitem
			v-for="item in childrenObject"
			:key="item.id"
			:item-data-object="item"
			:depth="depth"
			:menu-options="menuOptions"
		></mobilemenuitem>
	</div>
</div>
