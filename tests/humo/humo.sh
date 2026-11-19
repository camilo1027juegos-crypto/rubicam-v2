#!/usr/bin/env bash
# ============================================================
# humo.sh — Pruebas de humo del panel contra una BD MySQL real
# Uso: tests/humo/humo.sh [URL_RAIZ]     (por defecto http://127.0.0.1:8080)
# Requiere: BD importada (rubicam_db.sql + dashboard_modulos.sql),
#           servidor PHP sirviendo la raíz del proyecto, y la variable
#           ADMIN_CLAVE con la contraseña del usuario admin.
# Sale con código 1 si alguna verificación falla (build rojo).
# ============================================================
set -uo pipefail
RAIZ="${1:-http://127.0.0.1:8080}"
BASE="$RAIZ/Panel"
COOKIES="$(mktemp)"
FALLOS=0

ok()    { echo "✅ $1"; }
falla() { echo "❌ $1"; FALLOS=$((FALLOS + 1)); }

# 1) Sin sesión, la raíz redirige al login
cod=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$BASE/index.php")
[[ "$cod" == 302*login.php ]] && ok "index.php sin sesión redirige al login" || falla "index.php sin sesión: '$cod'"

# 2) La página de login responde y trae el token CSRF
html=$(curl -s -c "$COOKIES" -b "$COOKIES" "$BASE/login.php")
csrf=$(grep -o 'name="csrf" value="[0-9a-f]*"' <<<"$html" | sed 's/.*value="//; s/"$//')
[[ ${#csrf} -eq 64 ]] && ok "login.php responde con token CSRF" || falla "login.php sin token CSRF"

# 3) Login rechazado sin CSRF
cod=$(curl -s -o /dev/null -w '%{http_code}' -c "$COOKIES" -b "$COOKIES" \
      --data-urlencode "usuario=admin" --data-urlencode "password=${ADMIN_CLAVE:-}" "$BASE/login.php")
[[ "$cod" == 200 ]] && ok "login sin CSRF no entra (se queda en el formulario)" || falla "login sin CSRF devolvió $cod"

# 4) Contraseña incorrecta no entra
cod=$(curl -s -o /dev/null -w '%{http_code}' -c "$COOKIES" -b "$COOKIES" \
      --data-urlencode "csrf=$csrf" --data-urlencode "usuario=admin" --data-urlencode "password=incorrecta" "$BASE/login.php")
[[ "$cod" == 200 ]] && ok "contraseña incorrecta es rechazada" || falla "contraseña incorrecta devolvió $cod"

# 5) Login correcto redirige al panel de administración
res=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' -c "$COOKIES" -b "$COOKIES" \
      --data-urlencode "csrf=$csrf" --data-urlencode "usuario=admin" --data-urlencode "password=${ADMIN_CLAVE:-}" "$BASE/login.php")
[[ "$res" == 302*admin/usuarios.php ]] && ok "login de admin correcto" || falla "login de admin: '$res'"

# 6) Con sesión: administración de usuarios responde
cod=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" "$BASE/admin/usuarios.php")
[[ "$cod" == 200 ]] && ok "admin/usuarios.php responde 200" || falla "admin/usuarios.php devolvió $cod"

# 7) Diagnóstico: todas las tablas existen
estado=$(curl -s -b "$COOKIES" "$BASE/estado.php")
grep -q "Todo en orden" <<<"$estado" && ok "estado.php: todas las tablas existen" || falla "estado.php reporta tablas faltantes"

# 8) API del dashboard: escribe y lee un documento
api() { curl -s -b "$COOKIES" -H 'Content-Type: application/json' -d "$1" "$BASE/ws-api.php"; }
w=$(api '{"op":"set","col":"tareas","id":"humo-1","data":{"titulo":"Prueba de humo","hecha":false,"nota":"extra"}}')
r=$(api '{"op":"list","col":"tareas"}')
grep -q '"ok":true' <<<"$w" && grep -q 'Prueba de humo' <<<"$r" && grep -q '"nota":"extra"' <<<"$r" \
  && ok "ws-api.php guarda y lee una tarea" || falla "ws-api.php: escritura='$w' lectura='${r:0:200}'"
api '{"op":"delete","col":"tareas","id":"humo-1"}' >/dev/null

# 9) API sin sesión responde 401
cod=$(curl -s -o /dev/null -w '%{http_code}' -H 'Content-Type: application/json' -d '{"op":"listall"}' "$BASE/ws-api.php")
[[ "$cod" == 401 ]] && ok "ws-api.php sin sesión devuelve 401" || falla "ws-api.php sin sesión devolvió $cod"

# 10) Sitio público y chat público
cod=$(curl -s -o /dev/null -w '%{http_code}' "$RAIZ/index.html")
[[ "$cod" == 200 ]] && ok "sitio público: index.html responde 200" || falla "index.html devolvió $cod"
cod=$(curl -s -o /dev/null -w '%{http_code}' -X GET "$RAIZ/chat/chat-publico.php")
[[ "$cod" == 405 ]] && ok "chat-publico.php rechaza GET (405)" || falla "chat-publico.php GET devolvió $cod"

rm -f "$COOKIES"
echo "---"
if [[ $FALLOS -gt 0 ]]; then echo "Pruebas de humo: $FALLOS fallo(s)"; exit 1; fi
echo "Pruebas de humo: todas OK"
