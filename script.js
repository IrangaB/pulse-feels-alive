const menuButton = document.getElementById("menuButton");
const navigation = document.getElementById("navigation");
if (menuButton && navigation) {
  menuButton.addEventListener("click", () => {
    const open = navigation.classList.toggle("open");
    menuButton.setAttribute("aria-expanded", String(open));
    menuButton.textContent = open ? "×" : "☰";
  });
  navigation.querySelectorAll("a").forEach(link => link.addEventListener("click", () => {
    navigation.classList.remove("open");
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.textContent = "☰";
  }));
}
const year = document.getElementById("year");
if (year) year.textContent = new Date().getFullYear();

/* Enquiry form: sends without leaving the page. Without JavaScript the form still posts normally. */
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  const fields = document.getElementById("form-fields");
  const submit = document.getElementById("form-submit");
  const status = document.getElementById("form-status");
  const setStatus = (message, isError = false) => {
    status.textContent = message;
    status.classList.toggle("form-error", isError);
  };
  contactForm.addEventListener("submit", async event => {
    event.preventDefault();
    if (contactForm.dataset.preview) {
      setStatus("Review preview: the enquiry form is switched off here and will work on the final website.");
      return;
    }
    if (!contactForm.reportValidity()) return;
    submit.disabled = true;
    submit.textContent = "Sending…";
    setStatus("Sending your enquiry…");
    try {
      const response = await fetch(contactForm.action, {
        method: "POST",
        body: new FormData(contactForm),
        headers: { Accept: "application/json" },
      });
      const result = await response.json().catch(() => ({}));
      if (!response.ok || !result.ok) throw new Error(result.message || "");
      contactForm.reset();
      fields.hidden = true;
      submit.hidden = true;
      setStatus(result.message);
    } catch (error) {
      setStatus(error.message || "We could not send your enquiry. Please check your details and try again. If the problem continues, contact PULSE directly.", true);
      submit.disabled = false;
      submit.innerHTML = "Send Enquiry <span>↗</span>";
    }
  });
}
