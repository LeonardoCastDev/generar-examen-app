// src/store/exams.store.ts
import { create } from "zustand";
import type { Exam, Question } from "@typesAlias/domain";

// Tipos del store
interface Preview {
  seed: number;
  teacherKey: boolean;
  selection: Question[];
}

export interface ExamsState {
  exams: Exam[];
  currentExam: Exam | null;
  questions: Question[];
  preview: Preview | null;

  fetchExams: () => Promise<void>;
  createExam: (title: string, total_questions: number) => Promise<void>;
  removeExam: (id: number) => Promise<void>;
  loadExam: (id: number) => Promise<void>;
  addQuestion: (q: Omit<Question, "id" | "exam_id">) => Promise<void>;
  updateQuestion: (
    id: number,
    patch: Partial<Omit<Question, "id" | "exam_id">>
  ) => Promise<void>;
  deleteQuestion: (id: number) => Promise<void>;
  makePreview: (teacherKey?: boolean) => void;
  rerandomize: () => void;
}

// Función shuffle simple
function shuffle<T>(array: T[], seed: number): T[] {
  const arr = [...array];
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor((Math.sin(seed + i) * 10000) % (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

// Mock API temporal
const Exams = {
  listExams: async (): Promise<Exam[]> => [],
  createExam: async (data: {
    title: string;
    total_questions: number;
  }): Promise<Exam> => ({
    id: Math.floor(Math.random() * 10000),
    title: data.title,
    total_questions: data.total_questions,
    user_id: 1,
  }),
  deleteExam: async (id: number) => {},
};

const Questions = {
  listQuestions: async (examId: number): Promise<Question[]> => [],
  createQuestion: async (
    examId: number,
    q: Omit<Question, "id" | "exam_id">
  ): Promise<Question> => ({
    ...q,
    id: Math.floor(Math.random() * 10000),
    exam_id: examId,
  }),
  updateQuestion: async (
    examId: number,
    id: number,
    patch: Partial<Omit<Question, "id" | "exam_id">>
  ): Promise<Question> => ({
    id,
    exam_id: examId,
    type: patch.type || "open",
    question_text: patch.question_text || "",
    options: patch.options,
  }),
  deleteQuestion: async (examId: number, id: number) => {},
};

export const useExams = create<ExamsState>((set, get) => ({
  exams: [],
  currentExam: null,
  questions: [],
  preview: null,

  async fetchExams() {
    const exams = await Exams.listExams();
    set({ exams });
  },

  async createExam(title, total_questions) {
    const e = await Exams.createExam({ title, total_questions });
    set((s) => ({ exams: [e, ...s.exams] }));
  },

  async removeExam(id) {
    await Exams.deleteExam(id);
    set((s) => ({
      exams: s.exams.filter((e) => e.id !== id),
      currentExam: s.currentExam?.id === id ? null : s.currentExam,
    }));
  },

  async loadExam(id) {
    const exam = (await Exams.listExams()).find((e) => e.id === id) || null;
    const questions = exam ? await Questions.listQuestions(exam.id) : [];
    set({ currentExam: exam, questions, preview: null });
  },

  async addQuestion(q) {
    const exam = get().currentExam!;
    const saved = await Questions.createQuestion(exam.id, q);
    set((s) => ({ questions: [saved, ...s.questions] }));
  },

  async updateQuestion(id, patch) {
    const exam = get().currentExam!;
    const updated = await Questions.updateQuestion(exam.id, id, patch);
    set((s) => ({
      questions: s.questions.map((q) => (q.id === id ? updated : q)),
    }));
  },

  async deleteQuestion(id) {
    const exam = get().currentExam!;
    await Questions.deleteQuestion(exam.id, id);
    set((s) => ({ questions: s.questions.filter((q) => q.id !== id) }));
  },

  makePreview(teacherKey = false) {
    const exam = get().currentExam;
    const all = get().questions;
    if (!exam) return;
    const seed = Math.floor(Math.random() * 1e9);
    const picked = shuffle(all, seed)
      .slice(0, exam.total_questions)
      .map((q) => ({
        ...q,
        options:
          q.type === "multiple" && q.options
            ? shuffle(q.options, seed)
            : q.options,
      }));
    set({ preview: { seed, teacherKey, selection: picked } });
  },

  rerandomize() {
    const p = get().preview;
    if (!p) return;
    const exam = get().currentExam!;
    const all = get().questions;
    const seed = Math.floor(Math.random() * 1e9);
    const picked = shuffle(all, seed)
      .slice(0, exam.total_questions)
      .map((q) => ({
        ...q,
        options:
          q.type === "multiple" && q.options
            ? shuffle(q.options, seed)
            : q.options,
      }));
    set({ preview: { ...p, seed, selection: picked } });
  },
}));
