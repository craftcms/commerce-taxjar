if (typeof Craft.Commerce === typeof undefined) {
    Craft.Commerce = {};
}

(function($) {
    $('.taxjar-action:not([href=refund])').click(function(event) {
        event.preventDefault();
        var action = $(this).attr('href');
        const message = "Are you sure you want to delete this TaxJar order transaction and all associated refund transactions?";

        if (action === 'delete' && !window.confirm(message)) {
            return false;
        }

        $('.order-flex.taxjar').append('<div class="spacer"></div><div class="spinner"></div>');
        Craft.postActionRequest('commerce-taxjar/orders/' + action, {"id": window.orderEdit.orderId}, function(response) {
            console.log(response);
            if (response.success) {
                Craft.cp.displayNotice(Craft.t('commerce', 'Transaction ' + response.action + ' in TaxJar'));
            } else {
                Craft.cp.displayError(Craft.t('commerce', response.error));
            }
            $('.order-flex.taxjar').find('.spacer, .spinner').remove();
        });
    });
})(jQuery);

(function($) {
    var classes = [
        '#order-edit-btn',
        '.order-block .my-1 + div .btn-link',
        '.order-block .btn-link--danger',
        '.order-total-summary .btn-link',
        '.order-flex.justify-between.align-center.pb .btn-link'
    ];
    $(classes.join(',')).click(function(event) {
        $('.order-flex.taxjar').remove();
    });
})(jQuery);

if (typeof Craft.Commerce === typeof undefined) {
    Craft.Commerce = {};
}

Craft.Commerce.TaxJarRefund = Garnish.Base.extend(
    {
        orderId: null,
        locale: null,
        currency: null,

        $status: null,
        $completion: null,
        statusUpdateModal: null,

        init: function(settings) {
            this.setSettings(settings);
            this.orderId = this.settings.orderId;
            this.locale = this.settings.locale;
            this.currency = this.settings.currency;

            this.$taxjarMenu = $('.btngroup.taxjar .btn.menubtn');
            this.addListener(this.$taxjarMenu, 'click', 'listenRefund');

            // if (Object.keys(this.paymentForm.errors).length > 0) {
            //     this.openPaymentModal();
            // }
        },
        openRefundModal: function() {
            if (!this.refundModal) {
                this.refundModal = new Craft.Commerce.TaxJarRefundModal({
                    orderId: this.orderId,
                    locale: this.locale,
                    currency: this.currency
                });
            } else {
                this.refundModal.show();
            }
        },
        listenRefund: function(ev) {
            if (!this.$createRefund) {
                this.$createRefund = $('.taxjar-action[href=refund]');
                this.addListener(this.$createRefund, 'click', 'createRefund');
            }
        },
        createRefund: function(ev) {
            ev.preventDefault();
            this.openRefundModal();
        }
    },
    {
        defaults: {
            orderId: null,
            locale: null,
            currency: null
        }
    });

if (typeof Craft.Commerce === typeof undefined) {
    Craft.Commerce = {};
}

/**
 * Class Craft.Commerce.TaxJarRefundModal
 */
Craft.Commerce.TaxJarRefundModal = Garnish.Modal.extend(
    {
        orderId: null,
        locale: null,
        currency: null,

        $container: null,
        $body: null,

        init: function(settings) {
            this.orderId = settings.orderId;
            this.locale = settings.locale;
            this.currency = settings.currency;

            this.$container = $('<div id="taxjarrefundmodal" class="modal fitted loading"/>').appendTo(Garnish.$bod);

            this.base(this.$container, $.extend({
                resizable: false
            }, settings));

            var data = {
                orderId: this.orderId
            };

            Craft.postActionRequest('commerce-taxjar/orders/get-refund-modal', data, $.proxy(function(response, textStatus) {
                this.$container.removeClass('loading');

                if (textStatus === 'success') {
                    if (response.success) {
                        var $this = this;
                        this.$container.append(response.modalHtml);
                        Craft.appendHeadHtml(response.headHtml);
                        Craft.appendFootHtml(response.footHtml);

                        var $buttons = $('.buttons', this.$container),
                            $cancelBtn = $('<div class="btn">' + Craft.t('app', 'Cancel') + '</div>').prependTo($buttons);

                        this.addListener($cancelBtn, 'click', 'cancelRefund');
                        this.addListener($('input[id^=qty-], input[id^=deduction-], input#shipping-order, input#tax-order'), 'change keyup', 'updateAmounts');

                        Craft.initUiElements(this.$container);

                        setTimeout(function() {
                            $this.updateSizeAndPosition();
                        }, 200);
                    }
                    else {
                        var error = Craft.t('commerce', 'An unknown error occurred.');

                        if (response.error) {
                            error = response.error;
                        }

                        this.$container.append('<div class="body">' + error + '</div>');
                    }
                }
            }, this));
        },
        cancelRefund: function() {
            this.hide();
        },
        updateAmounts: function(ev) {
            var formatter = new Intl.NumberFormat(this.language, { style: 'currency', currency: this.currency }),
                sum = 0;

            if (['shipping-order', 'tax-order', 'deduction-order'].indexOf(ev.target.id) === -1) {
                var item = $(ev.target),
                    id = item.data('id'),
                    qty = item.val();

                var refundPrice = item.data('saleprice') * qty,
                    refundDiscount = item.data('unitdiscount') * qty,
                    refundTax = item.data('unittax') * qty,
                    refundSubtotal = refundPrice + refundDiscount + refundTax;

                $('#price-' + id).text(formatter.format(refundPrice));
                $('#discount-' + id).text(formatter.format(refundDiscount));
                $('#tax-' + id).text(formatter.format(refundTax));
                $('#subtotal-' + id).data('subtotal', refundSubtotal).text(formatter.format(refundSubtotal));
            } else {
                var tar = $(ev.target),
                    val = Number(tar.val()),
                    max = Number(tar.attr('max')),
                    min = Number(tar.attr('min'));

                if (val > max || (val == max && tar.val().length > tar.attr('max').length)) {
                    tar.val(tar.attr('max'));
                }
                if (val < min || (val == min && tar.val().length > tar.attr('min').length)) {
                    tar.val(tar.attr('min'));
                }
            }

            $('[id^=subtotal]').each(function() {
                sum += Number($(this).data('subtotal'));
            });
            if ($('#shipping-order').length) {
                sum += Number($('#shipping-order').val());
            }
            if ($('#tax-order').length) {
                sum += Number($('#tax-order').val());
            }
            
            var deduction = $('#deduction-order');
            deduction.attr('max', sum);
            if (deduction.val().length && Number(deduction.val()) > sum) {
                deduction.val(sum);
            }
            sum -= Number(deduction.val());

            $('#form-taxjar-refund input.btn.submit').val(Craft.t('app', 'Refund') + ' ' + formatter.format(sum));
        }
    },
    {});
