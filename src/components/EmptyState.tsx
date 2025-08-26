export default function EmptyState({
  title,
  action,
}: {
  title: string;
  action?: React.ReactNode;
}) {
  return (
    <div className="empty">
      <p>{title}</p>
      {action}
    </div>
  );
}
