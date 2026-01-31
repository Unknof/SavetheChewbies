import pathlib
import sys

# Allow `from app import ...` when running pytest from the repo root.
sys.path.insert(0, str(pathlib.Path(__file__).resolve().parents[1]))
