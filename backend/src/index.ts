import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import authRoutes from "./routes/auth.routes";
import examsRoutes from "./routes/exams.routes";
import questionsRoutes from "./routes/questions.routes";

dotenv.config();
const app = express();
const PORT = process.env.PORT || 4000;

app.use(cors());
app.use(express.json());

// root test
app.get("/", (_req, res) => res.send("Backend corriendo"));

app.use("/api/auth", authRoutes);
app.use("/api/exams", examsRoutes);
// mount questions inside exams (the questions.routes uses mergeParams to read examId)
app.use("/api/exams/:examId/questions", questionsRoutes);

app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
