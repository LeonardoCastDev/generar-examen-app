import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import Field from "@components/Field";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "@store/auth.store";

const Schema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  password: z.string().min(6),
});

type FormData = z.infer<typeof Schema>;

export default function RegisterPage() {
  const {
    register: rfRegister,
    handleSubmit,
    formState: { errors },
  } = useForm<FormData>({ resolver: zodResolver(Schema) });
  const { register: doRegister } = useAuth();
  const nav = useNavigate();

  const onSubmit = async (data: FormData) => {
    await doRegister(data.name, data.email, data.password);
    nav("/dashboard");
  };

  return (
    <div className="center">
      <h1>Crear cuenta</h1>
      <form onSubmit={handleSubmit(onSubmit)} className="card">
        <Field label="Nombre">
          <input type="text" {...rfRegister("name")} />
          {errors.name && (
            <small className="error">{errors.name.message}</small>
          )}
        </Field>
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
        <button type="submit">Registrarse</button>
        <p>
          ¿Ya tienes cuenta? <Link to="/">Inicia sesión</Link>
        </p>
      </form>
    </div>
  );
}
