(function ($) {
  $(document).ready(function () {
    // Accordion ARIA only, remove chevron icon logic
    $('.jct-accordion-header').each(function(i) {
      // Set ARIA attributes only
      $(this).attr({
        'aria-expanded': 'false',
        'aria-controls': 'jct-accordion-body-'+i,
        'id': 'jct-accordion-header-'+i,
        'tabindex': 0,
        'role': 'button'
      });
      $(this).next('.jct-accordion-body').attr({
        'id': 'jct-accordion-body-'+i,
        'aria-labelledby': 'jct-accordion-header-'+i,
        'role': 'region',
        'tabindex': -1
      });
    });

    // Open Site Keys accordion by default if nothing is set
    var lastOpen = localStorage.getItem('jctAccordionOpen');
    if (!lastOpen) {
      var $firstHeader = $('.jct-accordion-header').first();
      $firstHeader.addClass('active').attr('aria-expanded', 'true');
      $firstHeader.next('.jct-accordion-body').show();
    } else {
      var $header = $('#' + lastOpen);
      $header.addClass('active').attr('aria-expanded', 'true');
      $header.next('.jct-accordion-body').show();
    }

    // Accordion toggle with smooth animation and accessibility
    let accordionDebounce = false;
    $('.jct-accordion-header').on('click keydown', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
      if (accordionDebounce) return;
      accordionDebounce = true;
      setTimeout(function() { accordionDebounce = false; }, 250);
      const $header = $(this);
      const $body = $header.next('.jct-accordion-body');
      const isOpen = $header.hasClass('active');
      // Toggle current item
      $('.jct-accordion-header').removeClass('active').attr('aria-expanded', 'false');
      $('.jct-accordion-body').slideUp(220);
      if (!isOpen) {
        $header.addClass('active').attr('aria-expanded', 'true');
        $body.slideDown(220, function() {
          $body.attr('tabindex', -1).focus();
        });
        localStorage.setItem('jctAccordionOpen', $header.attr('id'));
      } else {
        localStorage.removeItem('jctAccordionOpen');
      }
    });

    // Settings saved toast
    if ($('#jct-settings-saved-toast').length) {
      $('#jct-settings-saved-toast').fadeIn(200).delay(2000).fadeOut(400);
    }

    // Render Turnstile widget for API test (settings page)
    if ($('#jct-turnstile-test-widget').length && window.turnstile && window.jctTestSiteKey) {
      window.jctTurnstileWidgetId = turnstile.render('jct-turnstile-test-widget', {
        sitekey: window.jctTestSiteKey,
        theme: window.jctTestTheme || 'auto',
        callback: function(token) {
          // Hide widget and show success message
          $('#jct-turnstile-test-widget').hide();
          $('#jct-turnstile-test-success').fadeIn(200);
        },
        'expired-callback': function() {
          $('#jct-turnstile-test-success').fadeOut(100);
        },
        'error-callback': function() {
          $('#jct-turnstile-test-success').fadeOut(100);
        }
      });
    }

    // Test Turnstile keys button
    $('#jct-test-turnstile').on('click', function(e) {
      e.preventDefault();
      var $btn = $(this);
      $btn.prop('disabled', true).text('Testing...');
      var siteKey = $('#site_key').val();
      var secretKey = $('#secret_key').val();
      $.post(ajaxurl, {
        action: 'jct_test_turnstile',
        site_key: siteKey,
        secret_key: secretKey,
        _ajax_nonce: window.jctTestNonce
      }, function(resp) {
        alert(resp.data ? resp.data : 'Test failed.');
        $btn.prop('disabled', false).text('Test Turnstile Keys');
      }).fail(function(jqXHR, textStatus) {
        alert('AJAX error: ' + textStatus);
        $btn.prop('disabled', false).text('Test Turnstile Keys');
      });
    });
  });
})(jQuery);
