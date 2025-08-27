// src/features/exams/ExamEditorPage.tsx
import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

import Navbar from "@components/Navbar";
import QuestionForm from "@features/exams/QuestionForm";
import QuestionList from "@features/exams/QuestionList";
import ConfirmDialog from "@components/ConfirmDialog";

import { useExams } from "@store/exams.store";
import type { Question } from "@typesAlias/domain";

export default function ExamEditorPage() {
  const { id } = useParams<{ id?: string }>();
  const nav = useNavigate();

  const {
    loadExam,
    currentExam,
    questions,
    addQuestion,
    updateQuestion,
    deleteQuestion,
    makePreview,
  } = useExams();

  const [editing, setEditing] = useState<Question | null>(null);
  const [confirmDelete, setConfirmDelete] = useState<Question | null>(null);

  useEffect(() => {
    if (id) void loadExam(Number(id));
  }, [id, loadExam]);

  if (!currentExam)
    return (
      <div className="center">
        <p>Cargando…</p>
      </div>
    );

  return (
    <>
      <Navbar />
      <main className="wrap">
        <div className="row between">
          <h2>{currentExam.title}</h2>
          <div className="row">
            <button onClick={() => makePreview(false)}>
              Preview aleatorio
            </button>
            <button onClick={() => nav(`/exams/${currentExam.id}/preview`)}>
              Ver preview
            </button>
          </div>
        </div>

        <div className="row gap">
          <div className="col">
            <h3>Agregar una pregunta</h3>
            <QuestionForm
              initial={editing ?? undefined}
              onSave={(q) => {
                if (editing) {
                  void updateQuestion(editing.id, q);
                  setEditing(null);
                } else {
                  void addQuestion(q);
                }
              }}
            />
          </div>

          <div className="col">
            <h3>Todas las preguntas</h3>
            <QuestionList
              questions={questions}
              onEdit={(q) => setEditing(q)}
              onDelete={(q) => setConfirmDelete(q)}
            />
          </div>
        </div>

        <ConfirmDialog
          open={!!confirmDelete}
          title="Eliminar pregunta"
          description="Esta acción no se puede deshacer."
          onCancel={() => setConfirmDelete(null)}
          onConfirm={async () => {
            if (confirmDelete) await deleteQuestion(confirmDelete.id);
            setConfirmDelete(null);
          }}
        />
      </main>
    </>
  );
}
