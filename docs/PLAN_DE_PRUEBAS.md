# Plan de Pruebas — Rubicam (resumen v1.0)

## Alcance
- **Backend PHP**: validación del chat público, límites anti-abuso, seguridad (CSRF, escape HTML), permisos por sección, conversión fila ↔ documento de la API del dashboard, configuración por variables de entorno.
- **Frontend JS**: motor de idiomas (ES/EN/DE), formulario de contacto con doble consentimiento, banner de cookies.
- **Integración / humo**: login, permisos de administrador, diagnóstico de tablas y API del dashboard contra MySQL real.

## Niveles y herramientas
| Nivel | Herramienta | Ubicación | Cantidad (v1.0) |
|---|---|---|---|
| Unitarias PHP | PHPUnit 9.6 | `tests/php/` | 51 pruebas |
| Integración PHP (BD SQLite en memoria) | PHPUnit | `tests/php/PermisosBdTest.php` | incluida arriba |
| Unitarias JS | Jest 30 + jsdom | `tests/js/` | 24 pruebas |
| Humo (MySQL 8.4) | Bash + curl | `tests/humo/humo.sh` | 11 verificaciones |

## Criterios de aceptación (umbrales de calidad)
| Criterio | Valor | Aplicado por |
|---|---|---|
| Sintaxis PHP | 0 errores | `php -l` |
| Análisis estático | 0 errores (ESLint recomendado; PHPStan nivel 0, sube a 5 en S2) | `npm run lint`, `phpstan` |
| Pruebas | 100 % en verde | PHPUnit / Jest |
| Cobertura de líneas PHP (módulos con lógica extraída) | ≥ 80 % | `tools/cobertura-minima.php` |
| Cobertura JS (líneas / ramas) | ≥ 80 % / ≥ 70 % | `coverageThreshold` de Jest |
| Humo | 11/11 verificaciones | `tests/humo/humo.sh` |

## Datos de prueba
Sin datos reales: el esquema se importa vacío, el único usuario es `admin` y su contraseña se
reemplaza en cada ejecución por un secreto (`ADMIN_CLAVE_STAGING`) o una clave aleatoria.

## Cobertura actual y subida progresiva
- Módulos con lógica extraída (`chat/lib`, `Panel/includes/seguridad.php`, `ws-mapeo.php`, `env.php`, `ws_schema.php`): **98,45 %**.
- Backend PHP completo: **11,28 %** (139 de 1.232 líneas ejecutables). Los *endpoints* (`ws-api.php`, `ws-chat.php`, `chat-publico.php`, `dashboard-nuevo.php`) aún mezclan lógica y salida HTTP.
- Plan: en cada sprint se extrae lógica de un *endpoint* a funciones probables y se amplía el alcance medido (ver Informe de CI).
