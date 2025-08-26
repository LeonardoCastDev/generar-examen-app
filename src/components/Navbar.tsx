import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "@store/auth.store";
import ConfirmDialog from "./ConfirmDialog";
import { useState } from "react";

export default function Navbar() {
  const { user, logout, deleteAccount } = useAuth();
  const [confirmLogout, setConfirmLogout] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const nav = useNavigate();

  return (
    <header className="nav">
      <Link to="/dashboard" className="brand">
        Exam Generator
      </Link>
      <nav>
        {user && (
          <>
            <span className="user">{user.name}</span>
            <button onClick={() => setConfirmLogout(true)}>
              Cerrar sesión
            </button>
            <button className="danger" onClick={() => setConfirmDelete(true)}>
              Borrar cuenta
            </button>
          </>
        )}
      </nav>
      <ConfirmDialog
        open={confirmLogout}
        title="¿Cerrar sesión?"
        description="¿Estás seguro que deseas cerrar sesión?"
        onCancel={() => setConfirmLogout(false)}
        onConfirm={async () => {
          await logout();
          nav("/");
        }}
      />
      <ConfirmDialog
        open={confirmDelete}
        title="Borrar cuenta"
        description="Esto eliminará permanentemente tu cuenta y exámenes."
        onCancel={() => setConfirmDelete(false)}
        onConfirm={async () => {
          await deleteAccount();
          nav("/");
        }}
      />
    </header>
  );
}
