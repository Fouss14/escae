(function($) {
	'use strict';
	$(document).ready(function() {
		// Function: Initialize Datatable.
		function wlsmInitializeTable(table, data) {
			table.DataTable({
				'processing': true,
				'serverSide': true,
				'responsive': true,
				'order': [],
				'ajax': {
					url: ajaxurl,
					type: 'POST',
					data: data
				},
				'deferRender': true,
				'language': {
					'processing': wlsmloadingtext.loadingtext
				},
				'lengthMenu': [25, 50, 100, 200]
			});
		}

		// Manager: School Classes Table.
		var schoolClassesTable = $('#wlsm-school-classes-table');
		var school = schoolClassesTable.data('school');
		var nonce = schoolClassesTable.data('nonce');
		if ( school && nonce ) {
			var data = { action: 'wlsm-fetch-school-classes', school: school };
			data['school-classes-' + school] = nonce;
			wlsmInitializeTable(schoolClassesTable, data);
		}

		// Manager: Admins Table.
		var schoolAdminsTable = $('#wlsm-school-admins-table');
		var school = schoolAdminsTable.data('school');
		var nonce = schoolAdminsTable.data('nonce');
		if ( school && nonce ) {
			var data = { action: 'wlsm-fetch-school-admins', school: school };
			data['school-admins-' + school] = nonce;
			wlsmInitializeTable(schoolAdminsTable, data);
		}

		// Manager: Classes Table.
		var classesTable = $('#wlsm-classes-table');
		wlsmInitializeTable(classesTable, { action: 'wlsm-fetch-classes' });

		// Manager: Sessions Table.
		var sessionsTable = $('#wlsm-sessions-table');
		wlsmInitializeTable(sessionsTable, { action: 'wlsm-fetch-sessions' });

		// Copy target content to clipboard on click.
		function copyToClipboard(selector, target) {
			$(document).on('click', selector, function () {
				var value = $(target).text();
				var temp = $('<input>');
				$('body').append(temp);
				temp.val(value).select();
				document.execCommand('copy');
				temp.remove();
				toastr.success();
			});
		}

		// Initialize datatable without server side.
		function wlsmInitializeDataTable(selector, length = [25, 50, 100, 200], action = '', data = '') {
			var options = {
				'responsive': true,
				'order': [],
				'language': {
					'processing': wlsmloadingtext.loadingtext
				},
				'pageLength': 0,
				'lengthMenu': length
			}
			if(action) {
				options['ajax'] = {
					url: ajaxurl + '?security=' + wlsmsecurity.nonce + '&action=' + action + data,
					dataSrc: 'data'
				}; 
			}
			selector.DataTable(options);
		}

		// Copy shortcodes.
		copyToClipboard('#wlsm_school_management_fees_copy_btn', '#wlsm_school_management_fees_shortcode');
		copyToClipboard('#wlsm_school_management_account_copy_btn', '#wlsm_school_management_account_shortcode');
		copyToClipboard('#wlsm_school_management_inquiry_copy_btn', '#wlsm_school_management_inquiry_shortcode');
		copyToClipboard('#wlsm_school_management_fees_default_session_copy_btn', '#wlsm_school_management_fees_default_session_shortcode');
		copyToClipboard('#wlsm_school_management_noticeboard_copy_btn', '#wlsm_school_management_noticeboard_shortcode');

		// Loading icon variables.
		var loaderContainer = $('<span/>', {
			'class': 'wlsm-loader ml-2'
		});
		var loader = $('<img/>', {
			'src': wlsmadminurl.wlsmAdminURL + 'images/spinner.gif',
			'class': 'wlsm-loader-image mb-1'
		});

		// Function: Before Submit.
		function wlsmBeforeSubmit(button) {
			$('div.text-danger').remove();
			$(".is-invalid").removeClass("is-invalid");
			$('.wlsm .alert-dismissible').remove();
			button.prop('disabled', true);
			loaderContainer.insertAfter(button);
			loader.appendTo(loaderContainer);
			return true;
		}

		// Function: Display Form Erros.
		function wlsmDisplayFormErrors(response, formId) {
			if(response.data && $.isPlainObject(response.data)) {
				$(formId + ' :input').each(function() {
					var input = this;
					$(input).removeClass('is-invalid');
					if(response.data[input.name]) {
						var errorSpan = '<div class="text-danger mt-1">' + response.data[input.name] + '</div>';
						$(input).addClass('is-invalid');
						$(errorSpan).insertAfter(input);
					}
				});
			} else {
				var errorSpan = '<div class="text-danger mt-3">' + response.data + '<hr></div>';
				$(errorSpan).insertBefore(formId);
				toastr.error(response.data);
			}
		}

		// Function: Show Success Alert.
		function wlsmShowSuccessAlert(message, formId) {
			var alertBox = '<div class="mt-2 alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="wlsm-font-bold"><i class="fa fa-check"></i> &nbsp;' + message + '</span></div>';
			$(alertBox).insertBefore(formId);
		}

		// Function: Display Form Error.
		function wlsmDisplayFormError(response, formId, button) {
			button.prop('disabled', false);
			var errorSpan = '<div class="text-danger mt-2"><span class="wlsm-font-bold">' + response.status + '</span>: ' + response.statusText + '<hr></div>';
			$(errorSpan).insertBefore(formId);
			toastr.error(response.data);
		}

		// Function: Complete.
		function wlsmComplete(button) {
			button.prop('disabled', false);
			loaderContainer.remove();
		}

		var subHeader = '.wlsm-sub-header-left';

		// Function: Delete.
		function wlsmDelete(event, element, data, performActions) {
			event.preventDefault();
			$('.wlsm .alert-dismissible').remove();
			var title = $(element).data('message-title');
			var content = $(element).data('message-content');
			var cancel = $(element).data('cancel');
			var submit = $(element).data('submit');
			$.confirm({
				title: title,
				content: content,
				type: 'red',
				useBootstrap: false,
				buttons: {
					formSubmit: {
						text: submit,
           				btnSession: 'btn-red',
						action: function () {
							$.ajax({
								data: data,
								url: ajaxurl,
								type: 'POST',
								beforeSend: function(xhr) {
									$('.wlsm .alert-dismissible').remove();
								},
								success: function(response) {
									if(response.success) {
										var alertBox = '<div class="alert alert-success alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i> &nbsp;' + response.data.message + '</strong></div>';
										$(alertBox).insertBefore(subHeader);
										toastr.success(
											response.data.message,
											'',
											{
												timeOut: 600,
												fadeOut: 600,
												closeButton: true,
												progressBar: true,
												onHidden: function() {
													performActions(response);
												}
											}
										);
									} else {
										var errorSpan = '<div class="alert alert-danger alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + response.data + '</strong></div>';
										$(errorSpan).insertBefore(subHeader);
									}
								},
								error: function(response) {
									var errorSpan = '<div class="alert alert-danger alert-dismissible clearfix" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + response.status + '</strong>: ' + response.statusText + '</div>';
									$(errorSpan).insertBefore(subHeader);
								}
							});
						}
					},
					cancel: {
						text: cancel,
						action: function () {
							$('.wlsm .alert-dismissible').remove();
						}
					}
				}
			});
		}

		// Manager: Save school.
		var saveSchoolFormId = '#wlsm-save-school-form';
		var saveSchoolForm = $(saveSchoolFormId);
		var saveSchoolBtn = $('#wlsm-save-school-btn');
		saveSchoolForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveSchoolForm[0].reset();
					} else {
						$('.wlsm-page-heading-box').load(location.href + " " + '.wlsm-page-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolFormId, saveSchoolBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolBtn);
			}
		});

		// Manager: Delete school.
		$(document).on('click', '.wlsm-delete-school', function(event) {
			event.preventDefault();
			var schoolId = $(this).data('school');
			var nonce = $(this).data('nonce');
			var data = "school_id=" + schoolId + "&delete-school-" + schoolId + "=" + nonce + "&action=wlsm-delete-school";
			var performActions = function() {
				schoolsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Manager: Delete school class.
		$(document).on('click', '.wlsm-delete-school-class', function(event) {
			event.preventDefault();
			var classId = $(this).data('class');
			var schoolId = $(this).data('school');
			var nonce = $(this).data('nonce');
			var data = "school_id=" + schoolId + "&class_id=" + classId + "&delete-school-class-" + classId + "=" + nonce + "&action=wlsm-delete-school-class";
			var performActions = function() {
				window.location.reload();
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Manager: Set school.
		$(document).on('click', '.wlsm-school-card-link', function(event) {
			var nonce = $(this).data('nonce');
			var school = $(this).data('school');
			$.ajax({
				data: "school=" + school + "&set-school-" + school + "=" + nonce + "&action=wlsm-set-school",
				url: ajaxurl,
				type: 'POST',
				beforeSend: function(xhr) {
					$('.wlsm .alert-dismissible').remove();
				},
				success: function(response) {
					if(response.success) {
						toastr.success(response.data.message);
						window.location.href = response.data.url;
					} else {
						if ( response.data ) {
							toastr.error(response.data);
						}
					}
				},
				error: function(response) {
					if ( response.data ) {
						toastr.error(response.data);
					}
				}
			});
		});

		// Manager: Autocomplete classes.
		var classSearch = $('#wlsm_class_search');
		$('#wlsm_class_search').autocomplete({
			minLength: 1,
			source: function(request, response) {
				$.ajax({
					data: 'action=wlsm-get-keyword-classes&keyword=' + request.term,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						if(res.success) {
							response(res.data);
						} else {
							response([]);
						}
					}
				});
			},
			select: function(event, ui) {
				classSearch.val('');
				var id = ui.item.ID;
				var label = ui.item.label;
				var classesInput = $('.wlsm_school_classes_input');
				if(classesInput) {
					var classesToAdd = classesInput.map(function() { return $(this).val(); }).get();
					if(-1 !== $.inArray(id, classesToAdd)) {
						return false;
					}
				}
				if(id) {
					$('.wlsm_school_classes').append('' +
						'<div class="wlsm-school-class-item mb-1">' +
							'<input class="wlsm_school_classes_input" type="hidden" name="classes[]" value="' + id + '">' +
							'<span class="wlsm-badge badge badge-info">' +
								label +
							'</span>' + '&nbsp;<i class="fa fa-times bg-danger text-white wlsm-remove-item"></i>' +
						'</div>' +
					'');
					return false;
				}
				return false;
			}
		});

		// Remove parent on click
		$(document).on('click', '.wlsm-remove-item', function() {
			$(this).parent().remove();
		});

		// Manager: Assign school classes.
		var assignClassesFormId = '#wlsm-assign-classes-form';
		var assignClassesForm = $(assignClassesFormId);
		var assignClassesBtn = $('#wlsm-assign-classes-btn');
		assignClassesForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(assignClassesBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, assignClassesFormId);
					toastr.success(response.data.message);
					window.location.reload();
					$('.wlsm_school_classes').html('');
				} else {
					wlsmDisplayFormErrors(response, assignClassesFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, assignClassesFormId, assignClassesBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(assignClassesBtn);
			}
		});

		// Manager: Add new or existing admin user.
		var existingUser = $('.wlsm-assign-exisitng-user');
		var newUser = $('.wlsm-assign-new-user');
		$(document).on('change', 'input[name="new_or_existing"]', function(event) {
			var user = this.value;
			$('.wlsm-assign-user').hide();
			if('new_user' === user) {
				newUser.fadeIn();
			} else {
				existingUser.fadeIn();
			}
		});

		// Manager: Assign school admin.
		var assignAdminFormId = '#wlsm-assign-admin-form';
		var assignAdminForm = $(assignAdminFormId);
		var assignAdminBtn = $('#wlsm-assign-admin-btn');
		assignAdminForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(assignAdminBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, assignAdminFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						assignAdminForm[0].reset();
					}
					schoolAdminsTable.DataTable().ajax.reload(null, false);
				} else {
					wlsmDisplayFormErrors(response, assignAdminFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, assignAdminFormId, assignAdminBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(assignAdminBtn);
			}
		});

		// Manager: Save new or existing admin user.
		var saveAdminExistingUser = $('.wlsm-school-admin-existing-user');
		var saveAdminNewUser = $('.wlsm-school-admin-new-user');
		$(document).on('change', 'input[name="save_new_or_existing"]', function(event) {
			var user = this.value;
			$('.wlsm-save-school-admin').hide();
			if('new_user' === user) {
				saveAdminNewUser.fadeIn();
			} else {
				saveAdminExistingUser.fadeIn();
			}
		});

		// Manager: Save school admin.
		var saveSchoolAdminFormId = '#wlsm-save-school-admin-form';
		var saveSchoolAdminForm = $(saveSchoolAdminFormId);
		var saveSchoolAdminBtn = $('#wlsm-save-school-admin-btn');
		saveSchoolAdminForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolAdminBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolAdminFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolAdminFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolAdminFormId, saveSchoolAdminBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolAdminBtn);
			}
		});

		// Manager: Delete school admin.
		$(document).on('click', '.wlsm-delete-school-admin', function(event) {
			var staffId = $(this).data('admin');
			var nonce = $(this).data('nonce');
			var data = "staff_id=" + staffId + "&delete-school-admin-" + staffId + "=" + nonce + "&action=wlsm-delete-school-admin";
			var performActions = function() {
				schoolAdminsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Manager: Save class.
		var saveClassFormId = '#wlsm-save-class-form';
		var saveClassForm = $(saveClassFormId);
		var saveClassBtn = $('#wlsm-save-class-btn');
		saveClassForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveClassBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveClassFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveClassForm[0].reset();
					} else {
						$('.wlsm-page-heading-box').load(location.href + " " + '.wlsm-page-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveClassFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveClassFormId, saveClassBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveClassBtn);
			}
		});

		// Manager: Delete class.
		$(document).on('click', '.wlsm-delete-class', function(event) {
			var classId = $(this).data('class');
			var nonce = $(this).data('nonce');
			var data = "class_id=" + classId + "&delete-class-" + classId + "=" + nonce + "&action=wlsm-delete-class";
			var performActions = function() {
				classesTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Manager: Save session.
		var saveSessionFormId = '#wlsm-save-session-form';
		var saveSessionForm = $(saveSessionFormId);
		var saveSessionBtn = $('#wlsm-save-session-btn');
		saveSessionForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSessionBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSessionFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveSessionForm[0].reset();
					} else {
						$('.wlsm-page-heading-box').load(location.href + " " + '.wlsm-page-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveSessionFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSessionFormId, saveSessionBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSessionBtn);
			}
		});

		// Manager: Delete session.
		$(document).on('click', '.wlsm-delete-session', function(event) {
			var sessionId = $(this).data('session');
			var nonce = $(this).data('nonce');
			var data = "session_id=" + sessionId + "&delete-session-" + sessionId + "=" + nonce + "&action=wlsm-delete-session";
			var performActions = function() {
				sessionsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Session start date.
		$('#wlsm_start_date').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true,
			view: 'years'
		});

		// Session end date.
		$('#wlsm_end_date').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true,
			view: 'years'
		});

		// Manager: Save general settings.
		var saveGeneralSettingsFormId = '#wlsm-save-general-settings-form';
		var saveGeneralSettingsForm = $(saveGeneralSettingsFormId);
		var saveGeneralSettingsBtn = $('#wlsm-save-general-settings-btn');
		saveGeneralSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveGeneralSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveGeneralSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveGeneralSettingsForm[0].reset();
					} else {
						$('.wlsm-page-heading-box').load(location.href + " " + '.wlsm-page-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveGeneralSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveGeneralSettingsFormId, saveGeneralSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveGeneralSettingsBtn);
			}
		});

		// Manager: Save uninstall settings.
		var saveUninstallSettingsFormId = '#wlsm-save-uninstall-settings-form';
		var saveUninstallSettingsForm = $(saveUninstallSettingsFormId);
		var saveUninstallSettingsBtn = $('#wlsm-save-uninstall-settings-btn');
		saveUninstallSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveUninstallSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveUninstallSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveUninstallSettingsForm[0].reset();
					}
				} else {
					wlsmDisplayFormErrors(response, saveUninstallSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveUninstallSettingsFormId, saveUninstallSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveUninstallSettingsBtn);
			}
		});

		// Manager: Reset plugin.
		var resetPluginFormId = '#wlsm-reset-plugin-form';
		var resetPluginForm = $(resetPluginFormId);
		var resetPluginBtn = $('#wlsm-reset-plugin-btn');

		$(document).on('click', '#wlsm-reset-plugin-btn', function(event) {
			$.confirm({
				title: resetPluginBtn.data('message-title'),
				content: resetPluginBtn.data('message-content'),
				type: 'red',
				useBootstrap: false,
				buttons: {
					confirm: {
						text: resetPluginBtn.data('submit'),
						btnClass: 'btn-red',
						action: function () {
							resetPluginForm.ajaxSubmit({
								beforeSubmit: function(arr, $form, options) {
									return wlsmBeforeSubmit(resetPluginBtn);
								},
								success: function(response) {
									if(response.success) {
										wlsmShowSuccessAlert(response.data.message, resetPluginFormId);
										toastr.success(response.data.message);
										if(response.data.hasOwnProperty('reload') && response.data.reload) {
											window.location.reload();
										} else if(response.data.hasOwnProperty('reset') && response.data.reset) {
											resetPluginForm[0].reset();
										}
									} else {
										wlsmDisplayFormErrors(response, resetPluginFormId);
									}
								},
								error: function(response) {
									wlsmDisplayFormError(response, resetPluginFormId, resetPluginBtn);
								},
								complete: function(event, xhr, settings) {
									wlsmComplete(resetPluginBtn);
								}
							});
						}
					},
					cancel: {
						text: resetPluginBtn.data('cancel'),
						action: function () {
							return;
						}
					}
				}
			});
		});

		// Staff: Set staff school.
		$(document).on('click', '.wlsm-staff-school-card-link', function(event) {
			var nonce = $(this).data('nonce');
			var school = $(this).data('school');
			$.ajax({
				data: "school=" + school + "&set-school-" + school + "=" + nonce + "&action=wlsm-staff-set-school",
				url: ajaxurl,
				type: 'POST',
				beforeSend: function(xhr) {
					$('.wlsm .alert-dismissible').remove();
				},
				success: function(response) {
					if(response.success) {
						toastr.success(response.data.message);
						window.location.href = response.data.url;
					} else {
						if ( response.data ) {
							toastr.error(response.data);
						}
					}
				},
				error: function(response) {
					if ( response.data ) {
						toastr.error(response.data);
					}
				}
			});
		});

		// Staff: Set current session.
		$(document).on('change', '#wlsm_user_current_session', function(event) {
			var nonce = $(this).find(':selected').data('nonce');
			var session = this.value;
			$.ajax({
				data: "session=" + session + "&set-session-" + session + "=" + nonce + "&action=wlsm-staff-set-session",
				url: ajaxurl,
				type: 'POST',
				beforeSend: function(xhr) {
					$('.wlsm .alert-dismissible').remove();
				},
				success: function(response) {
					if(response.success) {
						toastr.success(response.data.message);
						window.location.reload();
					} else {
						if ( response.data ) {
							toastr.error(response.data);
						}
					}
				},
				error: function(response) {
					if ( response.data ) {
						toastr.error(response.data);
					}
				}
			});
		});

		// Staff: Classes Table.
		var staffClassesTable = $('#wlsm-staff-classes-table');
		wlsmInitializeTable(staffClassesTable, { action: 'wlsm-fetch-staff-classes' });

		// Staff: Class Sections Table.
		var classSectionsTable = $('#wlsm-class-sections-table');
		var classSchool = classSectionsTable.data('class-school');
		var nonce = classSectionsTable.data('nonce');
		if ( classSchool && nonce ) {
			var data = { action: 'wlsm-fetch-class-sections', 'class_school': classSchool };
			data['class-sections-' + classSchool] = nonce;
			wlsmInitializeTable(classSectionsTable, data);
		}

		// Staff: Save class sections.
		var saveSectionFormId = '#wlsm-save-section-form';
		var saveSectionForm = $(saveSectionFormId);
		var saveSectionBtn = $('#wlsm-save-section-btn');
		saveSectionForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSectionBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSectionFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveSectionForm[0].reset();
						classSectionsTable.DataTable().ajax.reload(null, false);
					} else {
						$('.wlsm-page-heading-box').load(location.href + " " + '.wlsm-page-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveSectionFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSectionFormId, saveSectionBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSectionBtn);
			}
		});

		// Staff: Delete class section.
		$(document).on('click', '.wlsm-delete-section', function(event) {
			event.preventDefault();
			var sectionId = $(this).data('section');
			var classId = $(this).data('class');
			var nonce = $(this).data('nonce');
			var data = "class_id=" + classId + "&section_id=" + sectionId + "&delete-section-" + sectionId + "=" + nonce + "&action=wlsm-delete-section";
			var performActions = function() {
				classSectionsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Date of birth.
		$('#wlsm_date_of_birth').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true,
			view: 'years',
			direction: false
		});

		// Admission date.
		$('#wlsm_admission_date').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true
		});

		// Staff: Add new or existing student user.
		var studentNewUser = $('.wlsm-student-new-user');
		var studentExistingUser = $('.wlsm-student-existing-user');

		var studentUser = $('input[name="student_new_or_existing"]:checked').val();
		if('new_user' === studentUser) {
			studentExistingUser.fadeIn();
			studentNewUser.fadeIn();
		} else if('existing_user' === studentUser) {
			studentExistingUser.fadeIn();
			studentNewUser.hide();
		} else {
			studentExistingUser.hide();
			studentNewUser.hide();
		}

		$(document).on('change', 'input[name="student_new_or_existing"]', function(event) {
			var studentUser = this.value;

			if('new_user' === studentUser) {
				studentExistingUser.hide();
				studentNewUser.fadeIn();
			} else if('existing_user' === studentUser) {
				studentNewUser.hide();
				studentExistingUser.fadeIn();
			} else {
				studentExistingUser.hide();
				studentNewUser.hide();
			}
		});

		// Custom file input.
		$(document).on('change', '.custom-file-input', function() {
			var fileName = $(this).val().split('\\').pop();
			$(this).siblings('.custom-file-label').addClass('selected').html(fileName);
		});

		// Staff: Add admission.
		var addAdmissionFormId = '#wlsm-add-admission-form';
		var addAdmissionForm = $(addAdmissionFormId);
		var addAdmissionBtn = $('#wlsm-add-admission-btn');
		addAdmissionForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(addAdmissionBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, addAdmissionFormId);
					toastr.success(response.data.message);
					addAdmissionForm[0].reset();
					var selectPicker = $('.selectpicker');
					selectPicker.selectpicker('refresh');
					$('.wlsm-photo-box').load(location.href + " " + '.wlsm-photo-section', function () {});
				} else {
					wlsmDisplayFormErrors(response, addAdmissionFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, addAdmissionFormId, addAdmissionBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(addAdmissionBtn);
			}
		});

		// Staff: Edit student.
		var editStudentFormId = '#wlsm-edit-student-form';
		var editStudentForm = $(editStudentFormId);
		var editStudentBtn = $('#wlsm-edit-student-btn');
		editStudentForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(editStudentBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, editStudentFormId);
					toastr.success(response.data.message);
                    window.location.reload();
				} else {
					wlsmDisplayFormErrors(response, editStudentFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, editStudentFormId, editStudentBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(editStudentBtn);
			}
		});

		// Staff: Search students.
		var searchKeywordStudents = $('.wlsm-search-keyword-students');
		var searchClassStudents = $('.wlsm-search-class-students');
		$(document).on('change', 'input[name="search_students_by"]', function(event) {
			var searchBy = this.value;
			$('.wlsm-search-students').hide();
			if('search_by_class' === searchBy) {
				searchKeywordStudents.hide();
				searchClassStudents.fadeIn();
			} else {
				searchClassStudents.hide();
				searchKeywordStudents.fadeIn();
			}
		});

		$.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			$.each(a, function() {
				if (o[this.name]) {
					if (!o[this.name].push) {
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
					o[this.name] = this.value || '';
				}
			});
			return o;
		};

		// Staff: Students Table.
		function wlsmInitializeStudentsTable() {
			var data = $('#wlsm-get-students-form').serializeObject();
			data['from_table'] = true;
			wlsmInitializeTable($('#wlsm-staff-students-table'), data);
		}
		wlsmInitializeStudentsTable();

		$(document).on('click', '#wlsm-get-students-btn', function(event) {
			event.preventDefault();
			var getStudentsFormId = '#wlsm-get-students-form';
			var getStudentsForm = $(getStudentsFormId);
			var getStudentsBtn = $('#wlsm-get-students-btn');
			getStudentsForm.ajaxSubmit({
				beforeSubmit: function(arr, $form, options) {
					return wlsmBeforeSubmit(getStudentsBtn);
				},
				success: function(response) {
					if(response.success) {
						$('#wlsm-staff-students-table').DataTable().clear().destroy();
						wlsmInitializeStudentsTable();
					} else {
						wlsmDisplayFormErrors(response, getStudentsFormId);
					}
				},
				error: function(response) {
					wlsmDisplayFormError(response, getStudentsFormId, getStudentsBtn);
				},
				complete: function(event, xhr, settings) {
					wlsmComplete(getStudentsBtn);
				}
			});
		});

		// Staff: Get student session records.
		$(document).on('click', '.wlsm-view-session-records', function(event) {
			var element = $(this);
			var studentId = element.data('student');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['student_id'] = studentId;
			data['view-session-records-' + studentId] = nonce;
			data['action'] = 'wlsm-view-session-records';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data.html);
						}
					});
				},
				theme: 'bootstrap',
				useBootstrap: false,
				columnClass: 'medium',
			});
		});

		// Staff: Delete student.
		$(document).on('click', '.wlsm-delete-student', function(event) {
			event.preventDefault();
			var studentId = $(this).data('student');
			var nonce = $(this).data('nonce');
			var data = "student_id=" + studentId + "&delete-student-" + studentId + "=" + nonce + "&action=wlsm-delete-student";
			var performActions = function() {
				$('#wlsm-staff-students-table').DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Print Bulk ID Cards.
		var saveBulkIDCardsFormId = '#wlsm-print-bulk-id-cards-form';
		var saveBulkIDCardsForm = $(saveBulkIDCardsFormId);
		var saveBulkIDCardsBtn = $('#wlsm-print-bulk-id-cards-btn');
		saveBulkIDCardsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveBulkIDCardsBtn);
			},
			success: function(response) {
				if(response.success) {
					var data = JSON.parse(response.data.json);
					$.dialog({
						title: data.message_title,
						content: response.data.html,
						theme: 'bootstrap',
						useBootstrap: true,
						columnClass: 'xlarge',
						containerFluid: true,
						backgroundDismiss: true
					});
				} else {
					wlsmDisplayFormErrors(response, saveBulkIDCardsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveBulkIDCardsFormId, saveBulkIDCardsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveBulkIDCardsBtn);
			}
		});

		// Staff: Print ID Card.
		$(document).on('click', '.wlsm-print-id-card', function(event) {
			var element = $(this);
			var studentId = element.data('id-card');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['student_id'] = studentId;
			data['print-id-card-' + studentId] = nonce;
			data['action'] = 'wlsm-print-id-card';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data.html);
						}
					});
				},
				theme: 'bootstrap',
				useBootstrap: false,
				columnClass: 'large',
				backgroundDismiss: true
			});
		});

		// Staff: Manage promotion.
		var promoteStudentFormId = '#wlsm-promote-student-form';
		var promoteStudentForm = $(promoteStudentFormId);
		var managePromotionBtn = $('#wlsm-manage-promotion-btn');

		$(document).on('click', '#wlsm-manage-promotion-btn', function(e) {
			var studentsToPromote = $('.wlsm-students-to-promote');

			var promoteToSession = $('#wlsm-promote-to-session').val();
			var fromClass = $('#wlsm_from_class').val();
			var toClass = $('#wlsm_to_class').val();
			var nonce = $(this).data('nonce');

			var data = {};
			data['promote_to_session'] = promoteToSession;
			data['from_class'] = fromClass;
			data['to_class'] = toClass;
			data['nonce'] = nonce;
			data['action'] = 'wlsm-manage-promotion';

			if(nonce) {
				$.ajax({
					data: data,
					url: ajaxurl,
					type: 'POST',
					beforeSend: function() {
						return wlsmBeforeSubmit(managePromotionBtn);
					},
					success: function(response) {
						if(response.success) {
							studentsToPromote.html(response.data.html);
						} else {
							wlsmDisplayFormErrors(response, promoteStudentFormId);
						}
					},
					error: function(response) {
						wlsmDisplayFormError(response, getStudentsFormId, managePromotionBtn);
					},
					complete: function(event, xhr, settings) {
						wlsmComplete(managePromotionBtn);
					},
				});
			} else {
				studentsToPromote.html('');
			}
		});

		// Staff: Promote student.
		$(document).on('click', '#wlsm-promote-student-btn', function(e) {
			var promoteStudentBtn = $(this);

			e.preventDefault();
			$.confirm({
				title: promoteStudentBtn.data('message-title'),
				content: promoteStudentBtn.data('message-content'),
				type: 'green',
				useBootstrap: false,
				buttons: {
					confirm: {
						text: promoteStudentBtn.data('submit'),
						btnClass: 'btn-success',
						action: function () {
							promoteStudentForm.ajaxSubmit({
								beforeSubmit: function(arr, $form, options) {
									return wlsmBeforeSubmit(promoteStudentBtn);
								},
								success: function(response) {
									if(response.success) {
										wlsmShowSuccessAlert(response.data.message, promoteStudentFormId);
										toastr.success(response.data.message);
										window.location.reload();
									} else {
										wlsmDisplayFormErrors(response, promoteStudentFormId);
									}
								},
								error: function(response) {
									wlsmDisplayFormError(response, promoteStudentFormId, promoteStudentBtn);
								},
								complete: function(event, xhr, settings) {
									wlsmComplete(promoteStudentBtn);
								}
							});
						}
					},
					cancel: {
						text: promoteStudentBtn.data('cancel'),
						action: function () {
							return;
						}
					}
				}
			});
		});

		$(document).on('change', '#wlsm-select-all', function() {
			if($(this).is(':checked')) {
				$('.wlsm-select-single').prop('checked', true);
			} else {
				$('.wlsm-select-single').prop('checked', false);
			}
		});

		// Staff: Table.
		var staffTable = $('#wlsm-staff-table');
		var staffRole = staffTable.data('role');
		wlsmInitializeTable(staffTable, { action: 'wlsm-fetch-staff-' + staffRole });

		// Staff: Add new or existing staff user.
		var staffNewUser = $('.wlsm-staff-new-user');
		var staffExistingUser = $('.wlsm-staff-existing-user');

		var staffUser = $('input[name="staff_new_or_existing"]:checked').val();
		if('new_user' === staffUser) {
			staffExistingUser.fadeIn();
			staffNewUser.fadeIn();
		} else if('existing_user' === staffUser) {
			staffExistingUser.fadeIn();
			staffNewUser.hide();
		} else {
			staffExistingUser.hide();
			staffNewUser.hide();
		}

		$(document).on('change', 'input[name="staff_new_or_existing"]', function(event) {
			var staffUser = this.value;

			if('new_user' === staffUser) {
				staffExistingUser.hide();
				staffNewUser.fadeIn();
			} else if('existing_user' === staffUser) {
				staffNewUser.hide();
				staffExistingUser.fadeIn();
			} else {
				staffExistingUser.hide();
				staffNewUser.hide();
			}
		});

		// Joining date.
		$('#wlsm_joining_date').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true
		});

		$(document).on('change', '#wlsm_role', function() {
			var role = this.value;
			var nonce = $(this).data('nonce');
			var permissions = $('input[name="permission[]');
			if(role && nonce) {
				$.ajax({
					data: 'action=wlsm-get-role-permissions&nonce=' + nonce + '&role_id=' + role,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						if($.isArray(res)) {
							permissions.each(function(permisson) {
								if($.inArray(this.value, res) > -1) {
									$(this).prop('checked', true);
									$(this).prop('disabled', true);
								} else {
									$(this).prop('checked', false);
									$(this).prop('disabled', false);
								}
							});
						}
					}
				});
			} else {
				permissions.prop('disabled', false);
			}
		});

		// Staff: Save staff.
		var saveStaffFormId = '#wlsm-save-staff-form';
		var saveStaffForm = $(saveStaffFormId);
		var saveStaffBtn = $('#wlsm-save-staff-btn');
		saveStaffForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveStaffBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveStaffFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else {
						saveStaffForm[0].reset();
						var selectPicker = $('.selectpicker');
						selectPicker.selectpicker('refresh');
					}
				} else {
					wlsmDisplayFormErrors(response, saveStaffFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveStaffFormId, saveStaffBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveStaffBtn);
			}
		});

		// Staff: Delete staff.
		$(document).on('click', '.wlsm-delete-staff', function(event) {
			event.preventDefault();
			var staffId = $(this).data('staff');
			var nonce = $(this).data('nonce');
			var role = $(this).data('role');
			var data = "staff_id=" + staffId + "&delete-staff-" + staffId + "=" + nonce + "&action=wlsm-delete-" + role;
			var performActions = function() {
				staffTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Roles Table.
		var rolesTable = $('#wlsm-roles-table');
		wlsmInitializeTable(rolesTable, { action: 'wlsm-fetch-roles' });

		// Staff: Save role.
		var saveRoleFormId = '#wlsm-save-role-form';
		var saveRoleForm = $(saveRoleFormId);
		var saveRoleBtn = $('#wlsm-save-role-btn');
		saveRoleForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveRoleBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveRoleFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveRoleForm[0].reset();
					} else {
						$('.wlsm-section-heading-box').load(location.href + " " + '.wlsm-section-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveRoleFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveRoleFormId, saveRoleBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveRoleBtn);
			}
		});

		// Staff: Delete role.
		$(document).on('click', '.wlsm-delete-role', function(event) {
			var roleId = $(this).data('role');
			var nonce = $(this).data('nonce');
			var data = "role_id=" + roleId + "&delete-role-" + roleId + "=" + nonce + "&action=wlsm-delete-role";
			var performActions = function() {
				rolesTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Inquiries Table.
		var inquiriesTable = $('#wlsm-inquiries-table');
		wlsmInitializeTable(inquiriesTable, { action: 'wlsm-fetch-inquiries' });

		// Staff: Get inquiry message.
		$(document).on('click', '.wlsm-view-inquiry-message', function(event) {
			var element = $(this);
			var inquiryId = element.data('inquiry');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['inquiry_id'] = inquiryId;
			data['view-inquiry-message-' + inquiryId] = nonce;
			data['action'] = 'wlsm-view-inquiry-message';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data);
						}
					});
				},
				theme: 'bootstrap',
				columnClass: 'medium',
			});
		});

		// Inquiry next follow up date.
		$('#wlsm_inquiry_next_follow_up').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true
		});

		// Staff: Save inquiry.
		var saveInquiryFormId = '#wlsm-save-inquiry-form';
		var saveInquiryForm = $(saveInquiryFormId);
		var saveInquiryBtn = $('#wlsm-save-inquiry-btn');
		saveInquiryForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveInquiryBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveInquiryFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveInquiryForm[0].reset();
						var selectPicker = $('.selectpicker');
						selectPicker.selectpicker('refresh');
					} else {
						$('.wlsm-section-heading-box').load(location.href + " " + '.wlsm-section-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveInquiryFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveInquiryFormId, saveInquiryBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveInquiryBtn);
			}
		});

		// Staff: Delete inquiry.
		$(document).on('click', '.wlsm-delete-inquiry', function(event) {
			event.preventDefault();
			var inquiryId = $(this).data('inquiry');
			var nonce = $(this).data('nonce');
			var data = "inquiry_id=" + inquiryId + "&delete-inquiry-" + inquiryId + "=" + nonce + "&action=wlsm-delete-inquiry";
			var performActions = function() {
				inquiriesTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Notices Table.
		var noticesTable = $('#wlsm-notices-table');
		wlsmInitializeTable(noticesTable, { action: 'wlsm-fetch-notices' });

		// Notice link.
		var noticeAttachment = $('.wlsm-notice-attachment');
		var noticeUrl = $('.wlsm-notice-url');
		var noticeLinkTo = $('input[name="link_to"]:checked').val();
		if('attachment' === noticeLinkTo) {
			noticeAttachment.show();
		} else if('url' === noticeLinkTo) {
			noticeUrl.show();
		}

		// On change notice link.
		$(document).on('change', 'input[name="link_to"]', function() {
			var noticeLinkTo = this.value;
			var noticeLink = $('.wlsm-notice-link');
			noticeLink.hide();
			if('attachment' === noticeLinkTo) {
				noticeAttachment.fadeIn();
			} else if('url' === noticeLinkTo) {
				noticeUrl.fadeIn();
			}
		});

		// Staff: Save notice.
		var saveNoticeFormId = '#wlsm-save-notice-form';
		var saveNoticeForm = $(saveNoticeFormId);
		var saveNoticeBtn = $('#wlsm-save-notice-btn');
		saveNoticeForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveNoticeBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveNoticeFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveNoticeForm[0].reset();
						$('.wlsm-notice-link').hide();
						$('.wlsm-notice-url').show();
					} else {
						$('.wlsm-attachment-box').load(location.href + " " + '.wlsm-attachment-section', function () {});
						$('.wlsm-section-heading-box').load(location.href + " " + '.wlsm-section-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveNoticeFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveNoticeFormId, saveNoticeBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveNoticeBtn);
			}
		});

		// Staff: Delete notice.
		$(document).on('click', '.wlsm-delete-notice', function(event) {
			var noticeId = $(this).data('notice');
			var nonce = $(this).data('nonce');
			var data = "notice_id=" + noticeId + "&delete-notice-" + noticeId + "=" + nonce + "&action=wlsm-delete-notice";
			var performActions = function() {
				noticesTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Subjects Table.
		var subjectsTable = $('#wlsm-subjects-table');
		wlsmInitializeTable(subjectsTable, { action: 'wlsm-fetch-subjects' });

		// Staff: Save subject.
		var saveSubjectFormId = '#wlsm-save-subject-form';
		var saveSubjectForm = $(saveSubjectFormId);
		var saveSubjectBtn = $('#wlsm-save-subject-btn');
		saveSubjectForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSubjectBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSubjectFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveSubjectForm[0].reset();
						var selectPicker = $('.selectpicker');
						selectPicker.selectpicker('refresh');
					} else {
						$('.wlsm-section-heading-box').load(location.href + " " + '.wlsm-section-heading', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveSubjectFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSubjectFormId, saveSubjectBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSubjectBtn);
			}
		});

		// Staff: Delete subject.
		$(document).on('click', '.wlsm-delete-subject', function(event) {
			var subjectId = $(this).data('subject');
			var nonce = $(this).data('nonce');
			var data = "subject_id=" + subjectId + "&delete-subject-" + subjectId + "=" + nonce + "&action=wlsm-delete-subject";
			var performActions = function() {
				subjectsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Subject Admins Table.
		var subjectAdminsTable = $('#wlsm-subject-admins-table');
		var subject = subjectAdminsTable.data('subject');
		var nonce = subjectAdminsTable.data('nonce');
		if ( subject && nonce ) {
			var data = { action: 'wlsm-fetch-subject-admins', subject: subject };
			data['subject-admins-' + subject] = nonce;
			wlsmInitializeTable(subjectAdminsTable, data);
		}

		// Staff: Delete subject admin.
		$(document).on('click', '.wlsm-delete-subject-admin', function(event) {
			event.preventDefault();
			var adminId = $(this).data('admin');
			var subjectId = $(this).data('subject');
			var nonce = $(this).data('nonce');
			var data = "subject_id=" + subjectId + "&admin_id=" + adminId + "&delete-subject-admin-" + adminId + "=" + nonce + "&action=wlsm-delete-subject-admin";
			var performActions = function() {
				subjectAdminsTable.DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Autocomplete admins.
		var adminSearch = $('#wlsm_admin_search');
		$('#wlsm_admin_search').autocomplete({
			minLength: 1,
			source: function(request, response) {
				$.ajax({
					data: 'action=wlsm-get-keyword-admins&keyword=' + request.term,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						if(res.success) {
							response(res.data);
						} else {
							response([]);
						}
					}
				});
			},
			select: function(event, ui) {
				adminSearch.val('');
				var id = ui.item.ID;
				var label = ui.item.label;
				var adminsInput = $('.wlsm_subject_admins_input');
				if(adminsInput) {
					var adminsToAdd = adminsInput.map(function() { return $(this).val(); }).get();
					if(-1 !== $.inArray(id, adminsToAdd)) {
						return false;
					}
				}
				if(id) {
					$('.wlsm_subject_admins').append('' +
						'<div class="wlsm-subject-admin-item mb-1">' +
							'<input class="wlsm_subject_admins_input" type="hidden" name="admins[]" value="' + id + '">' +
							'<span class="wlsm-badge badge badge-info">' +
								label +
							'</span>' + '&nbsp;<i class="fa fa-times bg-danger text-white wlsm-remove-item"></i>' +
						'</div>' +
					'');
					return false;
				}
				return false;
			}
		});

		// Staff: Assign subject admins.
		var assignSubjectAdminsFormId = '#wlsm-assign-subject-admins-form';
		var assignSubjectAdminsForm = $(assignSubjectAdminsFormId);
		var assignSubjectAdminsBtn = $('#wlsm-assign-subject-admins-btn');
		assignSubjectAdminsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(assignSubjectAdminsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, assignSubjectAdminsFormId);
					toastr.success(response.data.message);
					subjectAdminsTable.DataTable().ajax.reload(null, false);
					$('.wlsm_subject_admins').html('');
				} else {
					wlsmDisplayFormErrors(response, assignSubjectAdminsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, assignSubjectAdminsFormId, assignSubjectAdminsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(assignSubjectAdminsBtn);
			}
		});

		// Staff: Invoices Table.
		function wlsmInitializeInvoicesTable() {
			var data = $('#wlsm-get-invoices-form').serializeObject();
			data['from_table'] = true;
			wlsmInitializeTable($('#wlsm-staff-invoices-table'), data);
		}
		wlsmInitializeInvoicesTable();

		$(document).on('click', '#wlsm-get-invoices-btn', function(event) {
			event.preventDefault();
			var getInvoicesFormId = '#wlsm-get-invoices-form';
			var getInvoicesForm = $(getInvoicesFormId);
			var getInvoicesBtn = $('#wlsm-get-invoices-btn');
			getInvoicesForm.ajaxSubmit({
				beforeSubmit: function(arr, $form, options) {
					return wlsmBeforeSubmit(getInvoicesBtn);
				},
				success: function(response) {
					if(response.success) {
						$('#wlsm-staff-invoices-table').DataTable().clear().destroy();
						wlsmInitializeInvoicesTable();
					} else {
						wlsmDisplayFormErrors(response, getInvoicesFormId);
					}
				},
				error: function(response) {
					wlsmDisplayFormError(response, getInvoicesFormId, getInvoicesBtn);
				},
				complete: function(event, xhr, settings) {
					wlsmComplete(getInvoicesBtn);
				}
			});
		});

		$('#wlsm_invoice_date_issued').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true
		});

		$('#wlsm_invoice_due_date').Zebra_DatePicker({
			format: wlsmdateformat.wlsmDateFormat,
			readonly_element: false,
			show_clear_date: true,
			disable_time_picker: true
		});

		// Staff: Single or bulk students.
		var invoicePayments = $('.wlsm-invoice-payments');
		var invoiceStudent = $('#wlsm_student');
		var invoiceStudentLabel = $('label[for="wlsm_student"]');

		$(document).on('change', 'input[name="invoice_type"]', function(event) {
			var invoiceType = this.value;

			invoiceStudent.selectpicker('destroy');
			if('bulk_invoice' === invoiceType) {
				invoicePayments.hide();
				invoiceStudent.attr('name', 'student[]');
				invoiceStudent.attr('multiple', 'multiple');
				invoiceStudentLabel.html(invoiceStudentLabel.data('bulk-label'));
			} else {
				invoicePayments.fadeIn();
				invoiceStudent.attr('name', 'student');
				invoiceStudent.removeAttr('multiple');
				invoiceStudentLabel.html(invoiceStudentLabel.data('single-label'));
			}
			invoiceStudent.selectpicker('render');
			invoiceStudent.selectpicker('selectAll');
		});

		var collectInvoicePayment = $('.wlsm-collect-invoice-payment');
		$(document).on('change', '#wlsm_collect_invoice_payment', function(event) {
			if($(this).is(':checked')) {
				collectInvoicePayment.fadeIn();
			} else {
				collectInvoicePayment.hide();
			}
		});

		$(document).on('click', '.wlsm-print-invoice-fee-structure', function() {
			var element = $(this);
			var studentsSelected = $("#wlsm_student :selected");
			var length = studentsSelected.length;
			var studentId = studentsSelected.val();
			var title = element.data('message-title');
			var nonce = element.data('nonce');
			var onlyOneStudent = element.data('only-one-student');

			if((1 === length) && studentId && nonce) {
				var data = {};
				data['student_id'] = studentId;
				data['print-invoice-fee-structure'] = nonce;
				data['action'] = 'wlsm-print-invoice-fee-structure';

				$.dialog({
					title: title,
					content: function() {
						var self = this;
						return $.ajax({
							data: data,
							url: ajaxurl,
							type: 'POST',
							success: function(res) {
								self.setContent(res.data.html);
							}
						});
					},
					theme: 'bootstrap',
					columnClass: 'large',
					containerFluid: true,
					backgroundDismiss: true
				});	
			} else {
				toastr.error(onlyOneStudent);
			}
		});

		// Staff: Save invoice.
		var saveInvoiceFormId = '#wlsm-save-invoice-form';
		var saveInvoiceForm = $(saveInvoiceFormId);
		var saveInvoiceBtn = $('#wlsm-save-invoice-btn');
		saveInvoiceForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveInvoiceBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveInvoiceFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reset') && response.data.reset) {
						saveInvoiceForm[0].reset();
						var selectPicker = $('.selectpicker');
						selectPicker.selectpicker('refresh');
					} else {
						$('.wlsm-section-heading-box').load(location.href + " " + ' .wlsm-section-heading', function () {});
						$('.wlsm-fee-invoice-status-box').load(location.href + " " + ' .wlsm-fee-invoice-status', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveInvoiceFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveInvoiceFormId, saveInvoiceBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveInvoiceBtn);
			}
		});

		// Staff: Delete invoice.
		$(document).on('click', '.wlsm-delete-invoice', function(event) {
			event.preventDefault();
			var invoiceId = $(this).data('invoice');
			var nonce = $(this).data('nonce');
			var data = "invoice_id=" + invoiceId + "&delete-invoice-" + invoiceId + "=" + nonce + "&action=wlsm-delete-invoice";
			var performActions = function() {
				$('#wlsm-staff-invoices-table').DataTable().ajax.reload(null, false);
				$('.wlsm-fee-invoices-amount-total-box').load(location.href + " " + '.wlsm-fee-invoices-amount-total', function () {});
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Print invoice.
		$(document).on('click', '.wlsm-print-invoice', function(event) {
			var element = $(this);
			var invoiceId = element.data('invoice');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['invoice_id'] = invoiceId;
			data['print-invoice-' + invoiceId] = nonce;
			data['action'] = 'wlsm-print-invoice';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data.html);
						}
					});
				},
				theme: 'bootstrap',
				useBootstrap: false,
				boxWidth: '900px'
			});
		});

		// Staff: Invoice - Payments table.
		var invoicePaymentsTable = $('#wlsm-invoice-payments-table');
		var invoice = invoicePaymentsTable.data('invoice');
		var nonce = invoicePaymentsTable.data('nonce');
		if ( invoice && nonce ) {
			var data = { action: 'wlsm-fetch-invoice-payments', 'invoice': invoice };
			data['invoice-payments-' + invoice] = nonce;
			wlsmInitializeTable(invoicePaymentsTable, data);
		}

		// Staff: Collect invoice payment.
		var collectInvoicePaymentFormId = '#wlsm-collect-invoice-payment-form';
		var collectInvoicePaymentForm = $(collectInvoicePaymentFormId);

		$(document).on('click', '#wlsm-collect-invoice-payment-btn', function(e) {
			var collectInvoicePaymentBtn = $(this);

			e.preventDefault();
			$.confirm({
				title: collectInvoicePaymentBtn.data('message-title'),
				content: collectInvoicePaymentBtn.data('message-content'),
				type: 'green',
				useBootstrap: false,
				buttons: {
					confirm: {
						text: collectInvoicePaymentBtn.data('submit'),
						btnClass: 'btn-success',
						action: function () {
							collectInvoicePaymentForm.ajaxSubmit({
								beforeSubmit: function(arr, $form, options) {
									return wlsmBeforeSubmit(collectInvoicePaymentBtn);
								},
								success: function(response) {
									if(response.success) {
										wlsmShowSuccessAlert(response.data.message, collectInvoicePaymentFormId);
										toastr.success(response.data.message);
										if(response.data.hasOwnProperty('reload') && response.data.reload) {
											window.location.reload();
										} else {
											if(response.data.hasOwnProperty('reset') && response.data.reset) {
												collectInvoicePaymentForm[0].reset();
											}

											invoicePaymentsTable.DataTable().ajax.reload(null, false);
											$('.wlsm-fee-invoice-status-box').load(location.href + " " + ' .wlsm-fee-invoice-status', function () {});
										}
									} else {
										wlsmDisplayFormErrors(response, collectInvoicePaymentFormId);
									}
								},
								error: function(response) {
									wlsmDisplayFormError(response, collectInvoicePaymentFormId, collectInvoicePaymentBtn);
								},
								complete: function(event, xhr, settings) {
									wlsmComplete(collectInvoicePaymentBtn);
								}
							});
						}
					},
					cancel: {
						text: collectInvoicePaymentBtn.data('cancel'),
						action: function () {
							return;
						}
					}
				}
			});
		});

		// Staff: Delete invoice payment.
		$(document).on('click', '.wlsm-delete-invoice-payment', function(event) {
			event.preventDefault();
			var paymentId = $(this).data('payment');
			var invoiceId = $(this).data('invoice');
			var nonce = $(this).data('nonce');
			var data = "invoice_id=" + invoiceId + "&payment_id=" + paymentId + "&delete-payment-" + paymentId + "=" + nonce + "&action=wlsm-delete-invoice-payment";
			var performActions = function(response) {
				if(response.data.hasOwnProperty('reload') && response.data.reload) {
					window.location.reload();
				} else {
					invoicePaymentsTable.DataTable().ajax.reload(null, false);
					$('.wlsm-fee-invoice-status-box').load(location.href + " " + '.wlsm-fee-invoice-status', function () {});
				}
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Payments Table.
		var paymentsTable = $('#wlsm-payments-table');
		wlsmInitializeTable(paymentsTable, { action: 'wlsm-fetch-payments' });

		// Staff: Get payment note.
		$(document).on('click', '.wlsm-view-payment-note', function(event) {
			var element = $(this);
			var paymentId = element.data('payment');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['payment_id'] = paymentId;
			data['view-payment-note-' + paymentId] = nonce;
			data['action'] = 'wlsm-view-payment-note';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data);
						}
					});
				},
				theme: 'bootstrap',
				columnClass: 'medium',
			});
		});

		// Staff: Delete payment.
		$(document).on('click', '.wlsm-delete-payment', function(event) {
			event.preventDefault();
			var paymentId = $(this).data('payment');
			var nonce = $(this).data('nonce');
			var data = "payment_id=" + paymentId + "&delete-payment-" + paymentId + "=" + nonce + "&action=wlsm-delete-payment";
			var performActions = function(response) {
				paymentsTable.DataTable().ajax.reload(null, false);
				$('.wlsm-stats-payment-table').DataTable().ajax.reload(null, false);
			}
			wlsmDelete(event, this, data, performActions);
		});

		// Staff: Print invoice payment.
		$(document).on('click', '.wlsm-print-invoice-payment', function(event) {
			var element = $(this);
			var paymentId = element.data('invoice-payment');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['payment_id'] = paymentId;
			data['print-invoice-payment-' + paymentId] = nonce;
			data['action'] = 'wlsm-print-invoice-payment';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: ajaxurl,
						type: 'POST',
						success: function(res) {
							self.setContent(res.data.html);
						}
					});
				},
				theme: 'bootstrap',
				useBootstrap: false,
				boxWidth: '900px'
			});
		});

		// Staff: Save school general settings.
		var saveSchoolGeneralSettingsFormId = '#wlsm-save-school-general-settings-form';
		var saveSchoolGeneralSettingsForm = $(saveSchoolGeneralSettingsFormId);
		var saveSchoolGeneralSettingsBtn = $('#wlsm-save-school-general-settings-btn');
		saveSchoolGeneralSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolGeneralSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolGeneralSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else {
						$('.wlsm-school-logo-box').load(location.href + " " + '.wlsm-school-logo-section', function () {});
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolGeneralSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolGeneralSettingsFormId, saveSchoolGeneralSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolGeneralSettingsBtn);
			}
		});

		// Staff: Save school email carrier settings.
		var saveSchoolEmailCarrierSettingsFormId = '#wlsm-save-school-email-carrier-settings-form';
		var saveSchoolEmailCarrierSettingsForm = $(saveSchoolEmailCarrierSettingsFormId);
		var saveSchoolEmailCarrierSettingsBtn = $('#wlsm-save-school-email-carrier-settings-btn');
		saveSchoolEmailCarrierSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolEmailCarrierSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolEmailCarrierSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolEmailCarrierSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolEmailCarrierSettingsFormId, saveSchoolEmailCarrierSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolEmailCarrierSettingsBtn);
			}
		});

		// Trigger TinyMCE on submit.
		function triggerTinyMCE(submitButton) {
			$(submitButton).mousedown(function() {
				tinyMCE.triggerSave();
			});
		}

		triggerTinyMCE('#wlsm-save-school-email-templates-settings-btn');

		// Staff: Save school email templates settings.
		var saveSchoolEmailTemplatesSettingsFormId = '#wlsm-save-school-email-templates-settings-form';
		var saveSchoolEmailTemplatesSettingsForm = $(saveSchoolEmailTemplatesSettingsFormId);
		var saveSchoolEmailTemplatesSettingsBtn = $('#wlsm-save-school-email-templates-settings-btn');
		saveSchoolEmailTemplatesSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolEmailTemplatesSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolEmailTemplatesSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolEmailTemplatesSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolEmailTemplatesSettingsFormId, saveSchoolEmailTemplatesSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolEmailTemplatesSettingsBtn);
			}
		});

		// Staff: Save school payment method settings.
		var saveSchoolPaymentMethodSettingsFormId = '#wlsm-save-school-payment-method-settings-form';
		var saveSchoolPaymentMethodSettingsForm = $(saveSchoolPaymentMethodSettingsFormId);
		var saveSchoolPaymentMethodSettingsBtn = $('#wlsm-save-school-payment-method-settings-btn');
		saveSchoolPaymentMethodSettingsForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(saveSchoolPaymentMethodSettingsBtn);
			},
			success: function(response) {
				if(response.success) {
					wlsmShowSuccessAlert(response.data.message, saveSchoolPaymentMethodSettingsFormId);
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					}
				} else {
					wlsmDisplayFormErrors(response, saveSchoolPaymentMethodSettingsFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, saveSchoolPaymentMethodSettingsFormId, saveSchoolPaymentMethodSettingsBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(saveSchoolPaymentMethodSettingsBtn);
			}
		});

		// Staff: School Dashboard - Active Inquiries table.
		wlsmInitializeDataTable($('.wlsm-stats-active-inquiries-table'), [5, 10]);

		// Staff: School Dashboard - Admission table.
		wlsmInitializeDataTable($('.wlsm-stats-admission-table'), [5, 10, 15]);

		// Staff: School Dashboard - Payment table.
		wlsmInitializeDataTable($('.wlsm-stats-payment-table'), [5, 10, 15], 'wlsm-fetch-stats-payments');

		// Email carriers.
		var wpMail = $('.wlsm_wp_mail');
		var smtp = $('.wlsm_smtp');
		var wlsmEmailCarrier = $('#wlsm_email_carrier').val();
		if('smtp' === wlsmEmailCarrier) {
			smtp.show();
		} else {
			wpMail.show();
		}

		// On change email carrier.
		$(document).on('change', '#wlsm_email_carrier', function() {
			var wlsmEmailCarrier = this.value;
			var emailCarrier = $('.wlsm_email_carrier');
			emailCarrier.hide();
			if('wp_mail' === wlsmEmailCarrier) {
				wpMail.fadeIn();
			} else if('smtp' === wlsmEmailCarrier) {
				smtp.fadeIn();
			}
		});

		// Staff: General Actions.
		$(document).on('change', '#wlsm_class', function() {
			var classId = this.value;
			var nonce = $(this).data('nonce');
			var sections = $('#wlsm_section');
			var fetchStudents = sections.data('fetch-students');
			$('div.text-danger').remove();
			if(classId && nonce) {
				var data = 'action=wlsm-get-class-sections&nonce=' + nonce + '&class_id=' + classId;
				if(sections.data('all-sections')) {
					data += '&all_sections=1';
				}
				$.ajax({
					data: data,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						var options = [];
						res.forEach(function(item) {
							var option = '<option value="' + item.ID + '">' + item.label + '</option>';
							options.push(option);
						});
						sections.html(options);
						sections.selectpicker('refresh');
						if(fetchStudents) {
							sections.trigger('change');
						}
					}
				});
			} else {
				sections.html([]);
				sections.selectpicker('refresh');
				if(fetchStudents) {
					sections.trigger('change');
				}
			}
		});

		$(document).on('change', '.wlsm_section', function() {
			var classId = $('#wlsm_class').val();
			var sectionId = this.value;
			if(!sectionId) {
				sectionId = 0;
			}
			var nonce = $(this).data('nonce');
			var students = $('#wlsm_student');
			$('div.text-danger').remove();
			if(classId && nonce) {
				var data = 'action=wlsm-get-section-students&nonce=' + nonce + '&section_id=' + sectionId + '&class_id=' + classId;
				var onlyActive = $(this).data('only-active');
				if(typeof onlyActive !== 'undefined') {
					data += '&only_active=' + onlyActive;
				}
				$.ajax({
					data: data,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						var options = [];
						res.forEach(function(item) {
							var option = '<option value="' + item.ID + '">' + item.name + ' (' + item.enrollment_number + ')' + '</option>';
							options.push(option);
						});
						students.html(options);
						students.selectpicker('refresh');
					}
				});
			} else {
				students.html([]);
				students.selectpicker('refresh');
			}
		});

		$(document).on('change', '#wlsm_school_class', function() {
			var schoolId = $('#wlsm_school').val();
			var classId = this.value;
			var nonce = $(this).data('nonce');
			var sections = $('#wlsm_school_class_section');
			$('div.text-danger').remove();
			if(schoolId && classId && nonce) {
				var data = 'action=wlsm-get-school-class-sections&nonce=' + nonce + '&school_id=' + schoolId + '&class_id=' + classId;
				$.ajax({
					data: data,
					url: ajaxurl,
					type: 'POST',
					success: function(res) {
						var options = [];
						res.forEach(function(item) {
							var option = '<option value="' + item.ID + '">' + item.label + '</option>';
							options.push(option);
						});
						sections.html(options);
						sections.selectpicker('refresh');
					}
				});
			} else {
				sections.html([]);
				sections.selectpicker('refresh');
			}
		});

		// Print.
		function wlsmPrint(targetId, title, styleSheets) {
			var target = $(targetId).html();

			var frame = $('<iframe />');
			frame[0].name = 'frame';
			frame.css({ 'position': 'absolute', 'top': '-1000000px' });

			var that = frame.appendTo('body');
			var frameDoc = frame[0].contentWindow ? frame[0].contentWindow : frame[0].contentDocument.document ? frame[0].contentDocument.document : frame[0].contentDocument;
			frameDoc.document.open();

			// Create a new HTML document.
			frameDoc.document.write('<html><head>' + title);
			frameDoc.document.write('</head><body>');

			// Append the external CSS file.
			styleSheets.forEach(function(styleSheet, index) {
				$(that).contents().find('head').append('<link href="' + styleSheet + '" rel="stylesheet" type="text/css" referrerpolicy="origin" />');
			});

			// Append the target.
			frameDoc.document.write(target);
			frameDoc.document.write('</body></html>');
			frameDoc.document.close();

			setTimeout(function () {
				window.frames["frame"].focus();
				window.frames["frame"].print();
				frame.remove();
			}, 1000);
		}

		// Print ID card.
		$(document).on('click', '#wlsm-print-id-card-btn', function() {
			var targetId = '#wlsm-print-id-card';
			var title = $(this).data('title');
			if(title) {
				title = '<title>' + title  + '</title>';
			}
			var styleSheets = $(this).data('styles');

			wlsmPrint(targetId, title, styleSheets);
		});

		// Print ID cards.
		$(document).on('click', '#wlsm-print-id-cards-btn', function() {
			var targetId = '#wlsm-print-id-cards';
			var title = $(this).data('title');
			if(title) {
				title = '<title>' + title  + '</title>';
			}
			var styleSheets = $(this).data('styles');

			wlsmPrint(targetId, title, styleSheets);
		});

		// Print fee structure.
		$(document).on('click', '#wlsm-print-fee-structure-btn', function() {
			var targetId = '#wlsm-print-fee-structure';
			var title = $(this).data('title');
			if(title) {
				title = '<title>' + title  + '</title>';
			}
			var styleSheets = $(this).data('styles');

			wlsmPrint(targetId, title, styleSheets);
		});

		// Print invoice.
		$(document).on('click', '#wlsm-print-invoice-btn', function() {
			var targetId = '#wlsm-print-invoice';
			var title = $(this).data('title');
			if(title) {
				title = '<title>' + title  + '</title>';
			}
			var styleSheets = $(this).data('styles');

			wlsmPrint(targetId, title, styleSheets);
		});

		// Print payment.
		$(document).on('click', '#wlsm-print-invoice-payment-btn', function() {
			var targetId = '#wlsm-print-invoice-payment';
			var title = $(this).data('title');
			if(title) {
				title = '<title>' + title  + '</title>';
			}
			var styleSheets = $(this).data('styles');

			wlsmPrint(targetId, title, styleSheets);
		});
	});
})(jQuery);
