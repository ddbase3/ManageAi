# ManageAi

Base3Framework Base3Manager project plugin for AI configuration and administration.

## Discoverable administration tabs

`?scope=web&module=manageai&tab=administration` still resolves the existing
`ManageAiAdministrationContentControl`, which delegates to ClientStack's
`SettingsDisplay`. Installed plugins contribute their `ISettingsTab` classes
through the existing class map, with native XRM authorization deciding visibility.
The former static `local/Administration/tabs.json` list is no longer read.
After installing and verifying this patch, run:

```sh
sh plugin/ManageAi/scripts/remove-obsolete-settings-tabs.sh
```

The script only deletes the expected, unmodified old file and succeeds when it
has already been removed. Do not restore the old JSON list.

## Canonical chatbot configuration and resources

Both the discoverable Chatbot Settings tab and the existing `chatbot` Manager
module use the same project-provided `chatbotConfigDisplayData` service. They
both address the existing `chatbot/default` Settings Store record. Resources
use the existing FileBridge/ClientStack FileManager protocol. There is no new
upload implementation or duplicate chatbot settings record.

`ManageAiPlugin` registers the current FileBridge backend under
`IManagedFileStorageService`, uses `FileUploadService`, and exposes the
`manageaichatbotfiles` output. The host explicitly checks the logged-in XRM
user, the existing `system/admin` decision and the
`chatbotconfigsettingstab` feature for every file operation. The FileManager
service handles its existing POST/XHR protocol; downloads remain GET.

Configure `[directories] data` as an **absolute directory outside the public
web root**, writable only by the PHP application identity and its appropriate
service group. The backend stores managed owners under `data/filemanager/owners`
and upload staging under `data/filemanager/uploads`. Do not expose this directory
through the web server, including when using nginx, or include it in static
asset publication. Database settings remain the existing `ISettingsStore` owner.

Grant rights through the active XRM authorization authority. Visibility does
not bypass direct server checks. Test denied users, uploads, downloads, deletion,
AJAX tabs, and retained `chatbot` module functionality in the deployed Manager.
No separate grant mapping is introduced.

### Administration initial-load isolation

The Manager's AJAX-loaded administration uses `SettingsDisplay` with
`initial_load_via_ajax=true`. This isolates the first settings panel HTTP status
from the outer `?name=content` response. A refused panel still returns its own
403 and cannot be accessed directly; other permitted panels remain selectable.
The manager's public history URL remains the existing `scope=web&module=manageai`
route. No changes to `TabControlDisplay` or other platform integrations are required.
