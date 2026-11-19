<?php
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    private string $archivo;

    protected function setUp(): void
    {
        $this->archivo = tempnam(sys_get_temp_dir(), 'env');
        putenv('RUBICAM_PRUEBA'); putenv('RUBICAM_YA_DEFINIDA'); putenv('RUBICAM_BOOL');
    }

    protected function tearDown(): void
    {
        @unlink($this->archivo);
        putenv('RUBICAM_PRUEBA'); putenv('RUBICAM_YA_DEFINIDA'); putenv('RUBICAM_BOOL');
    }

    public function testEnvDevuelveValorODefecto(): void
    {
        $this->assertSame('defecto', env('RUBICAM_PRUEBA', 'defecto'));
        $this->assertNull(env('RUBICAM_PRUEBA'));
        putenv('RUBICAM_PRUEBA=valor');
        $this->assertSame('valor', env('RUBICAM_PRUEBA', 'defecto'));
    }

    public function testCargarEnvNoPisaVariablesExistentes(): void
    {
        putenv('RUBICAM_YA_DEFINIDA=del-sistema');
        file_put_contents($this->archivo, "# comentario\n\nRUBICAM_PRUEBA=\"con comillas\"\nRUBICAM_YA_DEFINIDA=del-archivo\nlinea-invalida\n");
        cargar_env($this->archivo);
        $this->assertSame('con comillas', env('RUBICAM_PRUEBA'));
        $this->assertSame('del-sistema', env('RUBICAM_YA_DEFINIDA'), 'los secretos del entorno tienen prioridad');
    }

    public function testCargarEnvArchivoInexistente(): void
    {
        cargar_env('/no/existe/.env');
        $this->assertNull(env('RUBICAM_PRUEBA'));
    }

    public function testEnvBool(): void
    {
        $this->assertTrue(env_bool('RUBICAM_BOOL', true));
        foreach (['1', 'true', 'TRUE', 'si', 'sí', 'on'] as $v) {
            putenv("RUBICAM_BOOL=$v");
            $this->assertTrue(env_bool('RUBICAM_BOOL', false), $v);
        }
        foreach (['0', 'false', 'no'] as $v) {
            putenv("RUBICAM_BOOL=$v");
            $this->assertFalse(env_bool('RUBICAM_BOOL', true), $v);
        }
        putenv('RUBICAM_BOOL=');
        $this->assertTrue(env_bool('RUBICAM_BOOL', true), 'vacío = valor por defecto');
    }
}
