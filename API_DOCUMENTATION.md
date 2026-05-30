# Sinapsis Backend Setup and API Documentation

This document describes how to set up the Laravel backend and how to call the available API endpoints in the current codebase.

## Project Stack

| Area | Technology |
| --- | --- |
| Runtime | PHP 8.2+ |
| Framework | Laravel 12 |
| API auth | Laravel Sanctum bearer tokens |
| OAuth | Google OAuth via Laravel Socialite |
| Validation / DTO | Spatie Laravel Data |
| Storage | Local disk by default, Supabase S3-compatible disk for attachments |
| Realtime | Laravel Reverb / Echo channels |
| Tests | Pest 3 |
| Frontend assets | Vite, Tailwind CSS 4 |

## Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create the environment file

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 3. Configure the database

The default `.env.example` uses SQLite:

```env
DB_CONNECTION=sqlite
```

If you keep SQLite, create the database file before migrating:

```bash
touch database/database.sqlite
```

On Windows PowerShell:

```powershell
New-Item -ItemType File database/database.sqlite -Force
```

For PostgreSQL or Supabase, use values like:

```env
DB_CONNECTION=pgsql
DB_HOST=your-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

### 4. Configure Google OAuth

The app logs users in through Google OAuth and creates Sanctum tokens from the callback.

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URL=http://127.0.0.1:8000/api/auth/google/callback
```

Important: the callback route is `/api/auth/google/callback`, not `/api/v1/auth/google/callback`.

### 5. Configure Supabase attachment storage

Attachments use `Storage::disk('supabase')`, so configure these if you want upload/delete attachment endpoints to work:

```env
SUPABASE_ACCESS_KEY_ID=your-access-key
SUPABASE_SECRET_ACCESS_KEY=your-secret-key
SUPABASE_REGION=ap-southeast-1
SUPABASE_BUCKET=Attachment
SUPABASE_ENDPOINT=https://your-project.supabase.co/storage/v1/s3
SUPABASE_USE_PATH_STYLE_ENDPOINT=true
```

### 6. Configure Reverb if realtime channels are needed

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 7. Run migrations and build assets

```bash
php artisan migrate
npm run build
```

Or run the Composer setup script after `.env` and database are ready:

```bash
composer run setup
```

### 8. Start development servers

Backend only:

```bash
php artisan serve
```

Full local development stack:

```bash
composer run dev
```

Default local API URL:

```text
http://127.0.0.1:8000/api
```

## Authentication

The API uses Google OAuth for login and Laravel Sanctum for API access.

1. Open `GET /api/v1/auth/login`.
2. Google redirects to `GET /api/auth/google/callback`.
3. The callback returns a Sanctum token.
4. Send the token on protected requests:

```http
Authorization: Bearer {token}
Accept: application/json
```

All routes inside `/api/v1` are protected with `auth:sanctum` except:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/v1/auth/login` | Redirect to Google OAuth |
| GET | `/api/v1/shared/{token}` | Public shared note |

The OAuth callback is also public:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/auth/google/callback` | Google OAuth callback, returns token and user |

## Authorization Rules

Authorization is owner-based.

| Resource | Rule |
| --- | --- |
| Notes | User can view, update, delete, restore, and force-delete only their own notes |
| Folders | User can update and delete only their own folders |
| Tags | User can update and delete only their own tags |
| Attachments | User can delete only their own attachments |
| Study tools | Intended to allow viewing/deleting only the owner's study tools |
| Realtime user channel | User can join only `App.Models.User.{user_id}` for their own `user_id` |
| Realtime note channel | User can join only `note.{noteId}` for notes they own |

## Response Shapes

### User

```json
{
  "user_id": "uuid",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "image": "https://example.com/avatar.png",
  "last_opened_note_id": null,
  "created_at": "2026-05-31T00:00:00.000000Z",
  "updated_at": "2026-05-31T00:00:00.000000Z"
}
```

### Note

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "folder_id": null,
  "title": "My note",
  "content": "Markdown or text content",
  "is_published": false,
  "share_token": null,
  "deleted_at": null,
  "created_at": "2026-05-31T00:00:00.000000Z",
  "updated_at": "2026-05-31T00:00:00.000000Z",
  "tags": [],
  "backlinks": null,
  "outgoing_links": null,
  "share_url": null
}
```

### Folder

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "parent_id": null,
  "name": "Folder name",
  "created_at": "2026-05-31T00:00:00.000000Z",
  "updated_at": "2026-05-31T00:00:00.000000Z",
  "children": []
}
```

