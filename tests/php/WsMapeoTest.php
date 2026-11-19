<?php
use PHPUnit\Framework\TestCase;

final class WsMapeoTest extends TestCase
{
    private array $def = ['tabla' => 'ws_prueba', 'seccion' => 'tareas',
                          'cols' => ['titulo' => 's', 'hecha' => 'b', 'duracion' => 'i']];

    public function testFilaADocConvierteTipos(): void
    {
        $fila = ['id' => 't1', 'titulo' => 'Revisar DO', 'hecha' => '1', 'duracion' => '30', 'extra' => null];
        $this->assertSame(['titulo' => 'Revisar DO', 'hecha' => true, 'duracion' => 30], fila_a_doc($fila, $this->def));
    }

    public function testFilaADocOmiteNulosYMezclaExtra(): void
    {
        $fila = ['id' => 't1', 'titulo' => null, 'hecha' => 0, 'duracion' => null,
                 'extra' => '{"checklist":["a","b"],"id":"no-debe-quedar"}'];
        $this->assertSame(['checklist' => ['a', 'b'], 'hecha' => false], fila_a_doc($fila, $this->def));
    }

    public function testFilaADocIgnoraExtraCorrupto(): void
    {
        $fila = ['titulo' => 'x', 'hecha' => 1, 'extra' => '{no es json'];
        $this->assertSame(['titulo' => 'x', 'hecha' => true], fila_a_doc($fila, $this->def));
    }

    public function testDocAFilaSeparaColumnasYExtra(): void
    {
        $r = doc_a_fila('t9', ['titulo' => 123, 'hecha' => 'si', 'duracion' => '', 'id' => 'x', 'notas' => 'ñ'], $this->def);
        $this->assertSame(['id', 'titulo', 'hecha', 'duracion', 'extra'], $r['campos']);
        $this->assertSame(['t9', '123', 1, null, '{"notas":"ñ"}'], $r['valores']);
    }

    public function testDocAFilaSinExtra(): void
    {
        $r = doc_a_fila('t1', ['titulo' => 'a', 'hecha' => false, 'duracion' => 5], $this->def);
        $this->assertSame(['t1', 'a', 0, 5, null], $r['valores']);
    }

    public function testIdaYVueltaConservaElDocumento(): void
    {
        $doc = ['titulo' => 'Llamar cliente', 'hecha' => true, 'duracion' => 15, 'asistentes' => ['Ana', 'Luis']];
        $f = doc_a_fila('r1', $doc, $this->def);
        $fila = array_combine($f['campos'], $f['valores']);
        $this->assertEquals($doc, fila_a_doc($fila, $this->def));
    }

    public function testEsquemaEsConsistente(): void
    {
        $tablas = [];
        foreach (ws_schema() as $col => $def) {
            $this->assertMatchesRegularExpression('/^ws_[a-z_]+$/', $def['tabla'], "tabla de $col");
            $this->assertArrayHasKey('seccion', $def);
            foreach ($def['cols'] as $nombre => $tipo) {
                $this->assertContains($tipo, ['s', 'b', 'i'], "$col.$nombre tiene un tipo desconocido");
                $this->assertNotSame('id', $nombre, 'id no debe declararse como columna');
            }
            $tablas[] = $def['tabla'];
        }
        $this->assertSame($tablas, array_unique($tablas), 'dos colecciones no pueden compartir tabla');
    }

    public function testEsquemaCoincideConElSql(): void
    {
        $sql = file_get_contents(dirname(__DIR__, 2) . '/Panel/sql/dashboard_modulos.sql');
        foreach (ws_schema() as $col => $def) {
            $this->assertMatchesRegularExpression('/CREATE TABLE\s+`?' . $def['tabla'] . '`?\s*\(/i', $sql,
                "falta CREATE TABLE {$def['tabla']} en dashboard_modulos.sql");
        }
    }
}
