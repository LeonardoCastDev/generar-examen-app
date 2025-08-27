import { Suspense } from "react";
import { RouterProvider } from "react-router-dom";
import { router } from "./routes";

export default function App() {
  return (
    <div className="app-container">
      <Suspense
        fallback={
          <div style={{ textAlign: "center", marginTop: "2rem" }}>
            <p>Cargando aplicación...</p>
          </div>
        }
      >
        <RouterProvider router={router} />
      </Suspense>
    </div>
  );
}
