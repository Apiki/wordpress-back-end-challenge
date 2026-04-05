declare const apikiFavorites: {
  restUrl: string;
  nonce: string;
  isLoggedIn: boolean;
};

interface FavoriteResponse {
  id: number;
  post_id: number;
  created_at: string;
}

async function request<T>(method: string, body?: Record<string, unknown>): Promise<T> {
  const options: RequestInit = {
    method,
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": apikiFavorites.nonce,
    },
    credentials: "same-origin",
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(apikiFavorites.restUrl, options);

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Request failed");
  }

  return response.json();
}

export function addFavorite(postId: number): Promise<FavoriteResponse> {
  return request<FavoriteResponse>("POST", { post_id: postId });
}

export function removeFavorite(postId: number): Promise<{ deleted: boolean }> {
  return request<{ deleted: boolean }>("DELETE", { post_id: postId });
}
