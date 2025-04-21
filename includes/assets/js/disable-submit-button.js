/**
 * Enables submit buttons once Turnstile is validated
 */
function jctEnableSubmitButtons(selector) {
    document.querySelectorAll(selector)?.forEach(el => {
      el.style.pointerEvents = 'auto';
      el.style.opacity = '1';
    });
  }
  
  // Unified callbacks
  function turnstileWPCallback()             { jctEnableSubmitButtons('#wp-submit'); }
  function turnstileCommentCallback()        { jctEnableSubmitButtons('.jct-turnstile-comment'); }
  function turnstileWooLoginCallback()       { jctEnableSubmitButtons('.woocommerce-form-login__submit'); }
  function turnstileWooRegisterCallback()    { jctEnableSubmitButtons('.woocommerce-form-register__submit'); }
  function turnstileWooResetCallback()       { jctEnableSubmitButtons('.woocommerce-ResetPassword .button'); }
  function turnstileEDDLoginCallback()       { jctEnableSubmitButtons('#edd_login_submit'); }
  function turnstileEDDRegisterCallback()    { jctEnableSubmitButtons('#edd_register_form .edd-submit'); }
  function turnstilePMPLoginCallback()       { jctEnableSubmitButtons('#wp-submit'); }
  function turnstileElementorCallback()      { jctEnableSubmitButtons('.elementor-field-type-submit .elementor-button'); }
  function turnstileKadenceCallback()        { jctEnableSubmitButtons('.kb-submit-field .kb-button'); }
  function turnstileCF7Callback()            { jctEnableSubmitButtons('.wpcf7-submit'); }
  function turnstileMC4WPCallback()          { jctEnableSubmitButtons('.mc4wp-form-fields input[type=submit]'); }
  function turnstileBPCallback()             { jctEnableSubmitButtons('#buddypress #signup-form .submit'); }
  function turnstileBBPressReplyCallback()   { jctEnableSubmitButtons('#bbp_reply_submit'); }
  function turnstileBBPressCreateCallback()  { jctEnableSubmitButtons('#bbp_topic_submit'); }
  function turnstileWPFCallback()            { jctEnableSubmitButtons('.wpforms-submit'); }
  function turnstileFluentCallback()         { jctEnableSubmitButtons('.fluentform .ff-btn-submit'); }
  function turnstileFormidableCallback()     { jctEnableSubmitButtons('.frm_forms .frm_button_submit'); }
  function turnstileGravityCallback()        { jctEnableSubmitButtons('.gform_button'); }
  function turnstileUMCallback()             { jctEnableSubmitButtons('#um-submit-btn'); }
  function turnstileWPUFCallback()           { jctEnableSubmitButtons('.wpuf-form input[type="submit"]'); }
  function turnstileMEPRCallback()           { jctEnableSubmitButtons('.mepr-submit'); }
  
