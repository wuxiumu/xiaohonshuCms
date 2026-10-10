#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
fail(){ echo "✗ $1" >&2; exit 1; }; ok(){ echo "✓ $1"; }
command -v php >/dev/null || fail "需要 PHP"
[[ -f config.php && -f index.php && -f auth.php ]] || fail "缺少核心"
[[ -f .env.example ]] || fail "缺少 .env.example"
ok "核心文件齐全"
grep -E "'123456'|\"123456\"" config.php && fail "仍含弱口令" || ok "无弱口令字面量"
php -l config.php >/dev/null; php -l index.php >/dev/null; ok "PHP lint OK"
PORT="${SMOKE_PORT:-18091}"
php -S "127.0.0.1:${PORT}" -t "$ROOT" >/tmp/xhs-cms-smoke.log 2>&1 &
PID=$!; trap "kill $PID 2>/dev/null||true" EXIT
for i in $(seq 1 30); do curl -sf "http://127.0.0.1:${PORT}/" >/dev/null 2>&1 && break; sleep 0.15; done
curl -sf "http://127.0.0.1:${PORT}/" >/dev/null || fail "首页失败"; ok "服务可响应"
echo ""; echo "smoke: ok"
