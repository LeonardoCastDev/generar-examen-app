import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import Field from "@components/Field";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "@store/auth.store";

const Schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
});

type FormData = z.infer<typeof Schema>;

export default function LoginPage() {
  const {
    register: rfRegister,
    handleSubmit,
    formState: { errors },
  } = useForm<FormData>({ resolver: zodResolver(Schema) });
  const { login } = useAuth();
  const nav = useNavigate();

  const onSubmit = async (data: FormData) => {
    await login(data.email, data.password);
    nav("/dashboard");
  };

  return (
    <div className="center">
      <h1>Iniciar sesión</h1>
      <form onSubmit={handleSubmit(onSubmit)} className="card">
        <Field label="Correo">
          <input type="email" {...rfRegister("email")} />
          {errors.email && (
            <small className="error">{errors.email.message}</small>
          )}
        </Field>
        <Field label="Contraseña">
          <input type="password" {...rfRegister("password")} />
          {errors.password && (
            <small className="error">{errors.password.message}</small>
          )}
        </Field>
        <button type="submit">Entrar</button>
        <p>
          ¿No tienes cuenta? <Link to="/register">Regístrate</Link>
        </p>
      </form>
    </div>
  );
}
