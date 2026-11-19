<?php
use PHPUnit\Framework\TestCase;

final class SeguridadTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_SERVER['SCRIPT_NAME'] = '/Panel/index.php';
    }

    public function testHEscapaHtml(): void
    {
        $this->assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', h('<script>alert("x")</script>'));
        $this->assertSame('O&#039;Neil &amp; Cía', h("O'Neil & Cía"));
        $this->assertSame('42', h(42));
        $this->assertSame('', h(null));
    }

    public function testCsrfTokenEsAleatorioYEstable(): void
    {
        $t = csrf_token();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $t);
        $this->assertSame($t, csrf_token(), 'el token se reutiliza dentro de la misma sesión');
        $_SESSION = [];
        $this->assertNotSame($t, csrf_token(), 'una sesión nueva recibe otro token');
    }

    public function testCsrfValido(): void
    {
        $t = csrf_token();
        $this->assertFalse(csrf_valido(), 'sin token en el formulario');
        $_POST['csrf'] = 'otro';
        $this->assertFalse(csrf_valido(), 'token incorrecto');
        $_POST['csrf'] = ['array'];
        $this->assertFalse(csrf_valido(), 'tipo inválido no debe provocar error');
        $_POST['csrf'] = $t;
        $this->assertTrue(csrf_valido());
        $_SESSION = [];
        $this->assertFalse(csrf_valido(), 'sin sesión');
    }

    public function testBaseRel(): void
    {
        $this->assertSame('', base_rel());
        $_SERVER['SCRIPT_NAME'] = '/Panel/admin/usuarios.php';
        $this->assertSame('../', base_rel());
    }

    public function testMapaPermisos(): void
    {
        $filas = [
            ['seccion' => 'tareas', 'puede_ver' => 1, 'puede_editar' => 0],
            ['seccion' => 'clientes', 'puede_ver' => '1', 'puede_editar' => '1'],
        ];
        $this->assertSame([
            'tareas'   => ['ver' => true, 'editar' => false],
            'clientes' => ['ver' => true, 'editar' => true],
        ], mapa_permisos($filas));
        $this->assertSame([], mapa_permisos([]));
    }

    public function testAdminPuedeTodo(): void
    {
        $this->assertTrue(permiso_permitido(true, [], 'reportes', 'ver'));
        $this->assertTrue(permiso_permitido(true, [], 'reportes', 'editar'));
    }

    public function testUsuarioSegunSuMapa(): void
    {
        $mapa = ['tareas' => ['ver' => true, 'editar' => false]];
        $this->assertTrue(permiso_permitido(false, $mapa, 'tareas', 'ver'));
        $this->assertFalse(permiso_permitido(false, $mapa, 'tareas', 'editar'));
        $this->assertFalse(permiso_permitido(false, $mapa, 'clientes', 'ver'), 'sección sin permiso = denegado');
        $this->assertFalse(permiso_permitido(false, $mapa, 'tareas', 'borrar'), 'tipo desconocido = denegado');
    }
}
