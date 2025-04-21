/* Elementor Pro Popups – Cloudflare Turnstile Integration */
jQuery(document).ready(function ($) {
    $(document).on('elementor/popup/show', function () {
      if (!window.turnstile) return;
  
      // Use requestAnimationFrame inside a timeout for smoother layout
      setTimeout(() => {
        requestAnimationFrame(() => {
          const container = document.querySelector('.elementor-popup-modal .jct-turnstile');
  
          if (!container) return;
  
          // Hide any existing failure messages
          $('.jct-turnstile-failed-text').hide();
  
          // Check if Turnstile already rendered here
          if (!container.querySelector('iframe')) {
            turnstile.remove(container);
            turnstile.render(container);
          }
        });
      }, 300); // Reduced delay for snappier response
    });
  });
  
