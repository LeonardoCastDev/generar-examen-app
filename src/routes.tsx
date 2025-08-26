import { createBrowserRouter } from "react-router-dom";
import LoginPage from "@features/auth/LoginPage";
import RegisterPage from "@features/auth/RegisterPage";
import DashboardPage from "@features/exams/DashboardPage";
import ExamEditorPage from "@features/exams/ExamEditorPage";
import ExamPreviewPage from "@features/exams/ExamPreviewPage";

export const router = createBrowserRouter([
  { path: "/", element: <LoginPage /> },
  { path: "/register", element: <RegisterPage /> },
  { path: "/dashboard", element: <DashboardPage /> },
  { path: "/exams/:id", element: <ExamEditorPage /> },
  { path: "/exams/:id/preview", element: <ExamPreviewPage /> },
]);
