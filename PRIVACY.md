# ManageAi Privacy and Data Processing

This is a source-based technical inventory, not a deployment-specific privacy
notice or compliance attestation.

## Processing and storage

`ManageAiPlugin` binds the existing MySQL database and `DatabaseSettingsStore`.
The new ManageSettings chatbot module and existing Chatbot settings consumers
address the same `chatbot/default` settings dataset. The former nested Manager
administration route is removed; provider configuration, credentials and other
content remain with their owning plugins and services. This navigation change
does not introduce a new data store or processing purpose.

The Manager project now composes the existing FileBridge local managed storage
and ClientStack FileManager HTTP/upload services for chatbot resources. A
project-configured absolute private `[directories] data` path holds managed file
owners under `filemanager/owners` and temporary chunk uploads under
`filemanager/uploads`. These files can include operator-provided content or
personal data. The supplied source does not establish automatic expiry of
completed files or a deployment-specific upload-retention schedule. Storage
owner deletion is implemented by FileBridge, and file operations use the
existing FileManager protocol.

## Access, recipients, retention and deletion

The `manageaichatbotfiles` output requires a trusted user and explicit
`system/admin` and `chatbotconfigsettingstab` feature decisions from the active
XRM authority. The backend performs per-operation file validation, path checks
and owner-scoped storage access. Server-side permissions do not derive from a
visible tab alone. Data processed by AI services can be transmitted to configured
providers, depending on provider selection; the actual recipients, transfer
regions, purposes, backup policies and controller duties require deployment
review.

Operators must protect the private file root outside the web-served tree,
restrict filesystem access, maintain backups/deletion policies, and assess
browser/keyboard accessibility in the deployed UI against WCAG 2.2 AA.
