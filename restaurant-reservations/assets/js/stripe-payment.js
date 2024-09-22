var key = null;

if ( rtb_stripe_payment.stripe_mode == 'test' ) {
  key = rtb_stripe_payment.test_publishable_key;
}
else {
  key = rtb_stripe_payment.live_publishable_key;
}

if( rtb_stripe_payment.stripe_sca ) {
  var _stripe = Stripe(key);
}
else {
  Stripe.setPublishableKey(key);
}

function stripeResponseHandler(status, response) {
    if (response.error) {
		// show errors returned by Stripe
        jQuery(".payment-errors").html(response.error.message);
		// re-enable the submit button
		jQuery('#stripe-submit').attr("disabled", false);
    }
    else {
        var form$ = jQuery("#stripe-payment-form");
        // token contains id, last4, and card type
        var token = response['id'];
        // insert the token into the form so it gets submitted to the server
        form$.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
        // and submit
        form$.get(0).submit();
    }
}

function error_handler(msg = '') {
  jQuery('.payment-errors').html(msg);
  enable_payment_form();
}

function disable_payment_form() {
  jQuery('.payment-errors').html('');
  rtb_stripe_payment.stripe_sca && jQuery('.stripe-payment-help-text').slideDown();
  jQuery('#stripe-submit').prop('disabled', true);
}
function enable_payment_form() {
  rtb_stripe_payment.stripe_sca && jQuery('.stripe-payment-help-text').slideUp();
  jQuery('#stripe-submit').prop('disabled', false);
}

jQuery(document).ready(function($) {

  // setup payment element
  if ( rtb_stripe_payment.stripe_sca ) {
    
    var options = {
      mode: 'payment',
      theme: 'flat',
      amount: parseInt( rtb_stripe_payment.amount ),
      currency: rtb_stripe_payment.currency.toLowerCase(),
      captureMethod: rtb_stripe_payment.hold ? 'manual' : 'automatic',
    };

    var elements = _stripe.elements( options );
    var paymentElement = elements.create('payment');
    paymentElement.mount('#cardElement');
    
    paymentElement.on('change', function(ev) {
      if (ev.complete) {
        // enable payment button
        enable_payment_form();
      }
      else {
        if (ev.error) {
          error_handler(ev.error.message);
        }
      }
    });
  }

  $('#stripe-payment-form .single-masked').on('keyup', function (ev) {
    let value = $(this).val();
    
    if ( /\//.test(value) ) {
      value = value.replace( /(\/)+/, '/' );
    }

    if(value.length > 2 && !/\//.test(value)) {
      value = value.split('');
      value.splice(2, 0, '/');
      value = value.join('');
    }

    $(this).val(value);
  });

  var form = document.getElementById( 'stripe-payment-form' );
  form.addEventListener( 'submit', async (event) => {

    event.preventDefault();
    
		// disable the submit button to prevent repeated clicks
		disable_payment_form();

    // send the card details to Stripe
    if ( rtb_stripe_payment.stripe_sca ) {

      const { error: submitError } = await elements.submit();

      if ( submitError ) {

        error_handler( submitError );
        console.log( 'RTB-Stripe error: ', submitError );

        return;
      }

      var booking_id = $('#stripe-payment-form').data( 'booking_id' );
      // Call your backend to create the Checkout Session
      var params = {
        'nonce': rtb_stripe_payment.nonce,
        'action': 'rtb_stripe_get_intent',
        'booking_id': booking_id,
        'return_url': window.location.href,
      };

      $.post(ajaxurl, params, function(result) {
        result = JSON.parse(result);
        if( result.success ) {

          var clientSecret = result.clientSecret;

          _stripe.confirmPayment({ 
            elements,
            clientSecret,
            confirmParams: {
              return_url: result.redirect_url,
            },
            redirect: 'if_required',
            params: {
              billing_details: {
                name: result.name,
                email: result.email,
              }
            }
          }).then(function(result) {
            params = {
              nonce: rtb_stripe_payment.nonce,
              action: 'rtb_stripe_pmt_succeed', 
              booking_id: booking_id
            };

            if (result.error) {
              // Show error to your customer (e.g., insufficient funds)
              params['success'] = false;
              params['message'] = result.error.message;
              error_handler(result.error.message);
            }
            else {
              var pi = result.paymentIntent;

              // The payment has been processed!
              if (pi.status === 'succeeded' || pi.status === 'requires_capture') {
                params['success'] = true;
                params['payment_amount'] = pi.amount;
                params['payment_id'] = pi.id;
                // params['payment_intent'] = pi;
              }
              else {
                params['success'] = false;
                params['message'] = 'Unknown error';
              }
            }

            $.post(ajaxurl, params, function (result) {
              result = JSON.parse(result);

              if(true == result.success) {
                var url = new URL(window.location.pathname, window.location.origin);

                for(const [key, value] of Object.entries(result.urlParams)) {
                  url.searchParams.append(key, value);
                }

                window.location = url.href;
              }
              else {
                error_handler(result.message);
                console.log('RTB-Stripe error: ', result.message);
              }
            });
          });
        }
        else {
          error_handler(result.message);
          console.log('RTB-Stripe error: ', result.message);
        }
      });
    }
    else {

      let exp_month, exp_year;

      let single_field = $('#stripe-payment-form .single-masked').length;
      if(single_field) {
        let data = $('#stripe-payment-form .single-masked').val().split('/');
        exp_month = data[0];
        exp_year = data[1];
      }
      else {
        exp_month = $('input[data-stripe="exp_month"]').val();
        exp_year = $('input[data-stripe="exp_year"]').val();
      }

      Stripe.createToken({
        number: $('input[data-stripe="card_number"]').val(),
        cvc: $('input[data-stripe="card_cvc"]').val(),
        exp_month: exp_month,
        exp_year: exp_year,
        currency: $('input[data-stripe="currency"]').val()
      }, stripeResponseHandler);
    }

		// prevent the form from submitting with the default action
		return false;
	});
});