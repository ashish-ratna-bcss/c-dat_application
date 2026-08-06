#!/usr/bin/env python3
"""
Archive git-tracked URL-alias symlinks into old_versionfiles/.

- Reads symlink paths/targets from the git index (authoritative casing).
- Writes old_versionfiles/MANIFEST.csv (path,target,resolved_target).
- Creates case-safe symlink files under old_versionfiles/links/ so macOS
  case-insensitive volumes can store HOME.HTML and home.html as distinct entries.
- Does not modify canonical real files at the web root.

Safe name encoding: uppercase letters are prefixed with '^'
  HOME.html -> ^H^O^M^E.html
  home.html -> home.html
"""
from __future__ import annotations

import argparse
import csv
import os
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
ARCHIVE = ROOT / "old_versionfiles"
LINKS = ARCHIVE / "links"
MANIFEST = ARCHIVE / "MANIFEST.csv"
EXTS = (".html", ".htm", ".php", ".css", ".js")


def interesting(path: str) -> bool:
    low = path.lower()
    return low.endswith(EXTS) or low.endswith(".php.bak")


def case_safe(name: str) -> str:
    """Encode so case variants remain distinct on case-insensitive filesystems."""
    out: list[str] = []
    for ch in name:
        if ch == "/":
            out.append("__")
        elif ch == "^":
            out.append("^^")
        elif ch.isupper():
            out.append("^" + ch)
        else:
            out.append(ch)
    return "".join(out)


def resolve_rel(link_path: str, target: str) -> str:
    parent = Path(link_path).parent
    parts: list[str] = []
    for p in (parent / target).as_posix().split("/"):
        if p in ("", "."):
            continue
        if p == "..":
            if parts:
                parts.pop()
        else:
            parts.append(p)
    return "/".join(parts)


def rel_to_canonical(archive_link_file: Path, canonical_rel: str) -> str:
    """Relative path from archive symlink file to canonical file at repo root."""
    target_abs = (ROOT / canonical_rel).resolve()
    return os.path.relpath(target_abs, start=archive_link_file.parent)


def git_symlinks() -> list[tuple[str, str]]:
    out = subprocess.check_output(["git", "ls-files", "-s"], cwd=ROOT, text=True)
    meta: list[tuple[str, str]] = []  # sha, path
    for line in out.splitlines():
        mode, sha, rest = line.split(" ", 2)
        path = rest.split("\t", 1)[1]
        if mode == "120000" and interesting(path):
            meta.append((sha, path))
    if not meta:
        return []

    # Batch-read targets without deadlocking the pipe.
    import threading

    targets: list[str] = []
    proc = subprocess.Popen(
        ["git", "cat-file", "--batch"],
        cwd=ROOT,
        stdin=subprocess.PIPE,
        stdout=subprocess.PIPE,
    )
    assert proc.stdin and proc.stdout

    def writer() -> None:
        assert proc.stdin
        for sha, _ in meta:
            proc.stdin.write((sha + "\n").encode())
        proc.stdin.close()

    t = threading.Thread(target=writer)
    t.start()
    raw = proc.stdout.read()
    t.join()
    proc.wait()

    i = 0
    while i < len(raw):
        nl = raw.find(b"\n", i)
        if nl < 0:
            break
        header = raw[i:nl].decode()
        parts = header.split()
        if len(parts) < 3 or parts[1] == "missing":
            targets.append("<missing>")
            i = nl + 1
            continue
        size = int(parts[2])
        content = raw[nl + 1 : nl + 1 + size].decode().rstrip("\n")
        targets.append(content)
        i = nl + 1 + size
        if i < len(raw) and raw[i : i + 1] == b"\n":
            i += 1

    if len(targets) != len(meta):
        raise SystemExit(f"cat-file mismatch: {len(targets)} targets for {len(meta)} symlinks")
    return [(path, target) for (_, path), target in zip(meta, targets)]


