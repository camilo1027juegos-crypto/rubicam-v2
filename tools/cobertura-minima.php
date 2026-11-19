<?php
/* ============================================================
   cobertura-minima.php — Umbral de calidad de cobertura (quality gate)
   Uso: php tools/cobertura-minima.php build/coverage/clover.xml 80
   Lee el reporte Clover de PHPUnit y termina con código 1 (build rojo)
   si el porcentaje de líneas cubiertas es menor al mínimo.
   ============================================================ */
[$_, $archivo, $minimo] = $argv + [null, null, null];
if (!$archivo || !is_readable($archivo) || !is_numeric($minimo)) {
    fwrite(STDERR, "Uso: php tools/cobertura-minima.php <clover.xml> <porcentaje>\n");
    exit(2);
}
$xml = simplexml_load_file($archivo);
$m = $xml->project->metrics;
$total = (int)$m['statements'];
$cubiertas = (int)$m['coveredstatements'];
$pct = $total > 0 ? round($cubiertas / $total * 100, 2) : 0.0;

echo "Cobertura de líneas PHP: $pct % ($cubiertas de $total) · mínimo exigido: $minimo %\n";
if ($resumen = getenv('GITHUB_STEP_SUMMARY')) {
    file_put_contents($resumen, "### Cobertura PHP: **$pct %** (mínimo $minimo %)\n", FILE_APPEND);
}
if ($pct < (float)$minimo) {
    fwrite(STDERR, "❌ Umbral de calidad NO cumplido: la cobertura ($pct %) es menor que $minimo %.\n");
    exit(1);
}
echo "✅ Umbral de cobertura cumplido.\n";
