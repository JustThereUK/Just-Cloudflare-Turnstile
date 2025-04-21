document.addEventListener("DOMContentLoaded", () => {
    // Auto-click WordPress accordion section if present
    const wpAccordion = document.querySelector("#jct-accordion-wordpress");
    if (wpAccordion) {
      wpAccordion.click();
    }
  
    // Accordion toggle functionality
    const accordions = document.querySelectorAll(".jct-accordion");
    accordions.forEach(acc => {
      acc.addEventListener("click", function () {
        this.classList.toggle("jct-active");
        const panel = this.nextElementSibling;
        const isOpen = panel.style.display === "block";
  
        panel.style.display = isOpen ? "none" : "block";
        this.setAttribute("aria-expanded", !isOpen);
      });
    });
  
    // Appearance dropdown description toggles
    const appearanceSelect = document.querySelector("select[name='jct_appearance']");
    if (appearanceSelect) {
      const updateDescription = selected => {
        document.querySelectorAll('.wcu-appearance-always, .wcu-appearance-execute, .wcu-appearance-interaction-only')
          .forEach(el => el.style.display = 'none');
  
        const target = document.querySelector(`.wcu-appearance-${selected}`);
        if (target) {
          target.style.display = 'block';
        }
      };
  
      updateDescription(appearanceSelect.value);
  
      appearanceSelect.addEventListener("change", function () {
        updateDescription(this.value);
      });
    }
  });
  
