#!/bin/sh
set -eu

# Run from the BASE3 Framework root after verifying the new SettingsDisplay.
file="plugin/ManageAi/local/Administration/tabs.json"
expected="e4f8897929c5048929f68cec00ba5e9b61e1b62cf40bb0726db7133c71691b9a"
if [ ! -f "$file" ]; then
    echo "Already absent: $file"
    exit 0
fi
actual="$(sha256sum "$file" | cut -d ' ' -f 1)"
if [ "$actual" != "$expected" ]; then
    echo "Refusing to delete modified administration configuration: $file" >&2
    exit 1
fi
rm -- "$file"
echo "Removed obsolete static settings tab list: $file"
