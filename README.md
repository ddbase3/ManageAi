# ManageAi
Base3Framework Base3Manager plugin for AI.


## Chatbot administration and resources

The Chatbot configuration is exposed through
`local/Administration/tabs.json` (tab-based administration) and
`AdministrationChatbotConfigurationContentControl` (legacy module content).
Both configure the `chatbot/default` Settings Store record. Neither currently
provides file-management endpoints; therefore both explicitly disable the
optional `resources` section of `ChatbotConfigDisplay`. All other chatbot
settings stay available and are persisted by the configured `ISettingsStore`.

If the manager deployment later provides a real file-management backend,
that host integration must implement protected upload/list/delete endpoints and
pass `resources.enabled = true` with the corresponding `resources.endpoints` to
`ChatbotConfigDisplay::setData()` on every HTML render, including AJAX loads.
The current standalone display router does not decode `base3_display_data` into
`setData()` on its own. Editing `tabs.json` alone therefore cannot enable
resource upload on AJAX tabs. Do not invent endpoints or infer upload permission
from the visible editor.


## Administration tabs

`local/Administration/tabs.json` is the single, explicit configuration source
for the existing **Werkzeuge > Administration** tabs. The BASE3 section
references the platform-neutral Groups, Features and Resource Rights displays
provided by ClientStack; ManageXrm supplies their XRM-specific service
bindings. No dynamic plugin-tab merge or separate Authorization module is used.
