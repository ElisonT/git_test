<?php
/**
 * app/Models/Torneo.php
 *
 * Modelo mínimo de la entidad Torneo. Se irá ampliando en las
 * próximas entregas con equipos, rondas, enfrentamientos y
 * resultados; por ahora alcanza para mostrar datos reales en
 * el perfil del usuario.
 */

require_once __DIR__ . '/../../config/database.php';

class Torneo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = obtenerConexion();
    }

    public function contarJugados(int $usuarioId): int
    {
        $consulta = $this->conexion->prepare(
            "SELECT COUNT(*) AS total
               FROM inscripciones i
               JOIN torneos t ON t.id = i.torneo_id
              WHERE i.usuario_id = :usuario_id
                AND t.estado = 'finalizado'"
        );
        $consulta->execute(['usuario_id' => $usuarioId]);
        return (int) $consulta->fetch()['total'];
    }

    public function contarActivos(int $usuarioId): int
    {
        $consulta = $this->conexion->prepare(
            "SELECT COUNT(*) AS total
               FROM inscripciones i
               JOIN torneos t ON t.id = i.torneo_id
              WHERE i.usuario_id = :usuario_id
                AND t.estado IN ('inscripciones_abiertas', 'en_curso')"
        );
        $consulta->execute(['usuario_id' => $usuarioId]);
        return (int) $consulta->fetch()['total'];
    }

    public function contarGanados(int $usuarioId): int
    {
        $consulta = $this->conexion->prepare(
            'SELECT COUNT(*) AS total FROM torneos WHERE ganador_id = :usuario_id'
        );
        $consulta->execute(['usuario_id' => $usuarioId]);
        return (int) $consulta->fetch()['total'];
    }

    /**
     * Torneos en los que participa un usuario (para el carrusel del perfil).
     * Incluye la cantidad de inscriptos de cada torneo con una subconsulta.
     */
    public function listarParticipando(int $usuarioId, int $limite = 6): array
    {
        $consulta = $this->conexion->prepare(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM inscripciones i2 WHERE i2.torneo_id = t.id) AS cantidad_participantes
               FROM torneos t
               JOIN inscripciones i ON i.torneo_id = t.id
              WHERE i.usuario_id = :usuario_id
              ORDER BY t.fecha_inicio DESC
              LIMIT :limite"
        );
        $consulta->bindValue('usuario_id', $usuarioId, PDO::PARAM_INT);
        $consulta->bindValue('limite', $limite, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll();
    }
}
