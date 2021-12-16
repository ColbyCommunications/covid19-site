'use strict';

let jetPopupSettinsMixin = {
	data: function() {
		return {
			pageOptions: window.jetPopupSettingsConfig.settings,
			preparedOptions: {},
			savingStatus: false,
			ajaxSaveHandler: null,
		};
	},

	watch: {
		pageOptions: {
			handler( options ) {
				let prepared = {};

				for ( let option in options ) {

					if ( options.hasOwnProperty( option ) ) {
						prepared[ option ] = options[ option ];
					}
				}

				this.preparedOptions = prepared;

				this.saveOptions();
			},
			deep: true
		}
	},

	methods: {
		saveOptions: function() {
			var self = this;

			self.ajaxSaveHandler = jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'jet_popup_save_settings',
					'data': self.preparedOptions
				},
				beforeSend: function( jqXHR, ajaxSettings ) {

					if ( null !== self.ajaxSaveHandler ) {
						self.ajaxSaveHandler.abort();
					}

					self.savingStatus = true;
				},
				success: function( data, textStatus, jqXHR ) {
					self.savingStatus = false;

					switch ( data.type ) {
						case 'success':
							self.$CXNotice.add( {
								message: data.desc,
								type: 'success',
								duration: 3000,
							} );
						break;
						case 'error':
							self.$CXNotice.add( {
								message: data.desc,
								type: 'error',
								duration: 3000,
							} );
						break;
					}
				}
			} );

		},
	}
}

let jetPopupSettinsEventBus = new Vue();

Vue.component( 'mailchimp-list-item', {
	template: '#jet-dashboard-mailchimp-list-item',

	props: {
		list: Object,
		apikey: String
	},

	data: function() {
		return {
			mergeFieldsStatusLoading: false
		}
	},

	computed: {
		isMergeFields: function() {
			return this.list.hasOwnProperty( 'mergeFields' ) && ! jQuery.isEmptyObject( this.list[ 'mergeFields' ] ) ? true : false;
		}
	},

	methods: {
		getMergeFields: function() {
			let self = this;

			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'get_mailchimp_list_merge_fields',
					'apikey': self.apikey,
					'listid': self.list.id
				},
				beforeSend: function( jqXHR, ajaxSettings ) {
					self.mergeFieldsStatusLoading = true;
				},
				success: function( data, textStatus, jqXHR ) {
					self.mergeFieldsStatusLoading = false;

					switch ( data.type ) {
						case 'success':
							self.$CXNotice.add( {
								message: data.desc,
								type: 'success',
								duration: 3000,
							} );

							jetPopupSettinsEventBus.$emit( 'updateListMergeFields', data.request );
						break;
						case 'error':
							self.$CXNotice.add( {
								message: data.desc,
								type: 'error',
								duration: 3000,
							} );
						break;
					}
				}
			} );
		}
	}
});

