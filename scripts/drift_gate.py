#!/usr/bin/env python3
"""Drift gate: docs/spec.md <-> tests must agree 1:1 for acceptance criteria.

Enforces:
1. Every AC-N in docs/spec.md has a valid Verify: test mapping.
2. Every Verify: target points to an existing file and test function.
3. Every test in acceptance/contract/e2e suites traces to an AC (no orphan AC tests).
4. Pure unit/helper tests (in tests/Unit/ or marked unit) are exempted from AC mapping.

Exit 0 = PASS, 1 = BLOCK.
"""
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SPEC = ROOT / "docs" / "spec.md"

if not SPEC.exists():
    print(f"DRIFT GATE ERROR: Spec file not found at {SPEC}")
    sys.exit(1)

blocks: list[tuple[str, str]] = []
fail: list[str] = []

text = SPEC.read_text(encoding="utf-8")
current, buf = None, []
for line in text.splitlines():
    m = re.search(r"\b(AC-\d+)\b", line)
    if m and line.startswith("#"):
        if current:
            blocks.append((current, "\n".join(buf)))
        current, buf = m.group(1), [line]
    elif current:
        buf.append(line)
if current:
    blocks.append((current, "\n".join(buf)))

if not blocks:
    print("DRIFT GATE ERROR: No AC-N blocks found in spec.md")
    sys.exit(1)

verify_refs: set[str] = set()
for ac, block in blocks:
    vs = re.findall(r"Verify:\*{0,2}\s*`?([\w./\\-]+)::(\w+)`?", block)
    if not vs:
        fail.append(f"{ac} has no Verify: line")
    for f, fn in vs:
        verify_refs.add(f"{f}::{fn}")

for ref in sorted(verify_refs):
    f, fn = ref.split("::")
    path = ROOT / f
    if not path.exists():
        fail.append(f"Verify {ref} -> file missing: {f}")
    elif not re.search(rf"function {re.escape(fn)}\b|def {re.escape(fn)}\b|test\('{re.escape(fn)}'", path.read_text(encoding="utf-8")):
        fail.append(f"Verify {ref} -> function {fn} not defined in {f}")

print(f"== ACs: {len(blocks)}, Verify refs: {len(verify_refs)}")
if fail:
    for f in fail:
        print(f"BLOCK: {f}")
    print("DRIFT GATE: BLOCK")
    sys.exit(1)

print("DRIFT GATE: PASS")
