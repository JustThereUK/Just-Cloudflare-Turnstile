jQuery(document).ready(function ($) {
    if (typeof jctVars === 'undefined' || !window.turnstile) return;
  
    const $submitFields = $('.kb-submit-field');
  
    if ($submitFields.length > 0) {
      // Inject Turnstile before the submit field
      $submitFields.before(jctVars.field);
  
      const turnstileItem = document.getElementById('jct-turnstile-kadence');
      if (turnstileItem && !turnstileItem.querySelector('iframe')) {
        const sitekey = turnstileItem.dataset.sitekey || turnstileItem.dataset.field;
        if (sitekey) {
          turnstile.render(turnstileItem, { sitekey });
        }
      }
    }
  });
  
  // Re-render on submit click if needed (i.e. after validation fail or page change)
  jQuery(document).on('click', '.kb-submit-field .kb-button', function () {
    setTimeout(() => {
      const turnstileItem = document.getElementById('jct-turnstile-kadence');
      if (turnstileItem) {
        turnstile.remove(turnstileItem);
  
        const sitekey = turnstileItem.dataset.sitekey || turnstileItem.dataset.field;
        if (sitekey) {
          turnstile.render(turnstileItem, { sitekey });
        }
      }
    }, 500); // Reduced from 5000ms to make it feel faster
  });
  
