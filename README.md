# ManageAi

Manager-specific AI service composition and tools. The project continues to
bind the existing MySQL/Settings Store, FileBridge managed-file storage and
ClientStack FileManager HTTP/upload services. The `manageaichatbotfiles`
output remains the sole owner of the Manager chatbot resource endpoint.

## Settings navigation migration

The former `Werkzeuge > Administration` embedding of `SettingsDisplay` is
replaced by `ManageSettings`, which statically configures Manager modules and
renders existing `IDisplay` classes directly. Existing AI-only tools remain
under `ManageAi/local/Module/ai.json` (AI Provider, tests, upload and stores).
The standalone Agent Tool Test formerly in the old Agent Tools module is
retained here under the same content control. All other retired legacy
module tabs resolve to displays already present in ManageSettings.
The old `manageai`, `aisystem`, `chatbot`, `providers`, `agenttools` and
`parseflow` module declarations must be removed after installing ManageSettings,
otherwise their navigation remains duplicated. Run:

```sh
sh plugin/ManageAi/scripts/remove-migrated-settings-navigation.sh
```

This script validates the old files before deletion and succeeds when they are
already absent. The retired `ManageAiAdministrationContentControl` and its
unreferenced view/test are also removed. Existing standalone administration
content controls are not repurposed as new settings adapters.

## Canonical chatbot configuration and resources

The `ManageAiPlugin` registration of `chatbotConfigDisplayData` is retained for
existing `ChatbotconfigSettingsTab` consumers. The new static Manager chatbot
tab supplies equivalent `chatbot/default` identity and the explicit FileManager
endpoints in its JSON `data`. Neither path creates a second dataset.

The `manageaichatbotfiles` endpoint still checks user identity and the existing
feature permission before processing any request, then uses the unchanged
FileBridge/ClientStack FileManager protocol. Non-download actions require POST
and XMLHttpRequest. File upload/chunking, list, copy, delete and download
continue through the existing handler.

Configure `[directories] data` as an absolute private directory outside the
public web root, writable by the PHP service identity. The FileManager stores
managed owners under `filemanager/owners` and upload staging under
`filemanager/uploads`. Protect storage with filesystem permissions and web
server configuration. No new rights mapping or authority is introduced.

After deployment, rebuild the class map with the existing project procedure
and test each static settings module, the AI Usage overview, denied displays,
and chatbot resource upload/download from a real Manager session.
