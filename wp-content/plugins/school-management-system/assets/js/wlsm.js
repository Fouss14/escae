(function($) {
	'use strict';
	$(document).ready(function() {
		// Loading icon variables.
		var loaderContainer = $('<span/>', {
			'class': 'wlsm-loader wlsm-ml-2'
		});
		var loader = $('<img/>', {
			'src': wlsmadminurl.wlsmAdminURL + 'images/spinner.gif',
			'class': 'wlsm-loader-image wlsm-mb-1'
		});

		// Function: Before Submit.
		function wlsmBeforeSubmit(button) {
			$('div.wlsm-text-danger').remove();
			$(".wlsm-is-invalid").removeClass("wlsm-is-invalid");
			$('.wlsm-alert-dismissible').remove();
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
					$(input).removeClass('wlsm-is-invalid');
					if(response.data[input.name]) {
						var errorSpan = '<div class="wlsm-text-danger wlsm-mt-1">' + response.data[input.name] + '</div>';
						$(input).addClass('wlsm-is-invalid');
						$(errorSpan).insertAfter(input);
					}
				});
			} else {
				var errorSpan = '<div class="wlsm-text-danger wlsm-mt-3">' + response.data + '<hr></div>';
				$(errorSpan).insertBefore(formId);
				toastr.error(response.data);
			}
		}

		// Function: Display Form Error.
		function wlsmDisplayFormError(response, formId, button) {
			button.prop('disabled', false);
			var errorSpan = '<div class="text-danger wlsm-mt-2"><span class="wlsm-font-bold">' + response.status + '</span>: ' + response.statusText + '<hr></div>';
			$(errorSpan).insertBefore(formId);
			toastr.error(response.data);
		}

		// Function: Complete.
		function wlsmComplete(button) {
			button.prop('disabled', false);
			loaderContainer.remove();
		}

		// Get students with pending invoices.
		var getPendingInvoicesStudentsSection = '#wlsm-get-pending-invoices-students-section';
		var getPendingInvoicesStudentsBtn = $('#wlsm-get-pending-invoices-students-btn');

		$(document).on('click', '#wlsm-get-pending-invoices-students-btn', function(e) {
			var studentsWithPendingInvoices = $('.wlsm-students-with-pending-invoices');

			var schoolId = $('#wlsm_school').val();
			var sessionId = $('#wlsm_session').val();
			var classId = $('#wlsm_school_class').val();
			var studentName = $('#wlsm_student_name').val();
			var nonce = $(this).data('nonce');

			var data = {};
			data['school_id'] = schoolId;
			data['session_id'] = sessionId;
			data['class_id'] = classId;
			data['student_name'] = studentName;
			data['nonce'] = nonce;
			data['action'] = 'wlsm-p-get-students-with-pending-invoices';

			if(nonce) {
				$.ajax({
					data: data,
					url: wlsmajaxurl.url,
					type: 'POST',
					beforeSend: function() {
						return wlsmBeforeSubmit(getPendingInvoicesStudentsBtn);
					},
					success: function(response) {
						if(response.success) {
							studentsWithPendingInvoices.html(response.data.html);
						} else {
							wlsmDisplayFormErrors(response, getPendingInvoicesStudentsSection);
						}
					},
					error: function(response) {
						wlsmDisplayFormError(response, getPendingInvoicesStudentsSection, getPendingInvoicesStudentsBtn);
					},
					complete: function(event, xhr, settings) {
						wlsmComplete(getPendingInvoicesStudentsBtn);
					},
				});
			} else {
				studentsWithPendingInvoices.html('');
			}
		});

		// Get student pending fee invoices.
		$(document).on('click', '.wlsm-view-student-pending-invoices', function(e) {
			e.preventDefault();			
			var viewStudentInvoicesBtn = $(this);

			var studentPendingInvoices = $('.wlsm-student-pending-invoices');

			var studentId = $(this).data('student');
			var nonce = $(this).data('nonce');

			var data = {};
			data['student_id'] = studentId;
			data['nonce']      = nonce;
			data['action']     = 'wlsm-p-get-student-pending-invoices';

			if(nonce) {
				$.ajax({
					data: data,
					url: wlsmajaxurl.url,
					type: 'POST',
					beforeSend: function() {
						return wlsmBeforeSubmit(viewStudentInvoicesBtn);
					},
					success: function(response) {
						if(response.success) {
							studentPendingInvoices.html(response.data.html);
							studentPendingInvoices.focus();
							$(window).scrollTop(studentPendingInvoices.offset().top - ($(window).height() - studentPendingInvoices.outerHeight(true)) / 2);
						}
					},
					complete: function(event, xhr, settings) {
						wlsmComplete(viewStudentInvoicesBtn);
					},
				});
			} else {
				studentPendingInvoices.html('');
			}
		});

		// Get student pending fee invoice.
		$(document).on('click', '.wlsm-view-student-pending-invoice', function(e) {
			e.preventDefault();
			var viewStudentInvoiceBtn = $(this);

			var studentPendingInvoice = $('.wlsm-student-pending-invoice');

			var invoiceId = $(this).data('invoice');
			var nonce = $(this).data('nonce');

			var data = {};
			data['invoice_id'] = invoiceId;
			data['nonce'] = nonce;
			data['action'] = 'wlsm-p-get-student-pending-invoice';

			if(nonce) {
				$.ajax({
					data: data,
					url: wlsmajaxurl.url,
					type: 'POST',
					beforeSend: function() {
						return wlsmBeforeSubmit(viewStudentInvoiceBtn);
					},
					success: function(response) {
						if(response.success) {
							studentPendingInvoice.html(response.data.html);
							$(window).scrollTop(studentPendingInvoice.offset().top - ($(window).height() - studentPendingInvoice.outerHeight(true)) / 2);
						}
					},
					complete: function(event, xhr, settings) {
						wlsmComplete(viewStudentInvoiceBtn);
					},
				});
			} else {
				studentPendingInvoice.html('');
			}
		});

		$(document).on('click', '#wlsm-pay-invoice-amount-btn', function(e) {
			var payInvoiceAmountSectionId = '#wlsm-pay-invoice-amount-section';
			var payInvoiceAmountBtn = $(this);

			var payInvoiceAmount = $('.wlsm-pay-invoice-amount');

			var invoiceId = $('#wlsm_invoice_id').val();
			var paymentAmount = $('#wlsm_payment_amount').val();
			var paymentMethod = $('input[name="payment_method"]:checked').val();
			var nonce = $(this).data('nonce');

			var data = {};
			data['invoice_id'] = invoiceId;
			data['payment_amount'] = paymentAmount;
			data['payment_method'] = paymentMethod;
			data['nonce'] = nonce;
			data['action'] = 'wlsm-p-pay-invoice-amount';

			if(nonce) {
				$.ajax({
					data: data,
					url: wlsmajaxurl.url,
					type: 'POST',
					beforeSend: function() {
						return wlsmBeforeSubmit(payInvoiceAmountBtn);
					},
					success: function(response) {
						if(response.success) {
							var data = JSON.parse(response.data.json);
							var html = response.data.html;
							if(!data || !html) {
								return;
							}

							payInvoiceAmount.html(html);

							if (data.payment_method == 'stripe') {
								// Stripe Options.
								var options = {
									'key': data.stripe_key,
									'image': data.school_logo_url,
									'token': function(token) {
										var stripeData = {
											'action': data.action,
											'security': data.security,
											'invoice_id': data.invoice_id,
											'invoice_number': data.invoice_number,
											'amount': data.amount_in_cents,
											'stripeToken': token.id,
											'stripeEmail': token.email
										}

										// Send Stripe data to server.
										$.ajax({
											type: 'POST',
											url: wlsmajaxurl.url,
											data: stripeData,
											success: function (response) {
												if (response.success) {
													toastr.success(response.data.message);
													location.reload();
												} else {
													toastr.error(response.data);
												}
											},
											error: function (response) {
												toastr.error(response.statusText);
											},
											dataType: 'json'
										});
									}
								};

								// Initialize Stripe.
						 		var stripe = StripeCheckout.configure(options);

						 		// Open Stripe payment window.
								$(document).on('click', '#wlsm-stripe-btn', function(e) {
									stripe.open({
										name: data.name,
										description: data.description,
										currency: data.currency,
										amount: parseFloat(data.amount_in_cents)
									});
									e.preventDefault();
								});

								// Close stripe checkout on page navigation.
								$(window).on('popstate', function () {
									stripe.close();
								});

                   		 	}
						} else {
							wlsmDisplayFormErrors(response, payInvoiceAmountSectionId);
						}
					},
					error: function(response) {
						wlsmDisplayFormError(response, payInvoiceAmountSectionId, payInvoiceAmountBtn);
					},
					complete: function(event, xhr, settings) {
						wlsmComplete(payInvoiceAmountBtn);
					},
				});
			} else {
				payInvoiceAmount.html('');
			}
		});

		// Submit inquiry.
		var submitInquiryFormId = '#wlsm-submit-inquiry-form';
		var submitInquiryForm = $(submitInquiryFormId);
		var submitInquiryBtn = $('#wlsm-submit-inquiry-btn');
		submitInquiryForm.ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				return wlsmBeforeSubmit(submitInquiryBtn);
			},
			success: function(response) {
				if(response.success) {
					toastr.success(response.data.message);
					if(response.data.hasOwnProperty('reload') && response.data.reload) {
						window.location.reload();
					} else {
						submitInquiryForm[0].reset();
					}
				} else {
					wlsmDisplayFormErrors(response, submitInquiryFormId);
				}
			},
			error: function(response) {
				wlsmDisplayFormError(response, submitInquiryFormId, submitInquiryBtn);
			},
			complete: function(event, xhr, settings) {
				wlsmComplete(submitInquiryBtn);
			}
		});

		// General Actions.

		// Get school classes.
		$(document).on('change', '#wlsm_school', function() {
			var schoolId = this.value;
			var sessionId = $('#wlsm_session').val();
			var nonce = $(this).data('nonce');
			var classes = $('#wlsm_school_class');

			var firstOptionLabel = classes.find('option[value=""]').first().html();
			firstOptionLabel = '<option value="">' + firstOptionLabel + '</option>';

			$('div.wlsm-text-danger').remove();
			if(schoolId && nonce) {
				var data = 'action=wlsm-p-get-school-classes&nonce=' + nonce + '&school_id=' + schoolId;
				if(sessionId) {
					data += '&session_id=' + sessionId
				}
				$.ajax({
					data: data,
					url: wlsmajaxurl.url,
					type: 'POST',
					success: function(res) {
						var options = [firstOptionLabel];
						res.forEach(function(item) {
							var option = '<option value="' + item.ID + '">' + item.label + '</option>';
							options.push(option);
						});
						classes.html(options);
					}
				});
			} else {
				classes.html([firstOptionLabel]);
			}
		});

		// Add classes to login form button.
		$('#wlsm-login-form input[type="submit"]').attr('class', 'button btn btn-primary')
		$('#wlsm-login-via-widget-form input[type="submit"]').attr('class', 'button btn btn-primary')

		// Student: Print invoice payment.
		$(document).on('click', '.wlsm-st-print-invoice-payment', function(event) {
			var element = $(this);
			var paymentId = element.data('invoice-payment');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['payment_id'] = paymentId;
			data['st-print-invoice-payment-' + paymentId] = nonce;
			data['action'] = 'wlsm-p-st-print-invoice-payment';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: wlsmajaxurl.url,
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

		// Student: Print ID card.
		$(document).on('click', '.wlsm-st-print-id-card', function(event) {
			var element = $(this);
			var userId = element.data('id-card');
			var title = element.data('message-title');
			var nonce = element.data('nonce');

			var data = {};
			data['st-print-id-card-' + userId] = nonce;
			data['action'] = 'wlsm-p-st-print-id-card';

			$.dialog({
				title: title,
				content: function() {
					var self = this;
					return $.ajax({
						data: data,
						url: wlsmajaxurl.url,
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


var modal = document.getElementById("notice_modal");

// When the user clicks on <span> (x), close the modal
// span.onclick = function() {
//   modal.style.display = "none";
// }

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
if( document.getElementById("wlsm_notice") !== null ) {
	document.getElementById("wlsm_notice").addEventListener("click", function (e) {
	  if (e.target && e.target.classList.contains("wlsm_notice_link")) {
		const selectNotice = e.target.dataset.id;
		modal.style.display = "block";
	  }

	});
}
