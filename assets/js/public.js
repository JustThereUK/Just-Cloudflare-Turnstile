(function ($) {
  const JCT = {
    widgets: [],
    config: window.JCTConfig || {},
    observer: null,
    retryLimit: 20,

    sizeMap: {
      small: 'compact',
      medium: 'normal',
      large: 'normal',
      standard: 'normal',
      normal: 'normal'
    },

    // Render all unrendered .cf-turnstile widgets
    renderWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      var found = $('.cf-turnstile').length;
      if (found) {
        console.log('[JCT] Found ' + found + ' .cf-turnstile widgets on page ');
      } else {
        console.log('[JCT] No .cf-turnstile widgets found on page');
      }
      $('.cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
      // Contact Form 7: re-enable submit if needed
      if (typeof wpcf7 !== 'undefined') {
        document.querySelectorAll('.wpcf7 form').forEach(function(form) {
          form.addEventListener('wpcf7submit', function() {
            form.querySelectorAll('button[type=submit], input[type=submit]').forEach(function(btn) {
              btn.disabled = false;
              btn.classList.remove('jct-disabled');
            });
          });
        });
      }
    },

    renderElementorWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.elementor-form .cf-turnstile, .elementor-popup-modal .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    renderGravityFormsWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.gform_wrapper .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    renderFormidableFormsWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.frm_form_fields .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    renderForminatorWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.forminator-custom-form .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    renderJetpackFormsWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.contact-form .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    renderKadenceFormsWidgets: function () {
      if (typeof turnstile === 'undefined') return;
      $('.kb-form .cf-turnstile').each(function () {
        const el = this;
        if (el.dataset.rendered) return;
        $(el).find('.jct-spinner').remove();
        const params = {
          sitekey: el.getAttribute('data-sitekey'),
          theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
          size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
          appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
          callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
          'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
          'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
        };
        turnstile.render(el, params);
        el.dataset.rendered = 'true';
      });
    },

    init: function retry(attempt = 0) {
      if (typeof turnstile === 'undefined') {
        if (attempt >= JCT.retryLimit) {
          if (window.console && window.console.error) {
            console.error('Cloudflare Turnstile failed to load after ' + JCT.retryLimit + ' attempts.');
          }
          return;
        }
        $('.cf-turnstile').each(function () {
          if (!$(this).find('.jct-spinner').length) {
            $(this).html('<div class="jct-spinner" aria-label="Loading Turnstile..." role="status"></div>');
          }
        });
        return setTimeout(() => {
          try { JCT.init(attempt + 1); } catch (e) { if (window.console) console.error(e); }
        }, 300);
      }
      JCT.renderWidgets();
    },

    setResponseInput: function (el, token) {
      const $form = $(el).closest('form');
      let $input = $form.find('input[name="cf-turnstile-response"]');
      if (!$input.length) {
        $input = $('<input type="hidden" name="cf-turnstile-response" />').appendTo($form);
      }
      $input.val(token || '');
    },

    disableSubmit: function (el) {
      const $form = $(el).closest('form');
      $form.find('button[type=submit], input[type=submit]').prop('disabled', true).addClass('jct-disabled');
    },

    enableSubmit: function (el) {
      const $form = $(el).closest('form');
      $form.find('button[type=submit], input[type=submit]').prop('disabled', false).removeClass('jct-disabled');
    },

    observeDOM: function () {
      if (JCT.observer) return;
      let debounceTimer = null;
      JCT.observer = new MutationObserver(function (mutations) {
        let needsInit = false;
        mutations.forEach(function (mutation) {
          mutation.addedNodes && $(mutation.addedNodes).find('.cf-turnstile').length && (needsInit = true);
        });
        if (needsInit) {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => {
            try { JCT.init(); } catch (e) { if (window.console) console.error(e); }
          }, 100);
        }
      });
      JCT.observer.observe(document.body, { childList: true, subtree: true });
    },

    elementorIntegration: function () {
      $(document).on('elementor-pro/forms/ajax:beforeSend', function (e, jqXHR, data) {
        const $form = $(e.target).closest('form');
        const token = $form.find('input[name="cf-turnstile-response"]').val() || '';
        if (token) {
          data.data['cf-turnstile-response'] = token;
        }
      });
      $(document).on('elementor-pro/forms/new elementor/forms/new', function () {
        setTimeout(() => JCT.renderElementorWidgets(), 100);
      });
      $(window).on('elementor/popup/show', function () {
        setTimeout(() => JCT.renderElementorWidgets(), 100);
      });
      $(document).on('submit', '.elementor-form', function () {
        $(this).find('.cf-turnstile').each(function () {
          if (typeof turnstile !== 'undefined' && this.dataset.rendered) {
            turnstile.reset(this);
          }
        });
      });
    },

    fluentFormsIntegration: function () {
      function renderFluentTurnstile() {
        $('.fluentform-wrap .cf-turnstile, .fluentform .cf-turnstile').each(function () {
          const el = this;
          if (el.dataset.rendered) return;
          $(el).find('.jct-spinner').remove();
          const params = {
            sitekey: el.getAttribute('data-sitekey'),
            theme: el.getAttribute('data-theme') || JCT.config.theme || 'auto',
            size: JCT.sizeMap[el.getAttribute('data-size')] || 'normal',
            appearance: el.getAttribute('data-appearance') || JCT.config.appearance || 'always',
            callback: function (token) { JCT.setResponseInput(el, token); JCT.enableSubmit(el); },
            'expired-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); },
            'error-callback': function () { JCT.setResponseInput(el, ''); JCT.disableSubmit(el); }
          };
          turnstile.render(el, params);
          el.dataset.rendered = 'true';
        });
      }

      // On DOM ready and Fluent Forms render events
      $(document).ready(renderFluentTurnstile);
      $(document).on('fluentform_rendering_field_after fluentform_init_form fluentform_rendering_form_fields fluentform_after_form_render', function () {
        setTimeout(renderFluentTurnstile, 100);
      });

      // Watch DOM inside fluentform-wrap for AJAX inserts
      document.querySelectorAll('.fluentform-wrap').forEach(function (wrap) {
        new MutationObserver(() => {
          setTimeout(renderFluentTurnstile, 100);
        }).observe(wrap, { childList: true, subtree: true });
      });
    }
  };

  $(document).ready(function () {
    JCT.init();
    JCT.observeDOM();
    JCT.elementorIntegration();
    JCT.fluentFormsIntegration();
    JCT.renderGravityFormsWidgets();
    JCT.renderFormidableFormsWidgets();
    JCT.renderForminatorWidgets();
    JCT.renderJetpackFormsWidgets();
    JCT.renderKadenceFormsWidgets();

    ['login', 'register', 'lostpassword', 'comment'].forEach(function (context) {
      const el = document.getElementById('cf-turnstile-' + context);
      if (el && typeof turnstile !== 'undefined') {
        try { JCT.renderWidgets(); } catch (e) { if (window.console) console.error(e); }
      }
    });

    setTimeout(() => {
      try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); }
    }, 100);
  });

  $(window).on('elementor/frontend/init', function () {
    setTimeout(() => { try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  $(window).on('elementor/popup/show', function () {
    setTimeout(() => { try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  $(document).on('elementor/forms/new', function () {
    setTimeout(() => { try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  $(document).on('elementor-pro/forms/new elementor/forms/new', function () {
    setTimeout(() => { try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 200);
  });
  $(document).on('elementor/popup/show', function () {
    setTimeout(() => { try { JCT.renderElementorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 200);
  });
  $(document).on('gform_post_render', function () {
    setTimeout(() => { try { JCT.renderGravityFormsWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  $(document).on('frmFormComplete frmAfterFormRendered', function () {
    setTimeout(() => { try { JCT.renderFormidableFormsWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  // Forminator AJAX render support
  $(document).on('forminator:form:rendered', function () {
    setTimeout(() => { try { JCT.renderForminatorWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });
  // Listen for Forminator AJAX form render events and re-render Turnstile
  $(document).on('forminator:form:rendered forminator:form:ajax:rendered', function() {
    setTimeout(function() {
      if (typeof JCT !== 'undefined' && JCT.renderForminatorWidgets) {
        JCT.renderForminatorWidgets();
      }
    }, 100);
  });
  $(document).on('kb-form-rendered', function () {
    setTimeout(() => { try { JCT.renderKadenceFormsWidgets(); } catch (e) { if (window.console) console.error(e); } }, 100);
  });

})(jQuery);
