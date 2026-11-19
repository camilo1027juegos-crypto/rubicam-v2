<?php
use PHPUnit\Framework\TestCase;

/* Prueba de integración: permisos.php contra una BD SQLite en memoria
   (mismas consultas que en MySQL). Verifica que conexion.php reutiliza $pdo. */
final class PermisosBdTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $pdo;
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $pdo->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, usuario TEXT, rol TEXT, activo INT)');
        $pdo->exec('CREATE TABLE permisos (usuario_id INT, seccion TEXT, puede_ver INT, puede_editar INT)');
        $pdo->exec('CREATE TABLE secciones (clave TEXT, nombre TEXT, grupo TEXT, orden INT)');
        $pdo->exec("INSERT INTO usuarios VALUES (1,'admin','admin',1),(2,'ana','usuario',1),(3,'ex','usuario',0)");
        $pdo->exec("INSERT INTO permisos VALUES (2,'tareas',1,1),(2,'clientes',1,0)");
        $pdo->exec("INSERT INTO secciones VALUES ('clientes','Clientes','Gestión',2),('tareas','Tareas','Principal',1),('reportes','Reportes','Gestión',3)");
        require_once dirname(__DIR__, 2) . '/Panel/includes/permisos.php';
    }

    public function testCargarPermisosDeUnUsuario(): void
    {
        $this->assertSame([
            'tareas'   => ['ver' => true, 'editar' => true],
            'clientes' => ['ver' => true, 'editar' => false],
        ], cargar_permisos(2));
        $this->assertSame([], cargar_permisos(99));
    }

    public function testSeccionesOrdenadas(): void
    {
        $this->assertSame(['tareas', 'clientes', 'reportes'], array_column(secciones_todas(), 'clave'));
    }

    public function testSinSesionNoHayUsuario(): void
    {
        $_SESSION = [];
        $this->assertNull(usuario_actual());
        $this->assertFalse(es_admin());
    }
}
