#!/usr/bin/env bash
# local-ci.sh — local test runner and gate validator for TerminLock
set -uo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$DIR"

FAST=0
for arg in "$@"; do
  [ "$arg" = "--fast" ] && FAST=1
done

echo "================================================================"
echo " TerminLock local-ci"
echo "================================================================"

fail=0

# 1. Secret scan
echo "--- 1. secrets ---"
if grep -rnEI '(AKIA[0-9A-Z]{16}|ghp_[0-9a-zA-Z]{36}|sk_live_[0-9a-zA-Z]{24})' app/ routes/ config/ 2>/dev/null; then
  echo "FAIL: secret detected"
  fail=1
else
  echo "ok secrets"
fi

# 2. PHP syntax check
echo "--- 2. php:syntax ---"
find app routes config -name "*.php" -exec php -l {} \; >/dev/null
echo "ok php:syntax"

# 3. PHPUnit test suite
echo "--- 3. php:test ---"
if ./vendor/bin/phpunit; then
  echo "ok php:test"
else
  echo "FAIL php:test"
  fail=1
fi

# 4. Vite build
echo "--- 4. node:build ---"
if npm run build; then
  echo "ok node:build"
else
  echo "FAIL node:build"
  fail=1
fi

# 5. Drift gate
echo "--- 5. drift:gate ---"
if bash scripts/drift-gate.sh; then
  echo "ok drift:gate"
else
  echo "FAIL drift:gate"
  fail=1
fi

if [ "$fail" -eq 0 ]; then
  echo "================================================================"
  echo " LOCAL-CI: ALL GREEN"
  echo "================================================================"
  exit 0
else
  echo "================================================================"
  echo " LOCAL-CI: FAILURES DETECTED"
  echo "================================================================"
  exit 1
fi
