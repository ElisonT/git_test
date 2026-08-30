-- ============================================================
-- sgdm_dcl.sql
-- Control de acceso a la base de datos (DCL).
-- La aplicación web NUNCA se conecta como root: usa un usuario
-- de MySQL aparte, con solo los permisos que necesita para
-- funcionar (principio de menor privilegio). Esto también se
-- documenta en la parte de Ciberseguridad de la letra.
-- ============================================================

-- Cambiá 'CAMBIAR_ESTA_CONTRASEÑA' por una contraseña real y
-- fuerte antes de ejecutar esto en el servidor. Esa misma
-- contraseña va después en config/database.php.
CREATE USER IF NOT EXISTS 'sgdm_app'@'localhost'
    IDENTIFIED BY 'CAMBIAR_ESTA_CONTRASEÑA';

-- Solo lectura/escritura de datos (CRUD). Nada de DROP, ALTER,
-- CREATE ni GRANT: si el sitio tiene una falla de seguridad,
-- el usuario de la app no puede borrar tablas ni escalar permisos.
GRANT SELECT, INSERT, UPDATE, DELETE ON sgdm.* TO 'sgdm_app'@'localhost';

FLUSH PRIVILEGES;
