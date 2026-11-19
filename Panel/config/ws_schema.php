<?php
/* ============================================================
   ws_schema.php — Mapa de cada módulo (colección) a su tabla
   Tipos de columna:  s = texto · b = booleano (0/1) · i = número
   "seccion" enlaza con la tabla de permisos (ver/editar).
   Todo lo que no sea una columna se guarda en el JSON "extra".
   ============================================================ */
function ws_schema(): array {
    return [
        'tareas' => ['tabla'=>'ws_tareas', 'seccion'=>'tareas', 'cols'=>[
            'titulo'=>'s','area'=>'s','prioridad'=>'s','vence'=>'s','responsable'=>'s','ref'=>'s',
            'hecha'=>'b','completada'=>'s','creada'=>'s']],
        'reuniones' => ['tabla'=>'ws_reuniones', 'seccion'=>'reuniones', 'cols'=>[
            'titulo'=>'s','inicio'=>'s','duracion'=>'i','area'=>'s','lugar'=>'s','externos'=>'i','resumen'=>'s']],
        'cotizaciones' => ['tabla'=>'ws_cotizaciones', 'seccion'=>'cotizaciones', 'cols'=>[
            'numero'=>'s','estado'=>'s','cliente'=>'s','servicio'=>'s','valor'=>'i','vence'=>'s','responsable'=>'s','creada'=>'s']],
        'pendientes' => ['tabla'=>'ws_pendientes', 'seccion'=>'pendientes', 'cols'=>[
            'titulo'=>'s','esperando'=>'s','desde'=>'s','ref'=>'s','responsable'=>'s','resuelto'=>'b']],
        'operaciones' => ['tabla'=>'ws_operaciones', 'seccion'=>'operaciones', 'cols'=>[
            'numero'=>'s','ciudad'=>'s','tipo'=>'s','estado'=>'s','apertura'=>'s','servicio'=>'s','cliente'=>'s','responsable'=>'s','cotizacion'=>'s']],
        'clientes' => ['tabla'=>'ws_clientes', 'seccion'=>'clientes', 'cols'=>[
            'nombre'=>'s','nit'=>'s','ciudad'=>'s','contacto'=>'s','cargo'=>'s','email'=>'s','telefono'=>'s']],
        'documentos' => ['tabla'=>'ws_documentos', 'seccion'=>'documentos', 'cols'=>[
            'tipo'=>'s','estado'=>'s','nombre'=>'s','operacion'=>'s','fecha'=>'s','responsable'=>'s']],
        'solicitudes' => ['tabla'=>'ws_solicitudes', 'seccion'=>'solicitudes', 'cols'=>[
            'tipo'=>'s','solicitante'=>'s','estado'=>'s','desde'=>'s','hasta'=>'s','creada'=>'s']],
        'equipo' => ['tabla'=>'ws_equipo', 'seccion'=>'equipos', 'cols'=>[
            'nombre'=>'s','iniciales'=>'s','area'=>'s','rol'=>'s','color'=>'s','cumple'=>'s']],
        'avisos' => ['tabla'=>'ws_avisos', 'seccion'=>'informacion', 'cols'=>[
            'texto'=>'s','fijado'=>'b','fecha'=>'s']],
        // 'seccion' => null  => disponible para cualquier usuario con sesión
        'actividad' => ['tabla'=>'ws_actividad', 'seccion'=>null, 'cols'=>[
            'texto'=>'s','uid'=>'s','fecha'=>'s']],
        'progreso' => ['tabla'=>'ws_progreso', 'seccion'=>'guias', 'cols'=>[]],
    ];
}
