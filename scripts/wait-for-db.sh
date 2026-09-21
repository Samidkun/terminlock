#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$DIR"

echo "Waiting for postgres on port 5434..."
until docker compose exec -T db pg_isready -U terminlock -d terminlock >/dev/null 2>&1; do
  sleep 1
done

if ! docker compose exec -T db psql -U terminlock -d postgres -tAc \
    "SELECT 1 FROM pg_database WHERE datname='terminlock_test'" | grep -q 1; then
  echo "Creating database terminlock_test..."
  docker compose exec -T db psql -U terminlock -d postgres -c "CREATE DATABASE terminlock_test"
fi

echo "Database ready."
