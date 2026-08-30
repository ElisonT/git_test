<?php
/**
 * app/Controllers/UsuarioController.php
 *
 * Controlador: recibe los datos del formulario (ya en $_POST),
 * los valida, y coordina con el Modelo. No genera HTML: eso
 * queda en la Vista (register.php / login.php).
 */

require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioController
{
    private Usuario $modeloUsuario;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
    }

    /**
     * Procesa el formulario de registro.
     * @return string[] Lista de errores. Vacía si el registro salió bien.
     */
    public function procesarRegistro(array $datos): array
    {
        $errores = [];

        $nombreUsuario  = trim($datos['usuario'] ?? '');
        $nombreCompleto = trim($datos['nombre'] ?? '');
        $email          = trim($datos['email'] ?? '');
        $celular        = trim($datos['celular'] ?? '');
        $genero         = $datos['genero'] ?? 'otro';
        $contrasena     = $datos['password'] ?? '';
        $confirmacion   = $datos['confirm'] ?? '';

        // ---- Validaciones del lado del servidor ----
        // (el HTML ya valida en el navegador, pero eso se puede saltear,
        //  así que estas son las que realmente importan).
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nombreUsuario)) {
            $errores[] = 'El nombre de usuario solo puede tener letras, números y guion bajo.';
        }
        if ($nombreCompleto === '' || !str_contains(trim($nombreCompleto), ' ')) {
            $errores[] = 'Ingresá tu nombre y apellido.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if (strlen($contrasena) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($contrasena !== $confirmacion) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        // Solo se consulta la base si las validaciones básicas ya pasaron
        // (para no gastar una consulta con datos que ya sabemos inválidos).
        if (empty($errores)) {
            if ($this->modeloUsuario->nombreUsuarioExiste($nombreUsuario)) {
                $errores[] = 'Ese nombre de usuario ya está en uso.';
            }
            if ($this->modeloUsuario->emailExiste($email)) {
                $errores[] = 'Ya existe una cuenta registrada con ese correo.';
            }
        }

        if (empty($errores)) {
            $exito = $this->modeloUsuario->registrar([
                'nombre_usuario'  => $nombreUsuario,
                'nombre_completo' => $nombreCompleto,
                'email'           => $email,
                'celular'         => $celular,
                'genero'          => $genero,
                'contrasena'      => $contrasena,
            ]);

            if (!$exito) {
                $errores[] = 'No se pudo completar el registro. Probá de nuevo.';
            }
        }

        return $errores;
    }

    /**
     * Procesa el formulario de login.
     * @return string|null Mensaje de error, o null si el login fue exitoso.
     */
    public function procesarLogin(string $login, string $contrasena): ?string
    {
        $login = trim($login);

        if ($login === '' || $contrasena === '') {
            return 'Completá usuario/correo y contraseña.';
        }

        $usuario = $this->modeloUsuario->buscarPorLogin($login);

        if (!$usuario || !$this->modeloUsuario->verificarContrasena($contrasena, $usuario['contrasena_hash'])) {
            // Mensaje genérico a propósito: no decimos si falló el usuario
            // o la contraseña, para no facilitarle el trabajo a un atacante.
            return 'Usuario o contraseña incorrectos.';
        }

        if (!$usuario['activo']) {
            return 'Esta cuenta está suspendida. Contactate con un administrador.';
        }

        // Solo se guarda en sesión lo necesario; nunca la contraseña ni su hash.
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
        $_SESSION['usuario_rol']    = (int) $usuario['rol_id'];

        return null;
    }
}
