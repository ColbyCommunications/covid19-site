( function( $ ) {
	'use strict';

	var JetMenuPlugin = function( element, options ) {

		this.defaultSettings = {
			enabled: false,
			mouseLeaveDelay: 500,
			openSubType: 'click', // hover, click
			ajaxLoad: true,
			megaWidthType: 'container',
			megaWidthSelector: '',
			mainMenuSelector: '.jet-menu',
			menuItemSelector: '.jet-menu-item',
			moreMenuContent:  '&middot;&middot;&middot;'
		}

		this.settings = $.extend( this.defaultSettings, options );

		this.$window = $( window );

		this.$document = $( document );

		this.$element = $( element );

		this.$instance = $( this.settings.mainMenuSelector, this.$element ).addClass( 'jet-responsive-menu' );

		this.$menuItems = $( '>' + this.settings.menuItemSelector, this.$instance ).addClass( 'jet-responsive-menu-item' );

		this.$moreItemsInstance = null;

		this.hiddenItemsArray = [];

		this.createMenuInstance();

		this.$instance.trigger( 'jetMenuCreated' );

		return this;
	}

	JetMenuPlugin.prototype = {
		constructor: JetMenuPlugin,

		self: this,

		createMenuInstance: function() {
			var self = this,
				mainMenuWidth,
				totalVisibleItemsWidth = 0;

			this.subMenuRebuild();
			this.subMegaMenuRebuild();

			// Add available items list
			if ( ! tools.isEmpty( this.settings.moreMenuContent ) && self.settings.enabled ) {
				self.$instance.append( '<li class="jet-menu-item jet-menu-item-has-children jet-simple-menu-item jet-responsive-menu-available-items" hidden><a href="#" class="top-level-link"><div class="jet-menu-item-wrapper">' + this.settings.moreMenuContent + '</div></a><ul class="jet-sub-menu"></ul></li>' );
				self.$moreItemsInstance = $( '> .jet-responsive-menu-available-items', this.$instance );
				self.$moreItemsInstance.attr( { 'hidden': 'hidden' } );
			}

			this.rebuildItems();
			this.$instance.trigger( 'rebuildItems' ); // subMenu position rebuild

			this.subMenuHandler();

			this.watch();
		},

		/**
		 * SubMenu items Handler.
		 *
		 * @return {void}
		 */
		subMenuHandler: function() {
			var self = this,
				transitionend = 'transitionend oTransitionEnd webkitTransitionEnd',
				prevClickedItem = null,
				menuItem,
				menuItemParents,
				timer;

			switch ( this.settings.openSubType ) {
				case 'hover':
					this.$instance.on( 'mouseenter', '.jet-menu-item > a', { instance: this }, mouseEnterHandler );
					this.$instance.on( 'mouseleave', '.jet-menu-item > a', mouseLeaveHandler );
				break;

				case 'click':
					this.$instance.on( 'click', '.jet-menu-item > a', { instance: this }, clickHandler );
				break;
			}

			this.$instance.on( 'mouseenter', '.jet-sub-menu, .jet-sub-mega-menu', mouseEnterSubMenuHandler );
			this.$instance.on( 'mouseenter', mouseEnterInstanceHandler );
			this.$instance.on( 'mouseleave', mouseLeaveInstanceHandler );

			function clickHandler( event ) {
				var $this,
					$siblingsItems,
					$link,
					$currentTarget,
					subMenu,
					templateId,
					instance = event.data.instance;

				event.preventDefault();
				event.stopPropagation();

				$currentTarget = $( event.currentTarget );
				$this          = $currentTarget.closest('.jet-menu-item');
				$siblingsItems = $this.siblings( '.jet-menu-item.jet-menu-item-has-children' );
				$link          = $( '> a', $this );
				subMenu        = $( '.jet-sub-menu:first, .jet-sub-mega-menu', $this );
				templateId     = subMenu.data( 'template-id' ) || false;

				if ( $siblingsItems[0] ) {
					$siblingsItems.removeClass( 'jet-menu-hover' );
					$( 'jet-menu-item-has-children', $siblingsItems ).removeClass( 'jet-menu-hover' );
				}

				if ( ! $( '.jet-sub-menu, .jet-sub-mega-menu', $this )[0] || $this.hasClass('jet-menu-hover') ) {
					window.location = $link.attr( 'href' );

					return false;
				}

				if ( subMenu[0] ) {
					$this.addClass( 'jet-menu-hover' );

					if ( templateId ) {
						instance.maybeTemplateLoad( templateId, subMenu );
					}
				}
			}

			function mouseEnterHandler( event ) {
				var subMenu,
					templateId,
					instance;

				menuItem   = $( event.target ).parents( '.jet-menu-item' );
				subMenu    = menuItem.children( '.jet-sub-menu, .jet-sub-mega-menu' ).first();
				templateId = subMenu.data( 'template-id' ) || false;
				instance   = event.data.instance;

				$( '.jet-menu-hover', this.$instance ).removeClass( 'jet-menu-hover' );

				if ( subMenu[0] ) {
					menuItem.addClass( 'jet-menu-hover' );

					if ( templateId ) {
						instance.maybeTemplateLoad( templateId, subMenu );
					}
				}
			}

			function mouseLeaveHandler( event ) {
				// Item Mouse Leave Event
			}

			function mouseEnterSubMenuHandler( event ) {
				clearTimeout( timer );
			}

			function mouseEnterInstanceHandler( event ) {
				clearTimeout( timer );
			}

			function mouseLeaveInstanceHandler( event ) {
				timer = setTimeout( function() {
					$( '.jet-menu-hover', this.$instance ).removeClass( 'jet-menu-hover' );
				}, self.settings.mouseLeaveDelay );
			}

			var windowWidth = $( window ).width();

			self.$window.on( 'orientationchange resize', function( event ) {

				// Do not trigger a change if the viewport hasn't actually changed.  Scrolling on iOS will trigger a resize.
				if ( $( window ).width() === windowWidth ) {
					return;
				}

				windowWidth = $( window ).width();

				self.$instance.find( '.jet-menu-item' ).removeClass( 'jet-menu-hover' );
			} );

			self.$document.on( 'touchend', function( event ) {

				if ( $( event.target ).closest( '.jet-menu-item' ).length ) {
					return;
				}

				self.$instance.find( '.jet-menu-item' ).removeClass( 'jet-menu-hover' );
			} );

		},

		maybeTemplateLoad: function( templateId, $templateContainer ) {

			if ( ! this.settings.ajaxLoad ) {
				return;
			}

			if ( $templateContainer.hasClass( 'template-loaded' ) ) {
				return;
			}

			$.ajax( {
				type: 'GET',
				url: window.jetMenuPublicSettings.templateApiUrl,
				dataType: 'json',
				data: {
					'id': templateId,
					'dev': window.jetMenuPublicSettings.devMode
				},
				beforeSend: function( jqXHR, ajaxSettings ) {

				},
				success: function( responce, textStatus, jqXHR ) {
					var templateContent   = responce['template_content'],
						templateScripts   = responce['template_scripts'],
						templateStyles    = responce['template_styles'];

					for ( var scriptHandler in templateScripts ) {
						jetMenu.addedAssetsPromises.push( jetMenu.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
					}

					for ( var styleHandler in templateStyles ) {
						jetMenu.addedAssetsPromises.push( jetMenu.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
					}

					$templateContainer.addClass( 'template-loaded' );

					jetMenu.elementorContentRender( $templateContainer, templateContent );
				}
			} );
		},

		/**
		 * Responsive menu watcher function.
		 *
		 * @param  {number} Watcher debounce delay.
		 * @return {void}
		 */
		watch: function( delay ) {
			var delay = delay || 10;

			$( window ).on( 'resize.jetResponsiveMenu orientationchange.jetResponsiveMenu load.jetResponsiveMenu', this.debounce( delay, this.watcher.bind( this ) ) );
			this.$instance.trigger( 'containerResize' );
		},

		/**
		 * Responsive menu watcher callback.
		 *
		 * @param  {Object} Resize or Orientationchange event.
		 * @return {void}
		 */
		watcher: function( event ) {
			this.rebuildItems();
			this.$instance.trigger( 'rebuildItems' ); // subMenu position rebuild
			this.$instance.trigger( 'containerResize' );
		},

		/**
		 * Responsive Menu rebuilding function.
		 *
		 * @return {void}
		 */
		rebuildItems: function() {

			if ( ! this.settings.enabled ) {
				return false;
			}

			var self                       = this,
				mainMenuWidth              = this.$instance.width(),
				correctedMenuWidth         = this.$instance.width() - self.$moreItemsInstance.outerWidth( true ),
				iterationVisibleItemsWidth = 0,
				iterationHiddenItemsWidth  = this.getVisibleItemsWidth(),
				visibleItemsArray          = [],
				hiddenItemsArray           = [];

			self.$menuItems.each( function() {
				var $this = $( this );

				iterationVisibleItemsWidth += $this.outerWidth( true );

				if ( iterationVisibleItemsWidth > correctedMenuWidth && ! tools.inArray( this, hiddenItemsArray ) ) {
					hiddenItemsArray.push( this );
				} else {
					visibleItemsArray.push( this );
				}

			} );

			hiddenItemsArray.forEach( function( item ) {
				var $item = $( item );

				$item.attr( { 'hidden': 'hidden' } );
			} );

			visibleItemsArray.forEach( function( item, index ) {
				var $item = $( item );

				$item.removeAttr( 'hidden' );
			} );

			$( '> .jet-sub-menu', self.$moreItemsInstance ).empty();

			hiddenItemsArray.forEach( function( item ) {
				var $clone = $( item ).clone();

				// Remove sub-mega-menu content
				$( '.jet-sub-mega-menu', $clone ).remove();

				$clone.addClass( 'jet-sub-menu-item' );

				$clone.removeAttr( 'hidden' );

				$( '> .top-level-link', $clone ).toggleClass( 'top-level-link sub-level-link' );

				$( '> .jet-sub-menu', self.$moreItemsInstance ).append( $clone );
			} );

			if ( 0 == hiddenItemsArray.length ) {
				self.$moreItemsInstance.attr( { 'hidden': 'hidden' } );
				self.$moreItemsInstance.addClass( 'jet-empty' );
			} else {
				self.$moreItemsInstance.removeAttr( 'hidden' );
				self.$moreItemsInstance.removeClass( 'jet-empty' );
			}

			self.hiddenItemsArray = hiddenItemsArray;
		},

		/**
		 * Sub Menu rebuilding function.
		 *
		 * @return {void}
		 */
		subMenuRebuild: function() {
			var self = this,
				initSubMenuPosition = false;

			this.$instance.on( 'rebuildItems', function() {
				var $subMenuList = $( '.jet-sub-menu', self.$instance ),
					maxWidth     = self.$window.outerWidth( true ),
					isRTL        = $( 'body' ).hasClass( 'rtl' );

				if ( ! $subMenuList[0] ) {
					return;
				}

				if ( initSubMenuPosition ) {
					$subMenuList.removeClass( 'inverse-side' );
					initSubMenuPosition = false;
				}

				$subMenuList.each( function() {
					var $this = $( this ),
						subMenuOffset = $this.offset().left,
						subMenuPos    = subMenuOffset + $this.outerWidth( true );

					if ( ! isRTL ) {
						if ( subMenuPos >= maxWidth ) {
							$this.addClass( 'inverse-side' );
							$this.find( '.jet-sub-menu' ).addClass( 'inverse-side' );

							initSubMenuPosition = true;
						} else if ( subMenuOffset < 0 ) {
							$this.removeClass( 'inverse-side' );
							$this.find( '.jet-sub-menu' ).removeClass( 'inverse-side' );
						}
					} else {
						if ( subMenuOffset < 0 ) {
							$this.addClass( 'inverse-side' );
							$this.find( '.jet-sub-menu' ).addClass( 'inverse-side' );

							initSubMenuPosition = true;
						} else if ( subMenuPos >= maxWidth ) {
							$this.removeClass( 'inverse-side' );
							$this.find( '.jet-sub-menu' ).removeClass( 'inverse-side' );
						}
					}

				} );
			} );
		},

		/**
		 * Sub Mega Menu rebuilding function.
		 *
		 * @return {void}
		 */
		subMegaMenuRebuild: function() {
			var self = this;

			this.$instance.on( 'containerResize', function() {
				var $megaMenuList = $( '.jet-sub-mega-menu', self.$instance ),
					maxWidth      = $( 'body' ).outerWidth( true );

				switch( self.settings.megaWidthType ) {
					case 'items':
						var visibleItemsWidth = self.getVisibleItemsWidth(),
							firstOffset = $( '> .jet-menu-item:first', self.$instance ).position().left;

						$megaMenuList.css( {
							'width': visibleItemsWidth + 'px',
							'left': firstOffset
						} );
					break;
					case 'selector':
						var customSelector       = $( self.settings.megaWidthSelector ),
							instanceOffset       = null,
							customSelectorOffset = null;

						if ( customSelector[0] ) {
							instanceOffset       = self.$instance.offset().left;
							customSelectorOffset = customSelector.offset().left;

							$megaMenuList.css( {
								'width': customSelector.outerWidth(),
								'left': (customSelectorOffset - instanceOffset ) + 'px'
							} );
						}

					break;
				}

				if ( $megaMenuList[0] ) {
					$megaMenuList.css( {
						'maxWidth': ''
					} );

					$megaMenuList.each( function() {
						var $this = $( this ),
							megaMenuOffsetLeft = $this.offset().left,
							megaMenuOffsetRight = megaMenuOffsetLeft + $this.outerWidth( true );

						if ( megaMenuOffsetRight >= maxWidth ) {
							$this.css( {
								'maxWidth': maxWidth - megaMenuOffsetLeft
							} );
						}
					} );
				}
			} );
		},

		/**
		 * Get visible items total width
		 *
		 * @return {int}
		 */
		getVisibleItemsWidth: function() {
			var totalVisibleItemsWidth = 0;

			this.$menuItems.each( function() {
				var $this = $( this );

				if ( ! $this.hasAttr( 'hidden' ) ) {
					totalVisibleItemsWidth += $this.outerWidth( true );
				}
			} );

			return totalVisibleItemsWidth;
		},

		/**
		 * Mobile and tablet check funcion.
		 *
		 * @return {boolean} Mobile Status
		 */
		mobileAndTabletcheck: function() {
			var check = false;

			(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);

			return check;
		},

		/**
		 * Debounce the function call
		 *
		 * @param  {number}   threshold The delay.
		 * @param  {Function} callback  The function.
		 */
		debounce: function ( threshold, callback ) {
			var timeout;

			return function debounced( $event ) {
				function delayed() {
					callback.call( this, $event );
					timeout = null;
				}

				if ( timeout ) {
					clearTimeout( timeout );
				}

				timeout = setTimeout( delayed, threshold );
			};
		}
	}

	/*
	 * Js tools
	 */
	var tools = {
		isEmpty: function( value ) {
			return ( ( false === value ) || ( '' === value ) || ( null === value ) || ( undefined === value ));
		},

		isEmptyObject: function( value ) {
			return ( true === this.isEmpty( value ) ) || ( 0 === value.length );
		},

		isString: function(value) {
			return ( ( 'string' === typeof value ) || ( value instanceof String ) );
		},

		isArray: function( value ) {
			return $.isArray( value );
		},

		inArray: function( value, array ) {
			return ( $.inArray( value, array ) !== -1);
		}
	};

	/*
	 * Jq tools
	 */
	$.fn.hasAttr = function( name ) {
		return this.attr( name ) !== undefined;
	};

	// jQuery plugin
	$.fn.JetMenuPlugin = function( options ) {
		return this.each( function() {
			var $this         = $( this ),
				pluginOptions = ( 'object' === typeof options ) ? options : {};

			if ( ! $this.data( 'JetMenuPlugin' ) ) {

				// create plugin instance (only if not exists) and expose the entire instance API
				$this.data( 'JetMenuPlugin', new JetMenuPlugin( this, pluginOptions ) );
			}
		} );
	};

}( jQuery ) );

( function( $ ) {
	'use strict';

	window.jetMenu = {

		addedScripts: {},

		addedStyles: {},

		addedAssetsPromises: [],

		initedMobileMenuInstance: [],

		eventBus: new Vue(),

		$body: $( 'body' ),

		init: function() {
			this.initDesktopMenu();
			this.mobileVueComponents();
			this.initMobileMenu();
		},

		initDesktopMenu: function() {
			var rollUp                   = false,
				jetMenuMouseleaveDelay   = 500,
				jetMenuMegaWidthType     = 'container',
				jetMenuMegaWidthSelector = '',
				jetMenuMegaOpenSubType   = 'hover',
				jetMenuMegaAjax          = false;

			if ( window.jetMenuPublicSettings && window.jetMenuPublicSettings.menuSettings ) {
				rollUp                   = ( 'true' === jetMenuPublicSettings.menuSettings.jetMenuRollUp ) ? true : false;
				jetMenuMouseleaveDelay   = jetMenuPublicSettings.menuSettings.jetMenuMouseleaveDelay || 500;
				jetMenuMegaWidthType     = jetMenuPublicSettings.menuSettings.jetMenuMegaWidthType || 'container';
				jetMenuMegaWidthSelector = jetMenuPublicSettings.menuSettings.jetMenuMegaWidthSelector || '';
				jetMenuMegaOpenSubType   = jetMenuPublicSettings.menuSettings.jetMenuMegaOpenSubType || 'hover';
				jetMenuMegaAjax          = ( 'true' === jetMenuPublicSettings.menuSettings.jetMenuMegaAjax )  ? true : false;
			}

			$( '.jet-menu-container' ).JetMenuPlugin( {
				enabled: rollUp,
				mouseLeaveDelay: +jetMenuMouseleaveDelay,
				megaWidthType: jetMenuMegaWidthType,
				megaWidthSelector: jetMenuMegaWidthSelector,
				openSubType: jetMenuMegaOpenSubType,
				ajaxLoad: jetMenuMegaAjax,
			} );

		},

		initMobileMenu: function() {

			let mobileMenuList = $( '.jet-mobile-menu-single' );

			if ( ! mobileMenuList[0] ) {
				return false;
			}

			if ( 'true' === window.jetMenuPublicSettings.devMode ) {
				Vue.config.devtools = true;
			}

			mobileMenuList.each( function() {
				let $this          = $( this ),
					menuInstanceId = $this.attr( 'id' ),
					menuId         = $this.data( 'menu-id' ) || false,
					menuOptions    = $this.data( 'menu-options' ) || {};

				jetMenu.createMobileMenuInstance( menuInstanceId, menuId, menuOptions );
			} );
		},

		createMobileMenuInstance: function( menuInstanceId, menuId, menuOptions ) {

			if ( jetMenu.initedMobileMenuInstance.includes( menuInstanceId ) ) {
				return;
			}

			jetMenu.initedMobileMenuInstance.push( menuInstanceId );

			let mobileMenuInstance = new Vue( {
				el: '#' + menuInstanceId,
				data: {
					'uniqId': menuInstanceId,
					'menuOptions': menuOptions,
				}
			} );
		},

		mobileVueComponents: function() {

			Vue.component( 'mobilemenuitem', {
				template: '#mobile-menu-item-template',

				props: {
					itemDataObject: Object,
					depth: Number,
					menuOptions: Object
				},

				data: function() {
					return ( {
						ajaxRequest: null,
						templateLoadStatus: false,
					} )
				},

				computed: {

					itemClasses: function() {
						let itemClasses = [
							`jet-mobile-menu__item`,
							`jet-menu-item-${ this.itemDataObject.itemId }`,
							`jet-mobile-menu__item--sub-trigger-${ this.menuOptions.subTrigger || 'item' }`
						];

						if ( window.location.href === this.itemDataObject.url ) {
							itemClasses.push( 'jet-mobile-menu__item--active');
						}

						return itemClasses;
					},

					isСhildrenDefine: function() {
						return this.itemDataObject.children ? true : false;
					},

					isTemplateDefine: function() {
						return false !== this.itemDataObject.elementorTemplateId ? true : false;
					},

					isSub: function() {
						return this.isСhildrenDefine || this.isTemplateDefine ? true : false;
					},

					isTopLevel: function() {
						return 0 === this.depth ? true : false;
					},

					depthClass: function() {
						return 0 === this.depth ? 'mobile-top-level-link' : 'mobile-sub-level-link';
					},

					dropdownIconHtml: function() {
						return '' !== this.menuOptions.dropdownIcon ? this.menuOptions.dropdownIcon : '<i class="fas fa-angle-right"></i>';
					},

					itemIconHtml: function() {
						return this.itemDataObject.itemIcon;
					},

					isIconVisible: function() {
						let iconVisible = this.menuOptions['itemIconVisible'] || 'true';

						return 'false' === iconVisible || '' === this.itemDataObject.itemIcon || ! this.itemDataObject.itemIcon ? false : true;
					},

					isBadgeVisible: function() {
						let badgeVisible = this.menuOptions['itemBadgeVisible'] || 'true';

						return 'false' === badgeVisible || '' === this.itemDataObject.badgeText || ! this.itemDataObject.badgeText ? false : true;
					},

					isDescVisible: function() {
						let descVisible = this.menuOptions['itemDescVisible'] || 'false';

						return 'false' === descVisible || '' === this.itemDataObject.description || ! this.itemDataObject.description ? false : true;
					},

					loaderColor: function() {
						return this.menuOptions.loaderColor || '#3a3a3a';
					},
				},

				methods: {
					itemSubHandler: function( event ) {

						if ( 'item' !== this.menuOptions.subTrigger ) {
							return;
						}

						let target     = event.target,
							mobileLink = target.closest( '.mobile-link' );

						if ( this.isSub && mobileLink ) {
							event.preventDefault();
						}

						this.switchToSub( event );
					},

					maskerSubHandler: function( event ) {

						if ( 'submarker' !== this.menuOptions.subTrigger ) {
							return;
						}

						this.switchToSub( event );
					},

					switchToSub: function( event ) {

						if ( ! this.isSub ) {
							return;
						}

						if ( ! this.isTemplateDefine ) {

							jetMenu.eventBus.$emit( 'itemsSubSwitch', {
								menuUniqId: this.menuOptions.menuUniqId,
								id: this.itemDataObject.id,
								name: this.itemDataObject.name,
								children: this.itemDataObject.children || false
							} );

						} else {

							if ( ! this.itemDataObject.elementorContent ) {
								this.getElementorTemplate();
							} else {

								jetMenu.eventBus.$emit( 'showTemplateContent', {
									menuUniqId: this.menuOptions.menuUniqId,
									id: this.itemDataObject.id,
									name: this.itemDataObject.name,
									elementorContent: this.itemDataObject.elementorContent
								} );
							}
						}
					},

					getElementorTemplate: function() {
						var vueInstance = this;

						vueInstance.ajaxRequest = $.ajax( {
							type: 'GET',
							url: window.jetMenuPublicSettings.templateApiUrl,
							dataType: 'json',
							data: {
								'id': vueInstance.itemDataObject.elementorTemplateId,
								'dev': window.jetMenuPublicSettings.devMode
							},
							beforeSend: function( jqXHR, ajaxSettings ) {

								if ( null !== vueInstance.ajaxRequest ) {
									vueInstance.ajaxRequest.abort();
								}

								vueInstance.templateLoadStatus = true;
							},
							success: function( responce, textStatus, jqXHR ) {
								var templateContent   = responce['template_content'],
									templateScripts   = responce['template_scripts'],
									templateStyles    = responce['template_styles'];

								for ( var scriptHandler in templateScripts ) {
									jetMenu.addedAssetsPromises.push( jetMenu.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
								}

								for ( var styleHandler in templateStyles ) {
									jetMenu.addedAssetsPromises.push( jetMenu.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
								}

								vueInstance.templateLoadStatus = false;

								vueInstance.itemDataObject.elementorContent = templateContent;

								jetMenu.eventBus.$emit( 'showTemplateContent', {
									menuUniqId: vueInstance.menuOptions.menuUniqId,
									id: vueInstance.itemDataObject.id,
									name: vueInstance.itemDataObject.name,
									elementorContent: vueInstance.itemDataObject.elementorContent
								} );
							}
						} );
					}
				}

			});

			Vue.component( 'mobilemenulist', {
				template: '#mobile-menu-list-template',

				props: {
					depth: Number,
					childrenObject: Object,
					menuOptions: Object
				}

			});

			Vue.component( 'mobilemenu', {
				template: '#mobile-menu-template',

				props: {
					menuOptions: {
						type: Object,
						default: {}
					},
				},

				data: function() {
					return ( {
						menuOpen: false,
						children: false,
						itemsRawData: {},
						trail:[],
						breadcrumbsData:[],
						animation: 'items-next-animation',
						ajaxRequest: null,
						templateVisible: false,
						instanceLoadStatus: false,
						itemTemplateContent: false,

						headerTemplate: this.menuOptions['headerTemplate'] || 0,
						headerContent: false,
						headerTemplateVisible: false,
						beforeTemplate: this.menuOptions['beforeTemplate'] || 0,
						beforeContent: false,
						afterTemplate: this.menuOptions['afterTemplate'] || 0,
						afterContent: false,

						ajaxPromises: []
					} )
				},

				mounted: function() {
					let vueInstance = this;

					if ( this.menuOpen ) {
						jetMenu.$body.addClass( 'jet-mobile-menu-visible' );
					}

					this.ajaxPromises.push(
						new Promise( function( resolve, reject ) {
							$.ajax( {
								type: 'GET',
								url: window.jetMenuPublicSettings.menuItemsApiUrl,
								dataType: 'json',
								data: {
									'menu_id': vueInstance.menuId,
									'dev': window.jetMenuPublicSettings.devMode
								},
								beforeSend: function( jqXHR, ajaxSettings ) {

								},
								success: function( responce, textStatus, jqXHR ) {
									let responceData = responce.data.items;

									vueInstance.itemsRawData = responceData;

									resolve();
								}
							} );
						} )
					);

					if ( 0 !== +this.headerTemplate ) {
						this.ajaxPromises.push(
							new Promise( function( resolve, reject ) {
								$.ajax( {
									type: 'GET',
									url: window.jetMenuPublicSettings.templateApiUrl,
									dataType: 'json',
									data: {
										'id': vueInstance.headerTemplate,
										'dev': window.jetMenuPublicSettings.devMode
									},
									success: function( responce, textStatus, jqXHR ) {
										let templateContent = responce['template_content'],
											templateScripts = responce['template_scripts'],
											templateStyles  = responce['template_styles'];

										for ( let scriptHandler in templateScripts ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
										}

										for ( let styleHandler in templateStyles ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
										}

										vueInstance.headerContent = templateContent;
										vueInstance.headerTemplateVisible = true;

										resolve();
									}
								} );
							} )
						);
					}

					if ( 0 !== +this.beforeTemplate ) {
						this.ajaxPromises.push(
							new Promise( function( resolve, reject ) {
								$.ajax( {
									type: 'GET',
									url: window.jetMenuPublicSettings.templateApiUrl,
									dataType: 'json',
									data: {
										'id': vueInstance.beforeTemplate,
										'dev': window.jetMenuPublicSettings.devMode
									},
									success: function( responce, textStatus, jqXHR ) {
										let templateContent = responce['template_content'],
											templateScripts = responce['template_scripts'],
											templateStyles  = responce['template_styles'];

										for ( let scriptHandler in templateScripts ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
										}

										for ( let styleHandler in templateStyles ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
										}

										vueInstance.beforeContent = templateContent;

										resolve();
									}
								} );
							} )
						);
					}

					if ( 0 !== +this.afterTemplate ) {
						this.ajaxPromises.push(
							new Promise( function( resolve, reject ) {
								$.ajax( {
									type: 'GET',
									url: window.jetMenuPublicSettings.templateApiUrl,
									dataType: 'json',
									data: {
										'id': vueInstance.afterTemplate,
										'dev': window.jetMenuPublicSettings.devMode
									},
									success: function( responce, textStatus, jqXHR ) {
										let templateContent = responce['template_content'],
											templateScripts = responce['template_scripts'],
											templateStyles  = responce['template_styles'];

										for ( let scriptHandler in templateScripts ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
										}

										for ( let styleHandler in templateStyles ) {
											jetMenu.addedAssetsPromises.push( jetMenu.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
										}

										vueInstance.afterContent = templateContent;
										resolve();
									}
								} );
							} )
						);
					}

					this.instanceLoadStatus = true;

					Promise.all( this.ajaxPromises ).then( function() {
						vueInstance.instanceLoadStatus = false;
					}, function( reason ) {
						console.log( 'Script Loaded Error' );
					} );

					jetMenu.eventBus.$on( 'itemsSubSwitch', function ( payLoad ) {

						if ( vueInstance.menuOptions.menuUniqId !== payLoad.menuUniqId ) {
							return;
						}

						vueInstance.trail.push( payLoad.children );
						vueInstance.children = payLoad.children;
						vueInstance.breadcrumbsData.push( payLoad.name );
						vueInstance.animation = 'items-next-animation';
					} );

					jetMenu.eventBus.$on( 'showTemplateContent', function ( payLoad ) {

						if ( vueInstance.menuOptions.menuUniqId !== payLoad.menuUniqId ) {
							return;
						}

						vueInstance.itemTemplateContent = payLoad.elementorContent;
						vueInstance.templateVisible = true;
						vueInstance.breadcrumbsData.push( payLoad.name );
						vueInstance.animation = 'items-next-animation';

						vueInstance.showTemplateContent();
					} );

				},

				computed: {
					instanceClass: function() {
						let classes = [
							`jet-mobile-menu__instance--${ this.menuOptions.menuLayout }-layout`,
							`${ this.menuOptions.menuPosition }-container-position`,
							`${ this.menuOptions.togglePosition || 'default' }-toggle-position`,
						];

						return classes;
					},

					menuId: function() {

						if ( this.menuOptions.mobileMenuId ) {
							return this.menuOptions.mobileMenuId;
						}

						return this.menuOptions.menuId;
					},

					coverVisible: function() {

						let avaliableForLayout = [
							'slide-out'
						];

						return avaliableForLayout.includes( this.menuOptions.menuLayout );
					},

					itemsList: function() {

						if ( ! this.children ) {
							return this.itemsRawData;
						}

						return this.children;
					},

					isClose: function() {
						let avaliableForLayout = [
							'slide-out'
						];

						return avaliableForLayout.includes( this.menuOptions.menuLayout );
					},

					isBack: function() {
						return this.children || this.templateVisible ? true : false;
					},

					isBreadcrumbs: function() {
						return this.menuOptions.useBreadcrumb && 0 !== this.breadcrumbsData.length ? true : false;
					},

					depth: function() {
						return this.trail.length;
					},

					containerPosition: function() {
						return this.menuOptions['menuPosition'];
					},

					showAnimation: function() {

						let animation = false;

						switch( this.menuOptions.menuLayout ) {

							case 'slide-out':
								animation = 'right' === this.containerPosition ? 'menu-container-left-animation' : 'menu-container-right-animation';
								break;

							case 'dropdown':
								animation = 'menu-container-dropdown-animation';
								break;

							case 'push':
								animation = 'menu-container-expand-animation';
								break;
						}

						return animation;
					},

					toggleClosedIcon: function() {
						return '' !== this.menuOptions.toggleClosedIcon ? this.menuOptions.toggleClosedIcon : '<i class="fas fa-bars"></i>';
					},

					toggleOpenedIcon: function() {
						return '' !== this.menuOptions.toggleOpenedIcon ? this.menuOptions.toggleOpenedIcon : '<i class="fas fa-times"></i>';
					},

					toggleText: function() {
						return '' !== this.menuOptions.toggleText ? this.menuOptions.toggleText : false;
					},

					closeIcon: function() {
						return '' !== this.menuOptions.closeIcon ? this.menuOptions.closeIcon : '<i class="fas fa-times"></i>';
					},

					backIcon: function() {
						let backIcon = '' !== this.menuOptions.backIcon ? this.menuOptions.backIcon : '<i class="fas fa-arrow-left"></i>',
							backText = '' !== this.menuOptions.backText ? `<span>${ this.menuOptions.backText }</span>` : '',
							backHtml = backIcon + backText;

						return backHtml;
					},

					backText: function() {
						return '' !== this.menuOptions.backText ? this.menuOptions.backText : false;
					},

					breadcrumbIcon: function() {
						return '' !== this.menuOptions.breadcrumbIcon ? this.menuOptions.breadcrumbIcon : '<i class="fas fa-angle-rightt"></i>';
					},

					loaderColor: function() {
						return this.menuOptions.loaderColor || '#3a3a3a';
					},

					toggleLoaderVisible: function() {
						return this.menuOptions.toggleLoader && this.instanceLoadStatus ? true : false;
					},

					beforeTemplateVisible: function() {
						return this.beforeContent ? true : false;
					},

					afterTemplateVisible: function() {
						return this.afterContent ? true : false;
					}

				},

				watch: {
					menuOpen: function( curr, prev ) {

						if ( curr ) {
							jetMenu.$body.addClass( 'jet-mobile-menu-visible' );

							this.initTemplatesContent();
						}

						if ( ! curr ) {
							jetMenu.$body.removeClass( 'jet-mobile-menu-visible' );
							this.clearStates();
						}
					}
				},

				methods: {
					menuToggle: function() {
						this.menuOpen = ! this.menuOpen;
					},

					closeMenu: function() {
						this.menuOpen = false;
					},

					clearStates: function() {
						this.trail = [];
						this.children = false;
						this.breadcrumbsData = [];
						this.templateVisible = false;
					},

					goBack: function() {

						this.animation = 'items-prev-animation';

						if ( ! this.templateVisible ) {
							this.$delete( this.trail, this.trail.length - 1 );
							this.children = this.trail.slice(-1)[0] || false;
						} else {
							this.templateVisible = false;
						}

						this.$delete(this.breadcrumbsData, this.breadcrumbsData.length - 1);
					},

					showTemplateContent: function() {
						let vueInstance = this;

						this.$nextTick( function() {
							let $templateContainer = $( vueInstance.$refs['template-content'] ).find( '.jet-mobile-menu__template-content' );

							jetMenu.elementorContentRender( $templateContainer );
						} );
					},

					initTemplatesContent: function() {
						let vueInstance = this;

						this.$nextTick( function() {

							if ( vueInstance.headerContent ) {
								let $headerContainer = $( vueInstance.$refs['header-template-content'] );

								jetMenu.elementorContentRender( $headerContainer );
							}

							if ( vueInstance.beforeContent ) {
								let $beforeContainer = $( vueInstance.$refs['before-template-content'] );

								jetMenu.elementorContentRender( $beforeContainer );
							}

							if ( vueInstance.beforeContent ) {
								let $afterContainer = $( vueInstance.$refs['after-template-content'] );

								jetMenu.elementorContentRender( $afterContainer );
							}
						} );
					},

					breadcrumbHandle: function( index ) {

						if ( index === this.breadcrumbsData.length ) {
							return;
						}

						this.animation = 'items-prev-animation';

						this.trail = this.trail.slice( 0, index );
						this.children = this.trail.slice( -1 )[0] || false;
						this.templateVisible = false;
						this.breadcrumbsData = this.breadcrumbsData.slice( 0, index );
					}

				}
			});
		},

		loadScriptAsync: function( script, uri ) {

			if ( jetMenu.addedScripts.hasOwnProperty( script ) ) {
				return script;
			}

			jetMenu.addedScripts[ script ] = uri;

			return new Promise( function( resolve, reject ) {
				var tag = document.createElement( 'script' );

					tag.src    = uri;
					tag.async  = true;
					tag.onload = function() {
						resolve( script );
					};

				document.head.appendChild( tag );
			} );
		},

		loadStyle: function( style, uri ) {

			if ( jetMenu.addedStyles.hasOwnProperty( style ) && jetMenu.addedStyles[ style ] ===  uri) {
				return style;
			}

			jetMenu.addedStyles[ style ] = uri;

			return new Promise( function( resolve, reject ) {
				var tag = document.createElement( 'link' );

					tag.id      = style;
					tag.rel     = 'stylesheet';
					tag.href    = uri;
					tag.type    = 'text/css';
					tag.media   = 'all';
					tag.onload  = function() {
						resolve( style );
					};

				document.head.appendChild( tag );
			});
		},

		elementorContentRender: function( $templateContainer, templateContent ) {
			let content = templateContent || false;

			Promise.all( jetMenu.addedAssetsPromises ).then( function( value ) {

				if ( templateContent ) {
					$templateContainer.html( templateContent );
				}

				jetMenu.elementorFrontendInit( $templateContainer );
			}, function( reason ) {
				console.log( 'Script Loaded Error' );
			});
		},

		elementorFrontendInit: function( $templateContainer ) {

			$templateContainer.find( 'div[data-element_type]' ).each( function() {
				var $this       = $( this ),
					elementType = $this.data( 'element_type' );

				if ( ! elementType ) {
					return;
				}

				try {
					if ( 'widget' === elementType ) {
						elementType = $this.data( 'widget_type' );

						if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
							window.elementorFrontend.hooks.doAction( 'frontend/element_ready/widget', $this, $ );
						}
					}

					if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
						window.elementorFrontend.hooks.doAction( 'frontend/element_ready/' + elementType, $this, $ );
					}

				} catch ( err ) {
					console.log(err);

					$this.remove();

					return false;
				}
			} );
		}

	};

	jetMenu.init();

} ( jQuery ) );
