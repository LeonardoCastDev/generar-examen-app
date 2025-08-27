import { api } from "./api";
//import type { Exam } from "@types/domain";
import type { Exam } from "@typesAlias/domain";

export async function listExams(): Promise<Exam[]> {
  const { data } = await api.get("/exams");
  return data as Exam[];
}

export async function createExam(
  payload: Pick<Exam, "title" | "total_questions">
): Promise<Exam> {
  const { data } = await api.post("/exams", payload);
  return data as Exam;
}

export async function updateExam(
  id: number,
  payload: Partial<Pick<Exam, "title" | "total_questions">>
): Promise<Exam> {
  const { data } = await api.put(`/exams/${id}`, payload);
  return data as Exam;
}

export async function deleteExam(id: number): Promise<void> {
  await api.delete(`/exams/${id}`);
}
