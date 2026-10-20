# ManageAi Privacy and Data Processing

This is a source-based technical inventory, not a legal privacy notice or a
verification of the deployed operator's legal basis or compliance.

## Processing and storage

- `ManageAiPlugin` registers the shared `IDatabase` implementation (MySQL) and a
  database-backed `ISettingsStore`. Storage tables, backups and retention are
  determined by the configured database backend and host deployment.
- Administration screens for chatbot, AI providers and service testing can display
  configuration records and invoke the owning plugin/service. The Chatbot form
  stores the `chatbot/default` settings dataset through `ISettingsStore`.
- The two ManageAi chatbot configuration entry points currently disable the
  optional resources file manager; these entry points do not configure file
  upload/list/delete endpoints or transfer files through that widget.
- Other ManageAi service testers may submit operator-provided input to configured
  external AI providers. The selected provider's processing region, retention,
  recipients and credential handling must be reviewed in its own implementation.

## Access, security, deletion and recipients

The host's authentication/authorization, transport protection, service/provider
settings and database permissions govern access to these operations. This plugin
configuration does not establish file-upload permissions. Delete/correction
capabilities and storage retention follow the active Settings Store, host backend
and configured service providers; no deployment-specific lifetime is proven here.

## Known gaps

The supplied sources do not establish the production database backup retention,
operator access roles, encryption at rest, cross-border transfers or the actual
provider configurations. Verify these in the live deployment, and apply the
BASE3 WCAG 2.2 AA and privacy/accessibility requirements to all admin screens.


## Administration navigation

`local/Administration/tabs.json` lists static display identifiers and labels.
This metadata does not itself persist personal data or grants. The BASE3
administration displays call their owning services for authorization and data
processing; Manager-side XRM authorization requests are delegated to the XRM
backend through the configured proxies.
