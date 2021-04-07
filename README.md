# WP WebDAV
Makes the media files of a WordPress instance available via WebDAV.

## Endpoint
After installing the files are available via `/wp-json/webdav/files`.

## Available filters

| Name | Parameters | Default value | Description |
| :-- | :-- | :-- | :-- |
| `wp_webdav_create_node`  | `WP_WebDAV\Node` | `false` | Handles if the current user can create a new file or folder. |
| `wp_webdav_rename_node` | `WP_WebDAV\Node` | `false` | Handles if the current user can rename a file or folder. |
| `wp_webdav_delete_node` | `WP_WebDAV\Node` | `false` | Handles if the current user can delete a file or folder. |
| `wp_webdav_show_all_folder` | – | `true` | Handles if the __all folder__ is visible. |
| `wp_webdav_show_unassigned_folder` | – | `true` | Handles if the __unassigned folder__ is visible. |
| `wp_webdav_show_readme_file` | – | `true` | Handles if a __readme file__ is visible in the root folder. |
| `wp_webdav_root_folder_name` | – | `""`| Sets the name of the root folder. |
| `wp_webdav_nodes` | `WP_WebDAV\Node[]` | ... | Manipulate list of nodes of the passed folder. |

The root folder contains a folder for each defined category, and additional the following:
* The __all folder__ contains all files indepentend of its assigned category (visible as `[all files]`).
* The __unassigned folder__ contains all files without an assigned category (visible as `[unassigned]`).
* The __readme file__ (`Readme.md`) is handy for NextCloud folders.
