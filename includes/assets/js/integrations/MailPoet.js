document.addEventListener("DOMContentLoaded", () => {
    jQuery("form.mailpoet_form").on("submit", function () {
      const $form = jQuery(this);
      const tokenElem = document.querySelector("input[name='jct-turnstile-response']");
  
      if (tokenElem && tokenElem.value) {
        // Sanitize token just in case
        const token = tokenElem.value.replace(/"/g, '&quot;');
  
        // Remove any existing hidden Turnstile field before appending
        $form.find("input[name='data[jct-turnstile-response]']").remove();
  
        $form.append(
          `<input type="hidden" name="data[jct-turnstile-response]" value="${token}">`
        );
      }
    });
  });
  
