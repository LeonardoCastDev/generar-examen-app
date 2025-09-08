import { Router } from "express";
import { requireAuth } from "../middlewares/auth.middleware";
import {
  listExams,
  createExam,
  updateExam,
  deleteExam,
  previewExam,
  generatePdf,
} from "../controllers/exams.controller";

const router = Router();

router.use(requireAuth);
router.get("/", listExams);
router.post("/", createExam);
router.put("/:id", updateExam);
router.delete("/:id", deleteExam);

// preview and pdf
router.get("/:id/preview", previewExam); // returns JSON preview (seeded selection)
router.post("/:id/generate-pdf", generatePdf); // send body { studentName, matricula, teacherKey?, seed? }

export default router;
