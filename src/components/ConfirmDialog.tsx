interface Props {
  open: boolean;
  title: string;
  description?: string;
  onCancel: () => void;
  onConfirm: () => void | Promise<void>;
}
export default function ConfirmDialog({
  open,
  title,
  description,
  onCancel,
  onConfirm,
}: Props) {
  if (!open) return null;
  return (
    <div className="modal">
      <div className="card">
        <h3>{title}</h3>
        {description && <p>{description}</p>}
        <div className="row">
          <button onClick={onCancel}>Cancelar</button>
          <button className="danger" onClick={() => void onConfirm()}>
            Confirmar
          </button>
        </div>
      </div>
    </div>
  );
}
