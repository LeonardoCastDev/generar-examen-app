import { api } from "./api";
import type { User } from "@types/domain";

export async function login(email: string, password: string): Promise<User> {
  const { data } = await api.post("/auth/login", { email, password });
  return data.user as User;
}

export async function register(
  name: string,
  email: string,
  password: string
): Promise<User> {
  const { data } = await api.post("/auth/register", { name, email, password });
  return data.user as User;
}

export async function me(): Promise<User> {
  const { data } = await api.get("/auth/me");
  return data.user as User;
}

export async function logout(): Promise<void> {
  await api.post("/auth/logout");
}

export async function deleteAccount(): Promise<void> {
  await api.delete("/auth/account");
}
