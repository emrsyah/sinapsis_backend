# sinapsis-api — Backend Reference
> Laravel 13 · PHP 8.3 · PostgreSQL · Laravel Reverb · Laravel Sanctum

---

## Table of Contents
1. [Stack & Libraries](#1-stack--libraries)
2. [Folder Structure](#2-folder-structure)
3. [Database Schema](#3-database-schema)
4. [Architecture & Patterns](#4-architecture--patterns)
5. [Authentication](#5-authentication)
6. [API Endpoints](#6-api-endpoints)
7. [Data Layer (spatie/laravel-data)](#7-data-layer-spatielaravel-data)
8. [Real-time Broadcasting (Reverb)](#8-real-time-broadcasting-reverb)
9. [Environment Configuration](#9-environment-configuration)
10. [Coding Conventions](#10-coding-conventions)

---

## 1. Stack & Libraries

| Package | Version | Purpose |
|---|---|---|
| `laravel/framework` | ^13.0 | Core framework |
| `laravel/sanctum` | ^4.0 | Token-based API auth |
| `laravel/reverb` | ^1.0 | First-party WebSocket broadcasting |
| `spatie/laravel-data` | ^4.0 | Typed DTOs — replaces FormRequest + API Resource |
| `spatie/laravel-query-builder` | ^6.0 | Filter, sort, include on list endpoints |

**PHP:** 8.3 minimum (required by Laravel 13)

---

## 2. Folder Structure

```
sinapsis-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── NoteController.php
│   │       ├── FolderController.php
│   │       ├── TagController.php
│   │       ├── NoteLinkController.php
│   │       ├── AttachmentController.php
│   │       ├── StudyToolController.php
│   │       └── ShareController.php
│   ├── Data/                         # spatie/laravel-data DTOs
│   │   ├── Auth/
│   │   │   ├── RegisterData.php      # validates + types register input
│   │   │   └── LoginData.php
│   │   ├── Note/
│   │   │   ├── NoteData.php          # output resource shape
│   │   │   ├── StoreNoteData.php     # validated input for create
│   │   │   └── UpdateNoteData.php    # validated input for update
│   │   ├── Folder/
│   │   │   ├── FolderData.php
│   │   │   ├── StoreFolderData.php
│   │   │   └── UpdateFolderData.php
│   │   ├── Tag/
│   │   │   ├── TagData.php
│   │   │   └── StoreTagData.php
│   │   ├── StudyTool/
│   │   │   ├── StudyToolData.php
│   │   │   └── StoreStudyToolData.php
│   │   └── User/
│   │       ├── UserData.php
│   │       └── UpdateUserData.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Note.php
│   │   ├── Folder.php
│   │   ├── Tag.php
│   │   ├── NoteLink.php
│   │   ├── Attachment.php
│   │   └── StudyToolGeneration.php
│   ├── Events/
│   │   ├── NoteUpdated.php           # broadcast on note content change
│   │   └── StudyToolReady.php        # broadcast when AI generation saved
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   └── migrations/
│       ├── 0001_create_users_table.php
│       ├── 0002_create_folders_table.php
│       ├── 0003_create_notes_table.php
│       ├── 0004_create_tags_table.php
│       ├── 0005_create_note_tags_table.php
│       ├── 0006_create_note_links_table.php
│       ├── 0007_create_attachments_table.php
│       ├── 0008_create_study_tool_generations_table.php
│       └── 0009_create_personal_access_tokens_table.php
├── routes/
│   ├── api.php                       # all /api/v1 routes
│   └── channels.php                  # Reverb private channel auth
├── config/
│   ├── broadcasting.php
│   └── sanctum.php
└── .env
```

---

## 3. Database Schema

### 3.1 users
```sql
CREATE TABLE users (
  id                    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name                  VARCHAR(255) NOT NULL,
  email                 VARCHAR(255) UNIQUE NOT NULL,
  password              VARCHAR(255) NOT NULL,
  avatar_url            TEXT,
  last_opened_note_id   UUID REFERENCES notes(id) ON DELETE SET NULL,
  created_at            TIMESTAMP DEFAULT NOW(),
  updated_at            TIMESTAMP DEFAULT NOW()
);
```

### 3.2 folders
```sql
CREATE TABLE folders (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id     UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  parent_id   UUID REFERENCES folders(id) ON DELETE CASCADE,
  name        VARCHAR(255) NOT NULL,
  created_at  TIMESTAMP DEFAULT NOW(),
  updated_at  TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_folders_user_id ON folders(user_id);
CREATE INDEX idx_folders_parent_id ON folders(parent_id);
```

> `parent_id = null` means root-level folder.

### 3.3 notes
```sql
CREATE TABLE notes (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id      UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  folder_id    UUID REFERENCES folders(id) ON DELETE SET NULL,
  title        VARCHAR(255) NOT NULL DEFAULT 'Untitled',
  content      TEXT,                       -- stored as Markdown
  is_published BOOLEAN DEFAULT FALSE,
  share_token  VARCHAR(64) UNIQUE,         -- null if not published
  deleted_at   TIMESTAMP,                  -- soft delete
  created_at   TIMESTAMP DEFAULT NOW(),
  updated_at   TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_notes_user_id ON notes(user_id);
CREATE INDEX idx_notes_folder_id ON notes(folder_id);
CREATE INDEX idx_notes_share_token ON notes(share_token);
CREATE INDEX idx_notes_deleted_at ON notes(deleted_at);
CREATE INDEX idx_notes_title ON notes USING gin(to_tsvector('english', title));
```

### 3.4 tags
```sql
CREATE TABLE tags (
  id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  name       VARCHAR(100) NOT NULL,
  color      VARCHAR(7),                   -- hex e.g. #1D9E75
  created_at TIMESTAMP DEFAULT NOW(),
  UNIQUE(user_id, name)
);

CREATE TABLE note_tags (
  note_id UUID NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
  tag_id  UUID NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
  PRIMARY KEY (note_id, tag_id)
);
```

### 3.5 note_links (bi-directional)
```sql
CREATE TABLE note_links (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  source_note  UUID NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
  target_note  UUID NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
  created_at   TIMESTAMP DEFAULT NOW(),
  UNIQUE(source_note, target_note)
);

CREATE INDEX idx_note_links_source ON note_links(source_note);
CREATE INDEX idx_note_links_target ON note_links(target_note);
```

> To get backlinks: `SELECT * FROM note_links WHERE target_note = ?`
> To get outgoing: `SELECT * FROM note_links WHERE source_note = ?`

### 3.6 attachments
```sql
CREATE TABLE attachments (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  note_id      UUID NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
  user_id      UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  file_url     TEXT NOT NULL,              -- Uploadthing CDN URL
  file_name    VARCHAR(255) NOT NULL,
  file_type    VARCHAR(100),               -- MIME type
  file_size    INTEGER,                    -- bytes
  created_at   TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_attachments_note_id ON attachments(note_id);
```

### 3.7 study_tool_generations
```sql
CREATE TABLE study_tool_generations (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  note_id     UUID NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
  user_id     UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type        VARCHAR(20) NOT NULL CHECK (type IN ('flashcard', 'quiz', 'mindmap')),
  content     JSONB NOT NULL,
  image_url   TEXT,                        -- mindmap type only
  status      VARCHAR(20) DEFAULT 'pending'
              CHECK (status IN ('pending', 'completed', 'failed')),
  created_at  TIMESTAMP DEFAULT NOW()      -- serves as generation timestamp
);

CREATE INDEX idx_study_tools_note_id ON study_tool_generations(note_id);
CREATE INDEX idx_study_tools_user_id ON study_tool_generations(user_id);
CREATE INDEX idx_study_tools_type ON study_tool_generations(type);
```

#### JSONB content shapes

**flashcard:**
```json
{
  "cards": [
    { "question": "What is photosynthesis?", "answer": "The process by which..." }
  ]
}
```

**quiz:**
```json
{
  "questions": [
    {
      "question": "Which organelle performs photosynthesis?",
      "options": ["Mitochondria", "Chloroplast", "Nucleus", "Ribosome"],
      "correct_index": 1,
      "explanation": "Chloroplasts contain chlorophyll..."
    }
  ]
}
```

**mindmap:**
```json
{
  "root": "Photosynthesis",
  "children": [
    { "label": "Light reactions", "children": [...] },
    { "label": "Calvin cycle", "children": [...] }
  ],
  "image_url": "https://uploadthing.com/..."
}
```

---

## 4. Architecture & Patterns

### Thin Controllers
Controllers do one thing: receive a validated Data object, call the model/query, return a response. No business logic lives in controllers.

```php
// Good — thin controller
class NoteController extends Controller
{
    public function update(UpdateNoteData $data, Note $note): NoteData
    {
        $this->authorize('update', $note);
        $note->update($data->toArray());
        broadcast(new NoteUpdated($note))->toOthers();
        return NoteData::from($note);
    }
}
```

### spatie/laravel-data as single DTO layer
One Data class per operation handles both input validation (replaces FormRequest) and output serialization (replaces API Resource). This keeps the codebase DRY.

```php
// StoreNoteData.php — validates incoming request
class StoreNoteData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $title,
        public readonly ?string $content,
        public readonly ?string $folder_id,
    ) {}
}

// NoteData.php — serializes outgoing response
class NoteData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $content,
        public readonly bool $is_published,
        public readonly ?string $share_token,
        public readonly ?string $folder_id,
        public readonly ?string $deleted_at,
        public readonly string $created_at,
        public readonly string $updated_at,
        /** @var TagData[] */
        public readonly DataCollection $tags,
        /** @var NoteData[] */
        public readonly DataCollection $backlinks,
    ) {}

    public static function fromModel(Note $note): self
    {
        return self::from([
            ...$note->toArray(),
            'tags' => TagData::collect($note->tags),
            'backlinks' => NoteData::collect($note->backlinks),
        ]);
    }
}
```

### Model responsibilities
Models hold relationships, scopes, and casts — nothing else.

```php
class Note extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['title', 'content', 'folder_id', 'is_published', 'share_token'];

    protected $casts = [
        'is_published' => 'boolean',
        'deleted_at'   => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function folder(): BelongsTo { return $this->belongsTo(Folder::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
    public function studyTools(): HasMany { return $this->hasMany(StudyToolGeneration::class); }
    public function attachments(): HasMany { return $this->hasMany(Attachment::class); }

    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'target_note', 'source_note');
    }
    public function outgoingLinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'source_note', 'target_note');
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
    public function scopeNotTrashed(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }
    public function scopeTrashed(Builder $query): Builder
    {
        return $query->whereNotNull('deleted_at');
    }
}
```

### Authorization (Policies)
Every resource has a Policy. Never check ownership manually in controllers.

```php
// NotePolicy.php
class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
    public function delete(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
}
```

---

## 5. Authentication

Uses **Laravel Sanctum** with token-based auth. One token per device.

### Flow
```
POST /api/v1/auth/register → creates user → returns { token, user }
POST /api/v1/auth/login    → validates → returns { token, user }
POST /api/v1/auth/logout   → revokes current token → 204
```

### Protected routes
All routes except register, login, and `GET /api/v1/shared/{token}` require:
```
Authorization: Bearer {token}
```

### Token in routes/api.php
```php
Route::prefix('v1')->group(function () {
    // Public
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);
    Route::get('shared/{token}', [ShareController::class, 'show']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',     [AuthController::class, 'me']);
        Route::patch('auth/me',   [AuthController::class, 'update']);
        Route::patch('auth/me/last-opened', [AuthController::class, 'updateLastOpened']);

        Route::apiResource('notes',   NoteController::class);
        Route::apiResource('folders', FolderController::class);
        Route::apiResource('tags',    TagController::class);

        Route::get('notes/trash',                       [NoteController::class, 'trash']);
        Route::patch('notes/{note}/restore',            [NoteController::class, 'restore']);
        Route::delete('notes/{note}/force',             [NoteController::class, 'forceDelete']);
        Route::post('notes/{note}/publish',             [ShareController::class, 'publish']);
        Route::delete('notes/{note}/publish',           [ShareController::class, 'unpublish']);
        Route::get('notes/{note}/backlinks',            [NoteLinkController::class, 'index']);
        Route::post('notes/{note}/links',               [NoteLinkController::class, 'store']);
        Route::delete('notes/{note}/links/{target}',    [NoteLinkController::class, 'destroy']);
        Route::get('notes/{note}/attachments',          [AttachmentController::class, 'index']);
        Route::post('notes/{note}/attachments',         [AttachmentController::class, 'store']);
        Route::delete('attachments/{attachment}',       [AttachmentController::class, 'destroy']);
        Route::get('notes/{note}/study-tools',          [StudyToolController::class, 'index']);
        Route::post('notes/{note}/study-tools',         [StudyToolController::class, 'store']);
        Route::get('study-tools/{studyTool}',           [StudyToolController::class, 'show']);
        Route::patch('study-tools/{studyTool}/status',  [StudyToolController::class, 'updateStatus']);
        Route::post('notes/{note}/tags',                [TagController::class, 'attach']);
        Route::delete('notes/{note}/tags/{tag}',        [TagController::class, 'detach']);
    });
});
```

---

## 6. API Endpoints

### Auth
| Method | Endpoint | Body | Response |
|---|---|---|---|
| POST | `/api/v1/auth/register` | `name, email, password` | `{ token, user }` |
| POST | `/api/v1/auth/login` | `email, password` | `{ token, user }` |
| POST | `/api/v1/auth/logout` | — | `204` |
| GET | `/api/v1/auth/me` | — | `UserData` |
| PATCH | `/api/v1/auth/me` | `name?, avatar_url?` | `UserData` |
| PATCH | `/api/v1/auth/me/last-opened` | `note_id` | `204` |

### Notes
| Method | Endpoint | Query Params | Response |
|---|---|---|---|
| GET | `/api/v1/notes` | `folder_id?, tag_id?, search?, trash=true?` | `NoteData[]` |
| POST | `/api/v1/notes` | — | `NoteData` |
| GET | `/api/v1/notes/{id}` | `include=tags,backlinks` | `NoteData` |
| PATCH | `/api/v1/notes/{id}` | — | `NoteData` |
| DELETE | `/api/v1/notes/{id}` | — | `204` (soft delete) |
| GET | `/api/v1/notes/trash` | — | `NoteData[]` |
| PATCH | `/api/v1/notes/{id}/restore` | — | `NoteData` |
| DELETE | `/api/v1/notes/{id}/force` | — | `204` (permanent) |

### Folders
| Method | Endpoint | Response |
|---|---|---|
| GET | `/api/v1/folders` | Nested `FolderData[]` tree |
| POST | `/api/v1/folders` | `FolderData` |
| PATCH | `/api/v1/folders/{id}` | `FolderData` |
| DELETE | `/api/v1/folders/{id}` | `204` |

### Tags
| Method | Endpoint | Response |
|---|---|---|
| GET | `/api/v1/tags` | `TagData[]` |
| POST | `/api/v1/tags` | `TagData` |
| PATCH | `/api/v1/tags/{id}` | `TagData` |
| DELETE | `/api/v1/tags/{id}` | `204` |
| POST | `/api/v1/notes/{id}/tags` | `204` |
| DELETE | `/api/v1/notes/{id}/tags/{tag_id}` | `204` |

### Links
| Method | Endpoint | Response |
|---|---|---|
| GET | `/api/v1/notes/{id}/backlinks` | `NoteData[]` |
| POST | `/api/v1/notes/{id}/links` | `204` |
| DELETE | `/api/v1/notes/{id}/links/{target_id}` | `204` |

### Attachments
| Method | Endpoint | Response |
|---|---|---|
| GET | `/api/v1/notes/{id}/attachments` | `AttachmentData[]` |
| POST | `/api/v1/notes/{id}/attachments` | `AttachmentData` |
| DELETE | `/api/v1/attachments/{id}` | `204` |

### Study Tools
| Method | Endpoint | Query | Response |
|---|---|---|---|
| GET | `/api/v1/notes/{id}/study-tools` | `type=flashcard\|quiz\|mindmap?` | `StudyToolData[]` |
| POST | `/api/v1/notes/{id}/study-tools` | — | `StudyToolData` |
| GET | `/api/v1/study-tools/{id}` | — | `StudyToolData` |
| PATCH | `/api/v1/study-tools/{id}/status` | — | `StudyToolData` |

### Sharing
| Method | Endpoint | Auth | Response |
|---|---|---|---|
| POST | `/api/v1/notes/{id}/publish` | Yes | `{ share_url }` |
| DELETE | `/api/v1/notes/{id}/publish` | Yes | `204` |
| GET | `/api/v1/shared/{token}` | No | `NoteData` (public fields only) |

---

## 7. Data Layer (spatie/laravel-data)

### Why spatie/laravel-data
Replaces two separate layers (FormRequest for validation, API Resource for transformation) with a single typed Data class. This reduces file count and keeps request/response shapes colocated.

### Input Data (validation)
```php
// app/Data/Note/StoreNoteData.php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Uuid;

class StoreNoteData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $title,
        public readonly ?string $content = null,
        #[Uuid]
        public readonly ?string $folder_id = null,
    ) {}
}
```

### Output Data (serialization)
```php
// app/Data/Note/NoteData.php
class NoteData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $content,
        public readonly bool $is_published,
        public readonly ?string $share_token,
        public readonly ?string $folder_id,
        public readonly ?string $deleted_at,
        public readonly string $created_at,
        public readonly string $updated_at,
        #[DataCollectionOf(TagData::class)]
        public readonly ?DataCollection $tags = null,
        #[DataCollectionOf(NoteData::class)]
        public readonly ?DataCollection $backlinks = null,
    ) {}
}
```

### Using in controllers
```php
// Input: type-hint the Data class — validation is automatic
public function store(StoreNoteData $data): NoteData
{
    $note = auth()->user()->notes()->create($data->toArray());
    return NoteData::from($note->load('tags'));
}

// Output: return Data directly — Laravel auto-serializes to JSON
public function show(Note $note): NoteData
{
    $this->authorize('view', $note);
    return NoteData::from($note->load(['tags', 'backlinks']));
}
```

---

## 8. Real-time Broadcasting (Reverb)

### Channel structure
Each authenticated user gets a private channel: `private-user.{user_id}`

### channels.php
```php
Broadcast::channel('user.{userId}', function (User $user, string $userId) {
    return $user->id === $userId;
});
```

### Events

#### NoteUpdated
```php
class NoteUpdated implements ShouldBroadcast
{
    public function __construct(public readonly Note $note) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->note->user_id}");
    }

    public function broadcastAs(): string { return 'note.updated'; }

    public function broadcastWith(): array
    {
        return [
            'note_id'    => $this->note->id,
            'updated_at' => $this->note->updated_at,
        ];
    }
}
```

#### StudyToolReady
```php
class StudyToolReady implements ShouldBroadcast
{
    public function __construct(public readonly StudyToolGeneration $tool) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->tool->user_id}");
    }

    public function broadcastAs(): string { return 'studytool.ready'; }

    public function broadcastWith(): array
    {
        return [
            'note_id'       => $this->tool->note_id,
            'study_tool_id' => $this->tool->id,
            'type'          => $this->tool->type,
            'status'        => $this->tool->status,
        ];
    }
}
```

### Broadcasting from controller
```php
// In NoteController::update
$note->update($data->toArray());

// toOthers() prevents the broadcasting device from receiving its own event
broadcast(new NoteUpdated($note))->toOthers();

return NoteData::from($note);
```

---

## 9. Environment Configuration

```env
APP_NAME=Sinapsis
APP_ENV=production
APP_KEY=
APP_URL=https://api.sinapsis.app

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sinapsis
DB_USERNAME=
DB_PASSWORD=

# Broadcasting
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

# Auth
SANCTUM_STATEFUL_DOMAINS=sinapsis.app,localhost:3000

# Queue (sync for development, can upgrade later)
QUEUE_CONNECTION=sync
```

---

## 10. Coding Conventions

### Naming
| Thing | Convention | Example |
|---|---|---|
| Controllers | PascalCase, singular | `NoteController` |
| Models | PascalCase, singular | `StudyToolGeneration` |
| Data classes | PascalCase + Data suffix | `StoreNoteData` |
| Events | PascalCase, past tense | `NoteUpdated` |
| DB tables | snake_case, plural | `study_tool_generations` |
| DB columns | snake_case | `last_opened_note_id` |
| Routes | kebab-case | `/study-tools` |

### Rules
- Controllers must be thin — no raw queries, no business logic
- Always use `$this->authorize()` at the top of any action that touches a model
- Never return a Model directly — always return a Data class
- Never put validation logic in controllers — use Data class attributes
- All models that belong to a user must have a `scopeForUser` scope
- Use `HasUuids` on all models — UUIDs are generated at DB level (v7 ordered)
- Use `toOthers()` on all broadcasts — prevents echo back to the sender
- Use `SoftDeletes` trait on `Note` model only — other models hard-delete
- Never expose `password` or `remember_token` in any Data output class

## IMPORTANT NOTES
This can be use as the main references, but its not strict and can be change or expanded, especially the code part. Just a reference for the structure and how things work.