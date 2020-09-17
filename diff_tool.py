import argparse
import difflib
import sys

from pathlib import Path


def create_diff(old_file: Path, new_file: Path, out_file: Path = None):
    old_file_content = open(old_file).readlines()
    new_file_content = open(new_file).readlines()

    if out_file:
        delta = difflib.HtmlDiff().make_file(old_file_content, new_file_content, old_file.name, new_file.name)
        with open(out_file, 'w') as f:
            f.write(delta)
    else:
        delta = difflib.unified_diff(old_file_content, new_file_content, old_file.name, new_file.name)
        sys.stdout.writelines(delta)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('old_file_version')
    parser.add_argument('new_file_version')
    parser.add_argument("--html", help="specify filename to write output as HTML", required=False)
    args = parser.parse_args()

    old_file = Path(args.old_file_version)
    new_file = Path(args.new_file_version)
    out_file = Path(args.html) if args.html else None

    create_diff(old_file, new_file, out_file)


if __name__ == "__main__":
    main()
