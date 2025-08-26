export type QuestionType = "open" | "multiple";

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Exam {
  id: number;
  user_id: number;
  title: string;
  total_questions: number; // how many will be shown when generating
  created_at?: string;
  updated_at?: string;
}

export interface Question {
  id: number;
  exam_id: number;
  type: QuestionType;
  question_text: string;
  options?: Option[]; // only for multiple
}

export interface Option {
  id: number;
  question_id: number;
  option_text: string;
  is_correct: boolean;
}
