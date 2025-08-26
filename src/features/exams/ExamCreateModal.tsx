import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import Field from "@components/Field";
import { useExams } from "@store/exams.store";

const Schema = z.object({
  title: z.string().min(2),
  total_questions: z.number().int().min(1),
});

type FormData = z.infer<typeof Schema>;

export default function ExamCreateModal({ onClose }: { onClose: () => void }) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormData>({
    resolver: zodResolver(Schema),
    defaultValues: { title: "", total_questions: 10 },
  });
  const { createExam } = useExams();

  const onSubmit = async (data: any) => {
    data.total_questions = Number(data.total_questions);
    await createExam(data.title, data.total_questions);
    onClose();
  };

  return (
    <div className="modal">
      <form className="card" onSubmit={handleSubmit(onSubmit)}>
        <h3>Nuevo examen</h3>
        <Field label="Título">
          <input type="text" {...register("title")} />
          {errors.title && (
            <small className="error">{errors.title.message}</small>
          )}
        </Field>
        <Field label="Número de preguntas a mostrar">
          <input
            type="number"
            min={1}
            {...register("total_questions", { valueAsNumber: true })}
          />
          {errors.total_questions && (
            <small className="error">
              {errors.total_questions.message as string}
            </small>
          )}
        </Field>
        <div className="row">
          <button type="button" onClick={onClose}>
            Cancelar
          </button>
          <button type="submit">Crear</button>
        </div>
      </form>
    </div>
  );
}
