# AUT-4666 — Postman smoke install

Ticket: https://oat-sa.atlassian.net/browse/AUT-4666  
Archive: `AUT-4666-postman-for-jira.zip` (collection + environment, Postman Collection v2.1).

## Install

1. Download `AUT-4666-postman-for-jira.zip` from this PR (`docs/postman/AUT-4666/`).
2. Unpack (or import the zip as-is).
3. Open Postman → **Import** and select:
   - `AUT-4666-asset-search.postman_collection.json`
   - `AUT-4666-asset-search.postman_environment.json`
4. In the top-right corner, select environment **AUT-4666 Asset Search**.
5. Adjust `baseUrl` / `username` / `password` / `itemUri` / `mediaPath` for your stack if needed.
6. In the collection, run folder **00 Auth (required)** in order:
   1. Get login page (CSRF)
   2. Login (save taoSession) — followRedirects off; Tests write `taoSession`
7. Then run the AUT-4666 AC folders (Collection Runner or manually).

## If you get 403

- Environment not selected, or Auth not run → empty `taoSession`.
- Collection pre-request sets `Cookie: tao={{taoSession}}` on all non-login requests.

## Contents (by AC, not by step letters A–G)

| Folder | AC |
|--------|-----|
| Browse baseline | browse without `query` |
| Folder scope & authorization | subtree / sibling |
| MIME / insertion context | filters |
| Universal search | prefix / exact &lt;3 / AND / uri |
| Sort, pagination & response | page / sort |
| Errors & empty results | missing params |
| Indexed search (phase 2) | ES path |
| Metadata filters (phase 2) | metadata AND |

## Token rules (HKD-1996)

- length ≥ 3 → prefix  
- length &lt; 3 → exact  
- fields: `label`, `name`, `location`, `alt`, `uri`
