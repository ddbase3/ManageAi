#!/bin/sh
set -eu

root="${1:-.}"
if [ ! -f "$root/plugin/ManageAi/local/Administration/tabs.json" ]; then
  echo "Expected Manager root directory (tabs.json not found): $root" >&2
  exit 1
fi

# Remove ONLY exact files created by the superseded tab extension patch.
file="$root/plugin/ManageXrm/local/Administration/Tabs/authorization.json"
if [ -f "$file" ]; then
  actual="$(sha256sum "$file" | cut -d " " -f 1)"
  if [ "$actual" != "2f5d838d93a337367ba62a9d1c69686f2f549496eb3a52091c47e15f7975f6a7" ]; then
    echo "NOT REMOVED (modified file): $file" >&2
    exit 1
  fi
  rm -- "$file"
  echo "Removed: $file"
fi

file="$root/plugin/Base3Manager/test/Service/AdministrationTabExtensionsTest.php"
if [ -f "$file" ]; then
  actual="$(sha256sum "$file" | cut -d " " -f 1)"
  if [ "$actual" != "41b839ddb2ad1df7410e3a8bb989178bd2333d82c5e68c9cc0386dbdc3838918" ]; then
    echo "NOT REMOVED (modified file): $file" >&2
    exit 1
  fi
  rm -- "$file"
  echo "Removed: $file"
fi

file="$root/plugin/ManageAi/test/ContentControl/AdministrationTabExtensionsTest.php"
if [ -f "$file" ]; then
  actual="$(sha256sum "$file" | cut -d " " -f 1)"
  if [ "$actual" != "2f2fddc588afa259fe56ef94c494ddfc5aba18bb213980006c486df01ee52005" ]; then
    echo "NOT REMOVED (modified file): $file" >&2
    exit 1
  fi
  rm -- "$file"
  echo "Removed: $file"
fi

file="$root/plugin/ManageXrm/tools/remove-obsolete-authorization-module.sh"
if [ -f "$file" ]; then
  actual="$(sha256sum "$file" | cut -d " " -f 1)"
  if [ "$actual" != "d849e6f8d92bcba61994bc8f06d1d7953b5cbbe0f0352cf43eaefb4a81df95b7" ]; then
    echo "NOT REMOVED (modified file): $file" >&2
    exit 1
  fi
  rm -- "$file"
  echo "Removed: $file"
fi

# Remove the earlier separate module configuration if not already removed.
file="$root/plugin/ManageXrm/local/Module/authorization.json"
if [ -f "$file" ]; then
  actual="$(sha256sum "$file" | cut -d " " -f 1)"
  if [ "$actual" != "043e64a4b4818998282ee273557e28daf0eba989e5bf24e3a6169ed79d0bc72e" ]; then
    echo "NOT REMOVED (modified module): $file" >&2
    exit 1
  fi
  rm -- "$file"
  echo "Removed: $file"
fi

echo "Obsolete tab extension cleanup complete."
