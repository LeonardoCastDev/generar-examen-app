import { create } from "zustand";

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
    const exam = (await Exams.listExams()).find((e) => e.id === id) || null; // or Exams.get(id)
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
    // keep teacherKey and re-sample with a new seed
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
