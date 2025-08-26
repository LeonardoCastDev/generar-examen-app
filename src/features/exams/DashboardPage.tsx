import { useEffect, useState } from "react";
import Navbar from "@components/Navbar";
import EmptyState from "@components/EmptyState";
import { useExams } from "@store/exams.store";
import ExamCreateModal from "./ExamCreateModal";
import { Link } from "react-router-dom";

export default function DashboardPage() {
  const { exams, fetchExams, removeExam } = useExams();
  const [openCreate, setOpenCreate] = useState(false);

  useEffect(() => {
    void fetchExams();
  }, [fetchExams]);

  return (
    <>
      <Navbar />
      <main className="wrap">
        <div className="row between">
          <h2>Mis exámenes</h2>
          <button onClick={() => setOpenCreate(true)}>Crear examen</button>
        </div>

        {exams.length === 0 ? (
          <EmptyState
            title="Aún no has creado exámenes"
            action={
              <button onClick={() => setOpenCreate(true)}>Crear examen</button>
            }
          />
        ) : (
          <ul className="grid">
            {exams.map((e) => (
              <li key={e.id} className="card">
                <h3>{e.title}</h3>
                <p>Mostrará {e.total_questions} preguntas</p>
                <div className="row">
                  <Link to={`/exams/${e.id}`}>Editar</Link>
                  <button className="danger" onClick={() => removeExam(e.id)}>
                    Borrar
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </main>

      {openCreate && <ExamCreateModal onClose={() => setOpenCreate(false)} />}
    </>
  );
}