### Tag

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "name": "Important",
  "color": "#FFAA00",
  "created_at": "2026-05-31T00:00:00.000000Z"
}
```

### Attachment

```json
{
  "id": "uuid",
  "note_id": "uuid",
  "file_url": "https://...",
  "file_name": "document.pdf",
  "file_type": "application/pdf",
  "file_size": 12345,
  "created_at": "2026-05-31T00:00:00.000000Z"
}
```

### Study Tool

```json
{
  "id": "uuid",
  "note_id": "uuid",
  "type": "flashcard",
  "content": [],
  "image_url": null,
  "status": "completed",
  "created_at": "2026-05-31T00:00:00.000000Z"
}
```

## API Reference

Base URL:

```text
http://127.0.0.1:8000/api
```

Protected v1 base URL:

```text
http://127.0.0.1:8000/api/v1
```

### Auth

#### GET `/api/v1/auth/login`

Redirects to Google OAuth.

Authentication: public.

#### GET `/api/auth/google/callback`

Handles the Google OAuth callback and creates or updates a user by email.

Authentication: public.

Response `200`:

```json
{
  "token": "1|plain-text-sanctum-token",
  "user": {
    "user_id": "uuid",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "image": "https://...",
    "last_opened_note_id": null,
    "created_at": "2026-05-31T00:00:00.000000Z",
    "updated_at": "2026-05-31T00:00:00.000000Z"
  }
}
```

#### POST `/api/v1/auth/logout`

Revokes the current Sanctum access token.

Authentication: required.

Response: `204 No Content`.

#### GET `/api/v1/auth/me`

Returns the authenticated user.

Authentication: required.

#### PATCH `/api/v1/auth/me`

Updates the authenticated user.

Authentication: required.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | No | max 255 |
| `image` | string or null | No | nullable |
| `last_opened_note_id` | string or null | No | nullable |

#### PATCH `/api/v1/auth/me/last-opened`

Updates the authenticated user's last opened note.

Authentication: required.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `note_id` | UUID | Yes | must exist in `notes.id` |

Response: `204 No Content`.

### Notes

#### GET `/api/v1/notes`

Lists the authenticated user's notes.

Authentication: required.

Query parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `folder_id` | UUID | No | Filter by folder |
| `search` | string | No | Searches note titles with `LIKE` |
| `trash` | boolean | No | When true, returns only soft-deleted notes |

Response: array of Note objects.

#### POST `/api/v1/notes`

Creates a note.

Authentication: required.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `title` | string | Yes | max 255 |
| `content` | string or null | No | nullable |
| `folder_id` | UUID or null | No | must exist in `folders.id` |
| `is_published` | boolean | Yes in current DTO | defaults to false in PHP constructor |

Response: `201 Created` with Note object.

#### GET `/api/v1/notes/{note}`

Returns one note with tags, backlinks, and outgoing links.

Authentication: required.
Authorization: note owner only.

#### PATCH `/api/v1/notes/{note}`

Updates a note.

Authentication: required.
Authorization: note owner only.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `title` | string | No | max 255 |
| `content` | string or null | No | nullable |
| `folder_id` | UUID or null | No | must exist in `folders.id` |
| `is_published` | boolean | No | true or false |

#### DELETE `/api/v1/notes/{note}`

Soft-deletes a note.

Authentication: required.
Authorization: note owner only.

Response: `204 No Content`.

#### PATCH `/api/v1/notes/{id}/restore`

Restores a soft-deleted note.

Authentication: required.
Authorization: note owner only.

#### DELETE `/api/v1/notes/{id}/force`

Permanently deletes a note.

Authentication: required.
Authorization: note owner only.

Response: `204 No Content`.

#### POST `/api/v1/notes/{note}/publish`

Publishes a note and generates a 64-character `share_token` if one does not already exist.

Authentication: required.
Authorization: note owner only.

Response: Note object with `is_published`, `share_token`, and `share_url`.

#### DELETE `/api/v1/notes/{note}/publish`

Unpublishes a note and clears its `share_token`.

Authentication: required.
Authorization: note owner only.

Response: Note object.

#### POST `/api/v1/notes/{note}/tags/{tag}`

Attaches a tag ID to a note.

Authentication: required.
Authorization: note owner only.

Response: `204 No Content`.

#### DELETE `/api/v1/notes/{note}/tags/{tag}`

Detaches a tag ID from a note.

Authentication: required.
Authorization: note owner only.

Response: `204 No Content`.

### Public Sharing

#### GET `/api/v1/shared/{token}`

Returns a published note by share token.

Authentication: public.

Response: Note object with tags and backlinks.

Errors:

| Status | Meaning |
| --- | --- |
| 404 | Note was not found or is not published |

### Folders

#### GET `/api/v1/folders`

Returns root folders owned by the authenticated user, with recursive `children`.

Authentication: required.

#### POST `/api/v1/folders`

Creates a folder.

Authentication: required.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | Yes | max 255 |
| `parent_id` | UUID or null | No | nullable UUID |

Response: `201 Created` with Folder object.

#### PATCH `/api/v1/folders/{folder}`

Updates a folder.

Authentication: required.
Authorization: folder owner only.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | No | max 255 |
| `parent_id` | UUID or null | No | nullable UUID |

#### DELETE `/api/v1/folders/{folder}`

Deletes a folder.

Authentication: required.
Authorization: folder owner only.

Response: `204 No Content`.

### Tags

#### GET `/api/v1/tags`

Returns tags owned by the authenticated user.

Authentication: required.

#### POST `/api/v1/tags`

Creates a tag.

Authentication: required.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | Yes | max 100, unique per user in database |
| `color` | string or null | No | max 7, intended for hex colors such as `#FFAA00` |

