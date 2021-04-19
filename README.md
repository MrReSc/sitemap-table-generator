# sitemap-table-generator
A Bludit plugin that generates a sotable and searchable table from the overview (json from the API) of all blogposts.

## Requirement
TODO 

## Settings
| Setting | Description | Example |
|-|-|-|
| Relative path to the pages.json file | Realtive path starting from $_SERVER['DOCUMENT_ROOT']. | /siteindex/pages.json |
| Webhook for the sitemap | The sitemap table can be reached under this address (www.example.com/webhook). | sitemap-table |
| Page title | If a page title exists, an h1 heading is added on top. | Blogpost index |
| Tables header | The tables header and the plaintext to it. The order  must match the order from the JSON file. Example: 'dateRaw  Date\|permalink Link\|...' | title Titel\|description Beschreibung\|dateRaw Datum |
| Enable search field | If the search field is enabled (1 or true), the search field will be displayed. | 1 |
| Enable sort columns | If column sorting is enabled (1 or true), each column can be sorted individually. | true |

