<?php
require_once 'config.php';

class ExamGenerator {
    private $pdo;

    public function __construct() {
        global $pdo;
        if (!$pdo) {
            throw new Exception("Error: No hay conexión a la base de datos disponible");
        }
        $this->pdo = $pdo;
    }

    public function getMaterias() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM materias WHERE activa = 1 ORDER BY nombre_materia");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obteniendo materias: " . $e->getMessage());
            return [];
        }
    }

    public function generarExamen($id_materia, $titulo, $descripcion, $total_preguntas, $dificultad, $id_usuario) {
        try {
            $this->pdo->beginTransaction();

            // Validar datos de entrada
            if (empty($id_materia) || empty($titulo) || empty($total_preguntas) || empty($id_usuario)) {
                throw new Exception("Faltan datos obligatorios para generar el examen");
            }

            // Verificar que la materia existe
            $stmt = $this->pdo->prepare("SELECT id_materia FROM materias WHERE id_materia = ? AND activa = 1");
            $stmt->execute([$id_materia]);
            if (!$stmt->fetch()) {
                throw new Exception("La materia seleccionada no existe o no está activa");
            }

            // Crear el examen
            $stmt = $this->pdo->prepare("
                INSERT INTO examenes_generados (id_usuario, id_materia, titulo, descripcion, total_preguntas, nivel_dificultad, fecha_generacion, activo)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)
            ");
            
            $stmt->execute([
                $id_usuario, 
                $id_materia, 
                $titulo, 
                $descripcion ?: null, 
                intval($total_preguntas), 
                $dificultad
            ]);
            
            $id_examen = $this->pdo->lastInsertId();

            if (!$id_examen) {
                throw new Exception("Error al crear el registro del examen");
            }

            // Obtener preguntas según dificultad
            $preguntas = $this->obtenerPreguntasParaExamen($id_materia, $total_preguntas, $dificultad);

            if (empty($preguntas)) {
                throw new Exception("No hay preguntas disponibles para esta materia");
            }

            if (count($preguntas) < $total_preguntas) {
                // Si no hay suficientes preguntas, usar las que hay disponibles
                $total_preguntas = count($preguntas);
                
                // Actualizar el examen con el número real de preguntas
                $stmt = $this->pdo->prepare("UPDATE examenes_generados SET total_preguntas = ? WHERE id_examen = ?");
                $stmt->execute([$total_preguntas, $id_examen]);
            }

            // Insertar preguntas del examen
            $stmt = $this->pdo->prepare("
                INSERT INTO preguntas_examen (id_examen, id_pregunta_banco, numero_pregunta, puntuacion_asignada)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($preguntas as $index => $pregunta) {
                $puntuacion = isset($pregunta['puntuacion']) ? $pregunta['puntuacion'] : 1.0;
                
                $stmt->execute([
                    $id_examen, 
                    $pregunta['id_pregunta_banco'], 
                    $index + 1, 
                    $puntuacion
                ]);
            }

            // Registrar en historial
            $this->registrarHistorial($id_usuario, $id_materia, 'generar_examen', [
                'id_examen' => $id_examen,
                'total_preguntas' => $total_preguntas,
                'dificultad' => $dificultad
            ]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Examen generado exitosamente con ' . $total_preguntas . ' preguntas',
                'id_examen' => $id_examen,
                'total_preguntas' => $total_preguntas
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error generando examen: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al generar el examen: ' . $e->getMessage()
            ];
        }
    }

    private function obtenerPreguntasParaExamen($id_materia, $total_preguntas, $dificultad) {
        try {
            $where_dificultad = "";
            $params = [$id_materia];

            if ($dificultad !== 'mixto') {
                $where_dificultad = "AND nivel_dificultad = ?";
                $params[] = $dificultad;
            }

            $sql = "
                SELECT bp.*, m.nombre_materia 
                FROM banco_preguntas bp
                JOIN materias m ON bp.id_materia = m.id_materia
                WHERE bp.id_materia = ? AND bp.activa = 1 $where_dificultad
                ORDER BY RAND()
                LIMIT ?
            ";
            
            $params[] = intval($total_preguntas);
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $preguntas = $stmt->fetchAll();

            return $preguntas;

        } catch (PDOException $e) {
            error_log("Error obteniendo preguntas: " . $e->getMessage());
            return [];
        }
    }

    public function getExamenesUsuario($id_usuario, $limite = null) {
        try {
            $sql = "
                SELECT eg.*, m.nombre_materia 
                FROM examenes_generados eg
                JOIN materias m ON eg.id_materia = m.id_materia
                WHERE eg.id_usuario = ? AND eg.activo = 1
                ORDER BY eg.fecha_generacion DESC
            ";
            
            if ($limite && is_numeric($limite)) {
                $sql .= " LIMIT " . intval($limite);
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obteniendo exámenes del usuario: " . $e->getMessage());
            return [];
        }
    }

    public function getExamenCompleto($id_examen, $id_usuario) {
        try {
            // Obtener datos básicos del examen
            $stmt = $this->pdo->prepare("
                SELECT eg.*, m.nombre_materia, u.nombre, u.apellido
                FROM examenes_generados eg
                JOIN materias m ON eg.id_materia = m.id_materia
                JOIN usuarios u ON eg.id_usuario = u.id_usuario
                WHERE eg.id_examen = ? AND eg.id_usuario = ? AND eg.activo = 1
            ");
            
            $stmt->execute([$id_examen, $id_usuario]);
            $examen = $stmt->fetch();

            if (!$examen) {
                return null;
            }

            // Obtener preguntas del examen
            $stmt = $this->pdo->prepare("
                SELECT pe.*, bp.enunciado, bp.tipo_pregunta, bp.nivel_dificultad
                FROM preguntas_examen pe
                JOIN banco_preguntas bp ON pe.id_pregunta_banco = bp.id_pregunta_banco
                WHERE pe.id_examen = ?
                ORDER BY pe.numero_pregunta
            ");
            
            $stmt->execute([$id_examen]);
            $preguntas = $stmt->fetchAll();

            // Obtener opciones para cada pregunta
            foreach ($preguntas as &$pregunta) {
                if ($pregunta['tipo_pregunta'] === 'multiple') {
                    $stmt = $this->pdo->prepare("
                        SELECT * FROM opciones_banco
                        WHERE id_pregunta_banco = ?
                        ORDER BY letra_opcion
                    ");
                    $stmt->execute([$pregunta['id_pregunta_banco']]);
                    $pregunta['opciones'] = $stmt->fetchAll();
                }

                if ($pregunta['tipo_pregunta'] === 'abierta' || $pregunta['tipo_pregunta'] === 'verdadero_falso') {
                    $stmt = $this->pdo->prepare("
                        SELECT * FROM respuestas_banco
                        WHERE id_pregunta_banco = ?
                    ");
                    $stmt->execute([$pregunta['id_pregunta_banco']]);
                    $pregunta['respuestas'] = $stmt->fetchAll();
                }
            }

            $examen['preguntas'] = $preguntas;
            return $examen;

        } catch (PDOException $e) {
            error_log("Error obteniendo examen completo: " . $e->getMessage());
            return null;
        }
    }

    public function eliminarExamen($id_examen, $id_usuario) {
        try {
            $this->pdo->beginTransaction();

            // Verificar que el examen pertenece al usuario
            $stmt = $this->pdo->prepare("SELECT id_examen FROM examenes_generados WHERE id_examen = ? AND id_usuario = ?");
            $stmt->execute([$id_examen, $id_usuario]);
            
            if (!$stmt->fetch()) {
                throw new Exception("Examen no encontrado o no tienes permisos para eliminarlo");
            }

            // Marcar como inactivo en lugar de eliminar físicamente
            $stmt = $this->pdo->prepare("UPDATE examenes_generados SET activo = 0 WHERE id_examen = ? AND id_usuario = ?");
            $stmt->execute([$id_examen, $id_usuario]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Examen eliminado correctamente'
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error eliminando examen: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el examen: ' . $e->getMessage()
            ];
        }
    }

    private function registrarHistorial($id_usuario, $id_materia, $tipo_accion, $detalles = []) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO historial_generaciones (id_usuario, id_materia, tipo_accion, detalles, fecha_accion)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $id_usuario,
                $id_materia,
                $tipo_accion,
                json_encode($detalles)
            ]);
        } catch (PDOException $e) {
            // No hacer que falle toda la operación por un error de logging
            error_log("Error registrando historial: " . $e->getMessage());
        }
    }

    public function getEstadisticasUsuario($id_usuario) {
        try {
            $stats = [];

            // Total de exámenes generados
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM examenes_generados WHERE id_usuario = ? AND activo = 1");
            $stmt->execute([$id_usuario]);
            $stats['total_examenes'] = $stmt->fetchColumn();

            // Exámenes por materia
            $stmt = $this->pdo->prepare("
                SELECT m.nombre_materia, COUNT(*) as cantidad
                FROM examenes_generados eg
                JOIN materias m ON eg.id_materia = m.id_materia
                WHERE eg.id_usuario = ? AND eg.activo = 1
                GROUP BY eg.id_materia, m.nombre_materia
                ORDER BY cantidad DESC
            ");
            $stmt->execute([$id_usuario]);
            $stats['por_materia'] = $stmt->fetchAll();

            // Exámenes por dificultad
            $stmt = $this->pdo->prepare("
                SELECT nivel_dificultad, COUNT(*) as cantidad
                FROM examenes_generados
                WHERE id_usuario = ? AND activo = 1
                GROUP BY nivel_dificultad
                ORDER BY cantidad DESC
            ");
            $stmt->execute([$id_usuario]);
            $stats['por_dificultad'] = $stmt->fetchAll();

            return $stats;

        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }

    public function verificarIntegridad() {
        try {
            $problemas = [];

            // Verificar que existan materias activas
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM materias WHERE activa = 1");
            $materias_activas = $stmt->fetchColumn();
            if ($materias_activas == 0) {
                $problemas[] = "No hay materias activas en el sistema";
            }

            // Verificar que existan preguntas
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM banco_preguntas WHERE activa = 1");
            $preguntas_activas = $stmt->fetchColumn();
            if ($preguntas_activas == 0) {
                $problemas[] = "No hay preguntas activas en el banco";
            }

            // Verificar preguntas huérfanas (sin materia)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM banco_preguntas bp 
                LEFT JOIN materias m ON bp.id_materia = m.id_materia 
                WHERE m.id_materia IS NULL OR m.activa = 0
            ");
            $preguntas_huerfanas = $stmt->fetchColumn();
            if ($preguntas_huerfanas > 0) {
                $problemas[] = "Existen {$preguntas_huerfanas} preguntas asociadas a materias inactivas";
            }

            return [
                'valido' => empty($problemas),
                'problemas' => $problemas,
                'estadisticas' => [
                    'materias_activas' => $materias_activas,
                    'preguntas_activas' => $preguntas_activas
                ]
            ];

        } catch (PDOException $e) {
            error_log("Error verificando integridad: " . $e->getMessage());
            return [
                'valido' => false,
                'problemas' => ['Error al verificar la integridad de la base de datos'],
                'estadisticas' => []
            ];
        }
    }
}
?>