Response: `201 Created` with Tag object.

#### PATCH `/api/v1/tags/{tag}`

Updates a tag.

Authentication: required.
Authorization: tag owner only.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | No | max 100 |
| `color` | string or null | No | max 7 |

#### DELETE `/api/v1/tags/{tag}`

Deletes a tag.

Authentication: required.
Authorization: tag owner only.

Response: `204 No Content`.

### Note Links

#### GET `/api/v1/notes/{note}/backlinks`

Returns notes that link to the selected note.

Authentication: required.
Authorization: note owner only.

Response:

```json
{
  "backlinks": []
}
```

#### POST `/api/v1/notes/{note}/links`

Creates a link from `{note}` to another note.

Authentication: required.
Authorization: source note owner only; target note must be viewable by the same user.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `target_note` | UUID | Yes | target note ID |

Response: `201 Created` with NoteLink object.

#### DELETE `/api/v1/notes/{note}/links/{target}`

Deletes a link from `{note}` to `{target}`.

Authentication: required.
Authorization: source note owner only.

Response: `204 No Content`.

### Attachments

#### GET `/api/v1/notes/{note}/attachments`

Returns attachments for a note.

Authentication: required.
Authorization: note owner only.

#### POST `/api/v1/notes/{note}/attachments`

Uploads an attachment for a note to the `supabase` filesystem disk and stores attachment metadata.

Authentication: required.
Authorization: note owner only.
Content-Type: `multipart/form-data`.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `file` | file | Yes | max 10240 KB |

Response: `201 Created` with Attachment object.

#### DELETE `/api/v1/attachments/{attachment}`

Deletes an attachment from Supabase storage and the database.

Authentication: required.
Authorization: attachment owner only.

Response: `204 No Content`.

### Study Tools

Study tools are stored in the `study_tool_generations` table.

Allowed `type` values:

```text
flashcard, quiz, mindmap
```

Allowed `status` values:

```text
pending, failed, completed
```

#### GET `/api/v1/notes/{id}/study-tools`

Returns study tools for a note.

Authentication: required.
Authorization: note owner only.

Query parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `type` | string | Yes in current implementation | Filters by exact type |

Response: array of Study Tool objects.

#### POST `/api/v1/notes/{id}/study-tools`

Creates a study tool.

Authentication: required.
Authorization: owner of `note_id` in the request body.

Implementation note: the route contains `{id}`, but the controller uses `note_id` from the request body.

Body:

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `note_id` | UUID | Yes | note ID |
| `type` | string | Yes | `flashcard`, `quiz`, or `mindmap` |
| `content` | array | Yes | JSON array/object payload |
| `image_url` | string or null | No | optional image URL |
| `status` | string | Yes | `pending`, `failed`, or `completed` |

Response: Study Tool object.

#### GET `/api/v1/study-tools/{id}`

Returns one study tool by `note_id` and `type`.

Authentication: required.
Authorization: study tool owner only.

Implementation note: the `{id}` route parameter is not used by the controller.

Query parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `note_id` | UUID | Yes | note ID |
| `type` | string | Yes | `flashcard`, `quiz`, or `mindmap` |

Response: Study Tool object.

## Realtime Channels

Channels are defined in `routes/channels.php`.

| Channel | Type | Authorization |
| --- | --- | --- |
| `App.Models.User.{id}` | private | Authenticated user's `user_id` must match `{id}` |
| `note.{noteId}` | private | Authenticated user must own the note |

## Error Responses

The API middleware forces JSON responses for `api/*` routes.

| Status | Meaning |
| --- | --- |
| 401 | Missing or invalid token |
| 403 | Authenticated user does not own the resource |
| 404 | Route model or resource was not found |
| 422 | Validation failed |
| 500 | Server error |

Example unauthenticated response:

```json
{
  "message": "unauthorized"
}
```

## Current Implementation Notes

These are worth checking before production use:

| Area | Note |
| --- | --- |
| `StudyToolPolicy` | The file currently has no namespace/imports, so Laravel policy auto-discovery may not work until fixed. |
| `StoreStudyToolData` | Uses `Optional` attribute but the class is not imported in the file. |
| `StoreNoteData` | Uses `BooleanType` attribute but the class is not imported in the file. |
| `GET /study-tools/{id}` | The route parameter `{id}` is not used; lookup is based on `note_id` and `type` query parameters. |
| `POST /notes/{id}/study-tools` | The route parameter `{id}` is not used; ownership and creation are based on body `note_id`. |
| `GET /notes/{id}/study-tools` | The current query always applies `where('type', $request->query('type'))`; if `type` is omitted, it searches for `type = null`. |

