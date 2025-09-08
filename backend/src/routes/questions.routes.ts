import { Router } from "express";
import { requireAuth } from "../middlewares/auth.middleware";
import {
  listQuestions,
  createQuestion,
  updateQuestion,
  deleteQuestion,
} from "../controllers/questions.controller";

const router = Router({ mergeParams: true });

router.use(requireAuth);

router.get("/", listQuestions);
router.post("/", createQuestion);
router.put("/:questionId", updateQuestion);
router.delete("/:questionId", deleteQuestion);

export default router;