def real_paths() -> set[str]:
    """All non-symlink git paths (any extension) — alias targets may be .jpg/.zip/etc."""
    out = subprocess.check_output(["git", "ls-files", "-s"], cwd=ROOT, text=True)
    reals: set[str] = set()
    for line in out.splitlines():
        mode, _, rest = line.split(" ", 2)
        path = rest.split("\t", 1)[1]
        if mode in ("100644", "100755"):
            reals.add(path)
    return reals


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    pairs = git_symlinks()
    reals = real_paths()
    print(f"symlinks to archive: {len(pairs)}")
    print(f"real interesting files: {len(reals)}")

    if args.dry_run:
        for path, target in pairs[:20]:
            print(f"  {path} -> {target} (resolved {resolve_rel(path, target)})")
        print("…")
        return 0

    if ARCHIVE.exists():
        # Rebuild links tree only; keep folder
        if LINKS.exists():
            for p in LINKS.rglob("*"):
                if p.is_symlink() or p.is_file():
                    p.unlink()
            for p in sorted(LINKS.rglob("*"), reverse=True):
                if p.is_dir():
                    try:
                        p.rmdir()
                    except OSError:
                        pass
    LINKS.mkdir(parents=True, exist_ok=True)

    rows: list[dict[str, str]] = []
    created = 0
    skipped = 0
    fold_map = {p.lower(): p for p in reals}
    for path, target in pairs:
        resolved = resolve_rel(path, target) if target != "<missing>" else ""
        # Prefer git-known real path; fall back to casefold match.
        canonical = resolved if resolved in reals else (target if target in reals else "")
        if not canonical:
            canonical = fold_map.get(resolved.lower(), fold_map.get(target.lower(), ""))
        safe = case_safe(path)
        link_path = LINKS / safe
        link_path.parent.mkdir(parents=True, exist_ok=True)
        # Trust git inventory: create archive link even if macOS exists() is confused.
        if canonical and canonical in reals:
            rel = rel_to_canonical(link_path, canonical)
            if link_path.exists() or link_path.is_symlink():
                link_path.unlink()
            link_path.symlink_to(rel)
            created += 1
            status = "ok"
        else:
            skipped += 1
            status = "canonical_missing"
            rel = ""
        rows.append(
            {
                "symlink_path": path,
                "symlink_target": target,
                "resolved_target": resolved,
                "canonical": canonical,
                "archive_link": f"links/{safe}",
                "archive_rel_target": rel,
                "status": status,
            }
        )

    with MANIFEST.open("w", newline="") as f:
        w = csv.DictWriter(
            f,
            fieldnames=[
                "symlink_path",
                "symlink_target",
                "resolved_target",
                "canonical",
                "archive_link",
                "archive_rel_target",
                "status",
            ],
        )
        w.writeheader()
        w.writerows(rows)

    readme = ARCHIVE / "README.txt"
    readme.write_text(
        "\n".join(
            [
                "old_versionfiles — archived URL-alias symlinks",
                "================================================",
                "This folder is gitignored. Do not serve the site from here.",
                "",
                "Canonical app files stay at the repository root (e.g. HOME.html, LOGIN.PHP).",
                "Live old URLs (/home.html) are handled by nginx rewrites, not these links.",
                "",
                "links/ uses case-safe names (^ before each uppercase letter) so macOS can",
                "store HOME.HTML and home.html as different files.",
                "",
                f"Archived symlinks: {len(rows)}",
                f"Created: {created}",
                f"Skipped (missing canonical): {skipped}",
                "",
                "See MANIFEST.csv for the full mapping.",
                "",
            ]
        )
    )

    # Tracked copy of the mapping for git history
    docs = ROOT / "docs"
    docs.mkdir(exist_ok=True)
    tracked = docs / "url_alias_manifest.csv"
    with tracked.open("w", newline="") as f:
        w = csv.DictWriter(
            f,
            fieldnames=["symlink_path", "symlink_target", "resolved_target", "canonical", "status"],
        )
        w.writeheader()
        for r in rows:
            w.writerow({k: r[k] for k in w.fieldnames})

    print(f"wrote {MANIFEST}")
    print(f"wrote {tracked}")
    print(f"created_links={created} skipped={skipped}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
