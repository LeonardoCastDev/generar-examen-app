import type { Question } from "@types/domain";

export default function QuestionList({
  questions,
  onEdit,
  onDelete,
}: {
  questions: Question[];
  onEdit: (q: Question) => void;
  onDelete: (q: Question) => void;
}) {
  return (
    <ul className="list">
      {questions.map((q, idx) => (
        <li key={q.id} className="item">
          <div>
            <strong>{idx + 1}.</strong> {q.question_text}
            <small>
              {" "}
              —{" "}
              {q.type === "multiple" ? "Opción múltiple" : "Respuesta abierta"}
            </small>
          </div>
          <div className="row">
            <button onClick={() => onEdit(q)}>Modificar</button>
            <button className="danger" onClick={() => onDelete(q)}>
              Eliminar
            </button>
          </div>
        </li>
      ))}
    </ul>
  );
}
