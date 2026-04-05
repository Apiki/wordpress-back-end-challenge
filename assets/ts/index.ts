import { addFavorite, removeFavorite } from "./api";
import { showToast } from "./toast";
import "../scss/index.scss";

function init(): void {
  document.querySelectorAll<HTMLButtonElement>(".apiki-favorite-btn").forEach((button) => {
    button.addEventListener("click", handleClick);
  });
}

async function handleClick(event: Event): Promise<void> {
  const button = event.currentTarget as HTMLButtonElement;
  const postId = Number(button.dataset.postId);

  if (!postId || button.disabled) return;

  button.disabled = true;
  button.classList.add("apiki-favorite-btn--loading");

  const isActive = button.classList.contains("apiki-favorite-btn--active");

  try {
    if (isActive) {
      await removeFavorite(postId);
      button.classList.remove("apiki-favorite-btn--active");
      button.setAttribute("aria-label", "Add to favorites");
      button.setAttribute("title", "Add to favorites");
      showToast("Removed from favorites", "success");
    } else {
      await addFavorite(postId);
      button.classList.add("apiki-favorite-btn--active");
      button.setAttribute("aria-label", "Remove from favorites");
      button.setAttribute("title", "Remove from favorites");
      showToast("Added to favorites!", "success");
    }
  } catch (error) {
    const message = error instanceof Error ? error.message : "Something went wrong";
    showToast(message, "error");
  } finally {
    button.disabled = false;
    button.classList.remove("apiki-favorite-btn--loading");
  }
}

document.addEventListener("DOMContentLoaded", init);
