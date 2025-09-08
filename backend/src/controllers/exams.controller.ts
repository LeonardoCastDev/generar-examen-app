import { Request, Response } from "express";
import { db } from "../db";
import { AuthRequest } from "../middlewares/auth.middleware";
import PDFDocument from "pdfkit";

// helpers
function shuffle<T>(arr: T[], seed?: number) {
  const a = [...arr];
  // simple seeded shuffle if seed given
  let random = Math.random;
  if (seed !== undefined) {
    let s = seed % 2147483647;
    if (s <= 0) s += 2147483646;
    random = () => (s = (s * 16807) % 2147483647) / 2147483647;
  }
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

// List exams for logged user
export const listExams = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const [rows]: any = await db.query(
      "SELECT * FROM exams WHERE user_id = ? ORDER BY created_at DESC",
      [userId]
    );
    return res.json(rows);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error listando exámenes" });
  }
};

export const createExam = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const { title, total_questions } = req.body;
    if (!title || !total_questions)
      return res.status(400).json({ message: "Faltan datos" });

    const [result]: any = await db.query(
      "INSERT INTO exams (user_id, title, total_questions) VALUES (?, ?, ?)",
      [userId, title, total_questions]
    );
    const examId = result.insertId;
    const [examRows]: any = await db.query("SELECT * FROM exams WHERE id = ?", [
      examId,
    ]);
    return res.status(201).json(examRows[0]);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error creando examen" });
  }
};

export const updateExam = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const { id } = req.params;
    const { title, total_questions } = req.body;

    // check owner
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [id]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    await db.query(
      "UPDATE exams SET title = ?, total_questions = ? WHERE id = ?",
      [title ?? rows[0].title, total_questions ?? rows[0].total_questions, id]
    );
    const [examRows]: any = await db.query("SELECT * FROM exams WHERE id = ?", [
      id,
    ]);
    return res.json(examRows[0]);
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error actualizando examen" });
  }
};

export const deleteExam = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const { id } = req.params;
    const [rows]: any = await db.query(
      "SELECT user_id FROM exams WHERE id = ?",
      [id]
    );
    if (rows.length === 0)
      return res.status(404).json({ message: "Examen no encontrado" });
    if (rows[0].user_id !== userId)
      return res.status(403).json({ message: "No autorizado" });

    await db.query("DELETE FROM exams WHERE id = ?", [id]);
    return res.json({ message: "Examen eliminado" });
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error eliminando examen" });
  }
};

// Helper to fetch questions + options for an exam
async function getQuestionsWithOptions(examId: number) {
  const [qs]: any = await db.query(
    "SELECT * FROM questions WHERE exam_id = ?",
    [examId]
  );
  const questions = qs as any[];
  for (const q of questions) {
    const [opts]: any = await db.query(
      "SELECT * FROM options WHERE question_id = ? ORDER BY id",
      [q.id]
    );
    q.options = opts;
  }
  return questions;
}

// Preview: return randomized selection (JSON)
export const previewExam = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const { id } = req.params; // exam id
    const teacherKey = req.query.teacherKey === "true";
    const seed = req.query.seed ? Number(req.query.seed) : undefined;

    // check ownership
    const [rows]: any = await db.query(
      "SELECT * FROM exams WHERE id = ? AND user_id = ?",
      [id, userId]
    );
    if (rows.length === 0)
      return res
        .status(404)
        .json({ message: "Examen no encontrado o no eres el dueño" });

    const exam: any = rows[0];
    const questions = await getQuestionsWithOptions(Number(id));
    const randomized = shuffle(questions, seed)
      .slice(0, exam.total_questions)
      .map((q: any) => {
        const opts =
          q.options && Array.isArray(q.options)
            ? shuffle(q.options, seed)
            : q.options;
        return { ...q, options: opts };
      });

    return res.json({
      exam,
      selection: randomized,
      seed: seed ?? Date.now(),
      teacherKey,
    });
  } catch (err) {
    console.error(err);
    return res.status(500).json({ message: "Error generando preview" });
  }
};

// Generate PDF and send as response (application/pdf)
export const generatePdf = async (req: AuthRequest, res: Response) => {
  try {
    const userId = req.userId!;
    const { id } = req.params; // exam id
    const {
      studentName = "",
      matricula = "",
      teacherKey = false,
      seed,
    } = req.body as any;

    // ownership check
    const [rows]: any = await db.query(
      "SELECT * FROM exams WHERE id = ? AND user_id = ?",
      [id, userId]
    );
    if (rows.length === 0)
      return res
        .status(404)
        .json({ message: "Examen no encontrado o no eres el dueño" });

    const exam: any = rows[0];
    const questions = await getQuestionsWithOptions(Number(id));
    const randomized = shuffle(questions, seed ?? undefined)
      .slice(0, exam.total_questions)
      .map((q: any) => {
        const opts =
          q.options && Array.isArray(q.options)
            ? shuffle(q.options, seed ?? undefined)
            : q.options;
        return { ...q, options: opts };
      });

    // create PDF
    const doc = new PDFDocument({ size: "A4", margin: 40 });
    res.setHeader("Content-Type", "application/pdf");
    res.setHeader(
      "Content-Disposition",
      `attachment; filename="${exam.title}.pdf"`
    );

    // pipe directly to response
    doc.pipe(res);

    // Header
    doc.fontSize(18).text(exam.title, { align: "center" });
    doc.moveDown(0.2);
    doc.fontSize(10).text(`Alumno: ${studentName}`, { align: "left" });
    doc.text(`Matrícula: ${matricula}`, { align: "left" });
    doc.text(`Fecha: ${new Date().toLocaleDateString()}`, { align: "left" });
    doc.moveDown(0.5);

    // Questions
    randomized.forEach((q: any, idx: number) => {
      doc.fontSize(12).text(`${idx + 1}. ${q.question_text}`);
      doc.moveDown(0.2);
      if (q.type === "multiple" && Array.isArray(q.options)) {
        q.options.forEach((o: any, i: number) => {
          const letter = String.fromCharCode(65 + i);
          let line = `   ${letter}) ${o.option_text}`;
          if (teacherKey && o.is_correct) line += "  ✓";
          doc.fontSize(11).text(line);
        });
        doc.moveDown(0.4);
      } else {
        doc.moveDown(0.8);
      }
    });

    doc.end();
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: "Error generando PDF" });
  }
};
