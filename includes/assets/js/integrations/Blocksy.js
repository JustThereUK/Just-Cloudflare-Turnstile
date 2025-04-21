/* Blocksy Integration – Handles login/register modal Turnstile reset */
document.addEventListener('DOMContentLoaded', () => {
    const headerAccounts = document.querySelectorAll('.ct-header-account');
  
    if (!window.turnstile) return;
  
    headerAccounts.forEach(accountBtn => {
      accountBtn.addEventListener('click', () => {
        // Remove any stale Turnstile instances
        turnstile.remove('.ct-account-panel #jct-turnstile-woo-register');
  
        // Delay to allow modal DOM to render, then safely reset Turnstile
        setTimeout(() => {
          requestAnimationFrame(() => {
            const loginTurnstile = document.querySelector('.ct-account-panel #loginform .jct-turnstile');
            const registerTurnstile = document.querySelector('.ct-account-panel #registerform .jct-turnstile');
  
            if (loginTurnstile) {
              turnstile.reset(loginTurnstile);
            }
  
            if (registerTurnstile) {
              turnstile.reset(registerTurnstile);
              turnstile.remove('.ct-account-panel #registerform .jct-woocommerce-register');
            }
          });
        }, 500);
      });
    });
  });
  
