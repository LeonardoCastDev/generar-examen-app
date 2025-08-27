import { create } from "zustand";
//import type { User } from "@types/domain";
import type { User } from "@typesAlias/domain";
import * as Auth from "@services/auth";

interface AuthState {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  deleteAccount: () => Promise<void>;
}

export const useAuth = create<AuthState>((set) => ({
  user: null,
  loading: false,
  async login(email, password) {
    set({ loading: true });
    const user = await Auth.login(email, password);
    set({ user, loading: false });
  },
  async register(name, email, password) {
    set({ loading: true });
    const user = await Auth.register(name, email, password);
    set({ user, loading: false });
  },
  async logout() {
    await Auth.logout();
    set({ user: null });
  },
  async deleteAccount() {
    await Auth.deleteAccount();
    set({ user: null });
  },
}));
