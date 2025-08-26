import { useState } from "react";
import type { Question, Option, QuestionType } from "@types/domain";

interface Props {
  initial?: Partial<Question>;
  onSave: (q: Omit<Question, "id" | "exam_id">) => void;
}

export default function QuestionForm({ initial, onSave }: Props) {
  const [type, setType] = useState<QuestionType>(initial?.type || "open");
  const [text, setText] = useState(initial?.question_text || "");
  const [options, setOptions] = useState<
    Pick<Option, "option_text" | "is_correct">[]
  >(
    () =>
      initial?.options?.map((o) => ({
        option_text: o.option_text,
        is_correct: o.is_correct,
      })) || [{ option_text: "", is_correct: false }]
  );

  const addOption = () =>
    setOptions((opts) => [...opts, { option_text: "", is_correct: false }]);
  const removeOption = (idx: number) =>
    setOptions((opts) => opts.filter((_, i) => i !== idx));

  const submit = () => {
    const payload: Omit<Question, "id" | "exam_id"> = {
      type,
      question_text: text,
      options:
        type === "multiple"
          ? options.map((o, i) => ({
              id: i + 1,
              question_id: 0,
              option_text: o.option_text,
              is_correct: o.is_correct,
            }))
          : undefined,
    };
    onSave(payload);
  };

  return (
    <div className="card">
      <div className="row">
        <label>
          Tipo:
          <select value={type} onChange={(e) => setType(e.target.value as any)}>
            <option value="open">Respuesta abierta</option>
            <option value="multiple">Opción múltiple</option>
          </select>
        </label>
      </div>
      <label className="field">
        <span>Pregunta</span>
        <textarea
          value={text}
          onChange={(e) => setText(e.target.value)}
          rows={3}
        />
      </label>
      {type === "multiple" && (
        <div>
          <h4>Incisos</h4>
          {options.map((o, i) => (
            <div key={i} className="row">
              <input
                placeholder={`Inciso ${String.fromCharCode(65 + i)})`}
                value={o.option_text}
                onChange={(e) =>
                  setOptions((opts) =>
                    opts.map((oo, ii) =>
                      ii === i ? { ...oo, option_text: e.target.value } : oo
                    )
                  )
                }
              />
              <label className="row">
                <input
                  type="checkbox"
                  checked={o.is_correct}
                  onChange={(e) =>
                    setOptions((opts) =>
                      opts.map((oo, ii) =>
                        ii === i ? { ...oo, is_correct: e.target.checked } : oo
                      )
                    )
                  }
                />
                Correcta
              </label>
              <button className="danger" onClick={() => removeOption(i)}>
                Quitar
              </button>
            </div>
          ))}
          <button onClick={addOption}>Agregar inciso</button>
        </div>
      )}
      <div className="row end">
        <button onClick={submit}>Guardar pregunta</button>
      </div>
    </div>
  );
}
