<?php
/**
 * app/Models/Usuario.php
 *
 * Modelo (capa de datos) de la entidad Usuario.
 * Todas las consultas usan parámetros preparados (:nombre_param)
 * en vez de concatenar variables directamente en el SQL, para
 * evitar inyección SQL.
 */

require_once __DIR__ . '/../../config/database.php';

class Usuario
{
    private PDO $conexion;

    // Rol por defecto para quien se autoregistra desde register.php
    // (ver bd/sgdm_schema.sql: 3 = Participante).
    public const ROL_PARTICIPANTE = 3;

    public function __construct()
    {
        $this->conexion = obtenerConexion();
    }

    public function nombreUsuarioExiste(string $nombreUsuario): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT id FROM usuarios WHERE nombre_usuario = :nombre_usuario'
        );
        $consulta->execute(['nombre_usuario' => $nombreUsuario]);
        return (bool) $consulta->fetch();
    }

    public function emailExiste(string $email): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT id FROM usuarios WHERE email = :email'
        );
        $consulta->execute(['email' => $email]);
        return (bool) $consulta->fetch();
    }

    public function registrar(array $datos): bool
    {
        $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);

        $consulta = $this->conexion->prepare(
            'INSERT INTO usuarios
                (nombre_usuario, nombre_completo, email, celular, contrasena_hash, genero, rol_id)
             VALUES
                (:nombre_usuario, :nombre_completo, :email, :celular, :hash, :genero, :rol_id)'
        );

        return $consulta->execute([
            'nombre_usuario'  => $datos['nombre_usuario'],
            'nombre_completo' => $datos['nombre_completo'],
            'email'           => $datos['email'],
            'celular'         => $datos['celular'] ?: null,
            'hash'            => $hash,
            'genero'          => $datos['genero'],
            'rol_id'          => self::ROL_PARTICIPANTE,
        ]);
    }

    /**
     * El login acepta nombre de usuario O email en el mismo campo
     * (ver login.php: placeholder "Tu usuario o correo").
     */
    public function buscarPorLogin(string $login): array|false
    {
        $consulta = $this->conexion->prepare(
            'SELECT * FROM usuarios WHERE nombre_usuario = :login OR email = :login'
        );
        $consulta->execute(['login' => $login]);
        return $consulta->fetch();
    }

    public function buscarPorId(int $id): array|false
    {
        $consulta = $this->conexion->prepare('SELECT * FROM usuarios WHERE id = :id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch();
    }

    public function verificarContrasena(string $contrasenaPlano, string $hashGuardado): bool
    {
        return password_verify($contrasenaPlano, $hashGuardado);
    }
}
