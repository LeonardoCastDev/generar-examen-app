import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import Navbar from "@components/Navbar";
import { useExams } from "@store/exams.store";
import TeacherKeyToggle from "./TeacherKeyToggle";
import { PDFDownloadLink, PDFViewer } from "@react-pdf/renderer";
import ExamDocument from "@pdf/ExamDocument";

export default function ExamPreviewPage() {
  const { id } = useParams();
  const nav = useNavigate();
  const { currentExam, loadExam, preview, makePreview, rerandomize } =
    useExams();
  const [teacherKey, setTeacherKey] = useState(preview?.teacherKey ?? false);

  useEffect(() => {
    if (id) void loadExam(Number(id));
  }, [id, loadExam]);
  useEffect(() => {
    if (currentExam && !preview) makePreview(teacherKey);
  }, [currentExam, preview, makePreview, teacherKey]);

  if (!currentExam || !preview)
    return (
      <div className="center">
        <p>Cargando preview…</p>
      </div>
    );

  return (
    <>
      <Navbar />
      <main className="wrap">
        <div className="row between">
          <h2>Preview: {currentExam.title}</h2>
          <div className="row gap">
            <TeacherKeyToggle
              value={teacherKey}
              onChange={(v) => {
                setTeacherKey(v);
                makePreview(v);
              }}
            />
            <button onClick={() => rerandomize()}>Randomizar preguntas</button>
            <button onClick={() => nav(-1)}>Volver</button>
          </div>
        </div>

        <section className="pdf">
          <PDFViewer width="100%" height={600} showToolbar>
            <ExamDocument
              exam={currentExam}
              selection={preview.selection}
              teacherKey={teacherKey}
            />
          </PDFViewer>
        </section>

        <div className="row end">
          <PDFDownloadLink
            fileName={`${currentExam.title}.pdf`}
            document={
              <ExamDocument
                exam={currentExam}
                selection={preview.selection}
                teacherKey={teacherKey}
              />
            }
          >
            {({ loading }) => (
              <button disabled={loading}>
                {loading ? "Generando…" : "Descargar PDF"}
              </button>
            )}
          </PDFDownloadLink>
        </div>
      </main>
    </>
  );
}
