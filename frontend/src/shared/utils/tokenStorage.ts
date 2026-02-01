const TOKEN_KEY = 'auth_token';
const TOKEN_EXPIRY_KEY = 'auth_token_expiry';

export const tokenStorage = {
  get(): string | null {
    const token = localStorage.getItem(TOKEN_KEY);
    const expiry = localStorage.getItem(TOKEN_EXPIRY_KEY);

    if (!token || !expiry) {
      return null;
    }

    if (new Date(expiry) <= new Date()) {
      this.remove();
      return null;
    }

    return token;
  },

  set(token: string, expiresAt: string): void {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(TOKEN_EXPIRY_KEY, expiresAt);
  },

  remove(): void {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(TOKEN_EXPIRY_KEY);
  },

  exists(): boolean {
    return !!this.get();
  },

  getExpiry(): string | null {
    return localStorage.getItem(TOKEN_EXPIRY_KEY);
  },

  isExpired(): boolean {
    const expiry = this.getExpiry();
    if (!expiry) return true;
    return new Date(expiry) <= new Date();
  },
};
