import { Request, Response } from "express";
import { db } from "../db";
import { AuthRequest } from "../middlewares/auth.middleware";

export const listQuestions = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const examId = Number(req.params.examId);
    // verify ownership
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [examId]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    const [qs]: any = await db.query(
      "SELECT * FROM questions WHERE exam_id = ?",
      [examId]
    );
    const questions = qs as any[];

    // attach options
    for (const q of questions) {
      const [opts]: any = await db.query(
        "SELECT * FROM options WHERE question_id = ? ORDER BY id",
        [q.id]
      );
      q.options = opts;
    }

    return res.json(questions);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error listando preguntas" });
  }
};

export const createQuestion = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const examId = Number(req.params.examId);
    const { type, question_text, options } = req.body;

    // verify ownership
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [examId]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    const [result]: any = await db.query(
      "INSERT INTO questions (exam_id, type, question_text) VALUES (?, ?, ?)",
      [examId, type, question_text]
    );
    const questionId = result.insertId;

    if (type === "multiple" && Array.isArray(options)) {
      for (const opt of options) {
        await db.query(
          "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)",
          [questionId, opt.option_text, !!opt.is_correct]
        );
      }
    }

    const [qrows]: any = await db.query(
      "SELECT * FROM questions WHERE id = ?",
      [questionId]
    );
    const newQ = qrows[0];
    const [opts]: any = await db.query(
      "SELECT * FROM options WHERE question_id = ?",
      [questionId]
    );
    newQ.options = opts;

    return res.status(201).json(newQ);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error creando pregunta" });
  }
};

export const updateQuestion = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const examId = Number(req.params.examId);
    const qId = Number(req.params.questionId);
    const { type, question_text, options } = req.body;

    // ownership
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [examId]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    await db.query(
      "UPDATE questions SET type = ?, question_text = ? WHERE id = ?",
      [type, question_text, qId]
    );

    // refresh options: easiest is delete old and insert new for simplicity
    await db.query("DELETE FROM options WHERE question_id = ?", [qId]);
    if (type === "multiple" && Array.isArray(options)) {
      for (const opt of options) {
        await db.query(
          "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)",
          [qId, opt.option_text, !!opt.is_correct]
        );
      }
    }

    const [qrows]: any = await db.query(
      "SELECT * FROM questions WHERE id = ?",
      [qId]
    );
    const [opts]: any = await db.query(
      "SELECT * FROM options WHERE question_id = ?",
      [qId]
    );
    const updated = qrows[0];
    updated.options = opts;
    return res.json(updated);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error actualizando pregunta" });
  }
};

export const deleteQuestion = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const examId = Number(req.params.examId);
    const qId = Number(req.params.questionId);

    // ownership
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [examId]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    await db.query("DELETE FROM questions WHERE id = ?", [qId]);
    return res.json({ message: "Pregunta eliminada" });
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error eliminando pregunta" });
  }
};
