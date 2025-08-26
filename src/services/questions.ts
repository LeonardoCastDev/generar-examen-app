import { api } from "./api";
import type { Question, Option } from "@types/domain";

export async function listQuestions(examId: number): Promise<Question[]> {
  const { data } = await api.get(`/exams/${examId}/questions`);
  return data as Question[];
}

export async function createQuestion(
  examId: number,
  payload: Omit<Question, "id" | "exam_id">
): Promise<Question> {
  const { data } = await api.post(`/exams/${examId}/questions`, payload);
  return data as Question;
}

export async function updateQuestion(
  examId: number,
  id: number,
  payload: Partial<Question>
): Promise<Question> {
  const { data } = await api.put(`/exams/${examId}/questions/${id}`, payload);
  return data as Question;
}

export async function deleteQuestion(
  examId: number,
  id: number
): Promise<void> {
  await api.delete(`/exams/${examId}/questions/${id}`);
}

export async function addOption(
  questionId: number,
  payload: Omit<Option, "id" | "question_id">
): Promise<Option> {
  const { data } = await api.post(`/questions/${questionId}/options`, payload);
  return data as Option;
}

export async function deleteOption(
  questionId: number,
  optionId: number
): Promise<void> {
  await api.delete(`/questions/${questionId}/options/${optionId}`);
}