Vue.component( 'jet-popup-integrations', {
	template: '#jet-dashboard-jet-popup-integrations',

	mixins: [ jetPopupSettinsMixin ],

	data: function() {
		return ( {
			settingsData: window.jetPopupSettingsConfig.settings || {},
			mailchimpApiData: window.jetPopupSettingsConfig.mailchimpApiData || {},
			saveStatusLoading: false,
			syncStatusLoading: false,
			mergeFieldsStatusLoading: false,
			mailchimpAccountData: {},
			mailchimpListsData: {}
		} )
	},

	mounted: function() {
		var self = this;

		if ( this.mailchimpApiData.hasOwnProperty( this.settingsData['apikey'] ) ) {
			var user = this.mailchimpApiData[ this.settingsData['apikey'] ];

			if ( user.hasOwnProperty( 'account' ) ) {
				var account = user.account;

				this.mailchimpAccountData = {
					account_id: account.account_id,
					username: account.username || '-',
					first_name: account.first_name || '-',
					last_name: account.last_name || '-',
					avatar_url: account.avatar_url
				};
			}

			if ( user.hasOwnProperty( 'lists' ) ) {
				var lists     = user.lists,
					tempLists = {};

				if ( ! jQuery.isEmptyObject( lists ) ) {
					for ( var key in lists ) {
						var listInfo    = lists[ key ]['info'],
							mergeFields = lists[ key ]['merge_fields'] || [],
							mergeFieldsTemp = {};

						mergeFields.forEach( function( field, i, arr ) {
							mergeFieldsTemp[ field['tag'] ] = field['name'];
						} );

						tempLists[ key ] = {
							id: listInfo.id,
							name: listInfo.name,
							memberCount: listInfo.stats.member_count,
							doubleOptin: listInfo.double_optin,
							dateCreated: listInfo.date_created,
							mergeFields: mergeFieldsTemp
						};
					}
				}

				this.mailchimpListsData = tempLists;

			}
		}

		// Bus Events
		jetPopupSettinsEventBus.$on( 'updateListMergeFields', function ( request ) {
			var listid          = request.list_id,
				mergeFields     = request.merge_fields,
				mergeFieldsTemp = {};

			for ( key in mergeFields ) {
				var fieldData = mergeFields[ key ];

				mergeFieldsTemp[ fieldData['tag'] ] = fieldData['name'];
			}

			Vue.set( self.mailchimpListsData[ listid ], 'mergeFields', mergeFieldsTemp );
		} );
	},

	computed: {
		isMailchimpAccountData: function() {
			return ! jQuery.isEmptyObject( this.mailchimpAccountData ) ? true : false;
		},

		isMailchimpListsData: function() {
			return ! jQuery.isEmptyObject( this.mailchimpListsData ) ? true : false;
		}
	},

	methods: {
		mailchimpSync: function() {
			var self = this;

			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'get_mailchimp_user_data',
					'apikey': this.settingsData.apikey
				},
				beforeSend: function( jqXHR, ajaxSettings ) {
					self.syncStatusLoading = true;
				},
				success: function( data, textStatus, jqXHR ) {

					switch ( data.type ) {
						case 'success':
							var dataRequest = data.request;

							self.$CXNotice.add( {
								message: data.desc,
								type: 'success',
								duration: 3000,
							} );

							self.mailchimpAccountData = {
								account_id: dataRequest.account_id,
								username: dataRequest.username || '-',
								first_name: dataRequest.first_name || '-',
								last_name: dataRequest.last_name || '-',
								avatar_url: dataRequest.avatar_url
							};

							self.mailchimpSyncLists();

						break;
						case 'error':
							self.syncStatusLoading = false;

							self.$CXNotice.add( {
								message: data.desc,
								type: 'error',
								duration: 3000,
							} );
						break;
					}
				}
			} );

		},

		mailchimpSyncLists: function() {
			var self = this;

			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'get_mailchimp_lists',
					'apikey': this.settingsData.apikey
				},
				beforeSend: function( jqXHR, ajaxSettings ) {
					self.syncStatusLoading = true;
				},
				success: function( data, textStatus, jqXHR ) {

					self.syncStatusLoading = false;

					switch ( data.type ) {

						case 'success':

							self.$CXNotice.add( {
								message: data.desc,
								type: 'success',
								duration: 3000,
							} );

							var request = data.request;

							if ( request.hasOwnProperty( 'lists' ) ) {
								var lists     = request['lists'],
									tempLists = {};

								for ( var key in lists ) {
									var listData = lists[ key ];

									tempLists[ listData.id ] = {
										id: listData.id,
										name: listData.name,
										memberCount: listData.stats.member_count,
										doubleOptin: listData.double_optin,
										dateCreated: listData.date_created
									}
								}

								self.mailchimpListsData = tempLists;
							}
						break;

						case 'error':
							vueInstance.$CXNotice.add( {
								message: data.desc,
								type: 'error',
								duration: 3000,
							} );
						break;
					}
				}
			} );
		}

	}

} );

