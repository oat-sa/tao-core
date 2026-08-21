# AUT-4666 — установка Postman smoke

Тикет: https://oat-sa.atlassian.net/browse/AUT-4666  
Архив: `AUT-4666-postman-for-jira.zip` (collection + environment, Postman Collection v2.1).

## Установка

1. Скачай `AUT-4666-postman-for-jira.zip` из вложения к этому PR / комментарию.
2. Распакуй (или импортируй zip целиком).
3. Открой Postman → **Import** → выбери:
   - `AUT-4666-asset-search.postman_collection.json`
   - `AUT-4666-asset-search.postman_environment.json`
4. В правом верхнем углу выбери environment **AUT-4666 Asset Search**.
5. При необходимости поправь `baseUrl` / `username` / `password` / `itemUri` / `mediaPath` под свой стенд.
6. В коллекции запусти папку **00 Auth (required)** по порядку:
   1. Get login page (CSRF)
   2. Login (save taoSession) — followRedirects выключен; в Tests пишется `taoSession`
7. Дальше гоняй папки по AC AUT-4666 (Collection Runner или вручную).

## Если 403

- Environment не выбран, или Auth не прогнан → `taoSession` пустой.
- Collection pre-request сам ставит `Cookie: tao={{taoSession}}` на все запросы кроме login.

## Что внутри (по AC, не по шагам A–G)

| Папка | AC |
|--------|-----|
| Browse baseline | browse без `query` |
| Folder scope & authorization | subtree / sibling |
| MIME / insertion context | filters |
| Universal search | prefix / exact &lt;3 / AND / uri |
| Sort, pagination & response | page / sort |
| Errors & empty results | missing params |
| Indexed search (phase 2) | ES path |
| Metadata filters (phase 2) | metadata AND |

## Token rules (HKD-1996)

- длина ≥ 3 → prefix  
- длина &lt; 3 → exact  
- поля: `label`, `name`, `location`, `alt`, `uri`
