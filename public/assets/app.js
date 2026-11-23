"use strict";
// A validação de estoque, permissões e preço permanece sempre no servidor.
document.querySelectorAll("form[data-confirm]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  });
});
const checkout = document.querySelector("#checkout-form");
checkout?.addEventListener("submit", () => {
  const button = checkout.querySelector("[type=submit]");
  button.disabled = true;
  button.textContent = "Confirmando…";
});
