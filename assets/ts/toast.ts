export function showToast(message: string, type: "success" | "error"): void {
  const toast = document.createElement("div");
  toast.className = `apiki-toast apiki-toast--${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.add("apiki-toast--visible");
  });

  setTimeout(() => {
    toast.classList.remove("apiki-toast--visible");
    toast.addEventListener("transitionend", () => toast.remove());
  }, 3000);
}
