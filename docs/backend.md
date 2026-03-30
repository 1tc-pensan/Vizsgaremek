# UFO Észlelés Jelentő Rendszer – Backend Dokumentáció

**GitHub Repository**: [https://github.com/1tc-pensan/Vizsgaremek](https://github.com/1tc-pensan/Vizsgaremek)  
**Elérési út a repóban**: `/backend`

---

## 1. Projekt áttekintés

A backend egy **Laravel 12** keretrendszerre épülő REST API, amely paranormális jelenségek (UFO-észlelések, kísértetek, crop circle-ök stb.) bejelentését, moderálását és szavazás alapú hitelesség-értékelését biztosítja.

### Fő technológiák

| Technológia | Verzió | Szerepe |
|---|---|---|
| PHP | 8.2+ | Programnyelv |
| Laravel | 12.x | Backend keretrendszer |
| Laravel Sanctum | 4.3 | API token alapú autentikáció |
| SQLite / MySQL | – | Adatbázis |
| PHPUnit | 11.5 | Tesztelés |
| Faker | 1.23 | Tesztadat-generálás |

---

## 2. Telepítés és futtatás

```bash
# 1. Függőségek telepítése
composer install

# 2. Környezeti fájl létrehozása
cp .env.example .env
php artisan key:generate

# 3. Adatbázis migrálás és seedelés
php artisan migrate
php artisan db:seed

# 4. Tárhelykapcsolat létrehozása (képek eléréséhez)
php artisan storage:link

# 5. Szerver indítása
php artisan serve
# → http://localhost:8000/api
```

---

## 3. Adatbázis séma

### ER-diagram (egyszerűsített)

```
┌──────────┐     1:N     ┌───────────┐     N:1     ┌────────────┐
│  users   │────────────►│  reports   │◄────────────│ categories │
│          │             │           │              │            │
│ id       │             │ id        │              │ id         │
│ name     │             │ user_id   │              │ name       │
│ email    │             │ category_id│             │ description│
│ password │             │ title     │              └────────────┘
│ role     │             │ description│
│ is_banned│             │ date      │
└──────┬───┘             │ latitude  │
       │                 │ longitude │
       │   1:N           │ witnesses │
       │                 │ status    │
       │                 └─────┬─────┘
       │                       │
       │                  1:N  │  1:N
       │              ┌────────┴────────┐
       │              ▼                 ▼
       │     ┌──────────────┐   ┌────────┐
       │     │report_images │   │ votes  │
       │     │              │   │        │
       │     │ id           │   │ id     │
       │     │ report_id    │   │report_id│
       │     │ image_path   │   │user_id │◄────┘
       │     └──────────────┘   │vote_type│
       │                        └────────┘
       └────────────────────────────┘
```

### Táblák részletezése

#### `users`
| Mező | Típus | Leírás |
|---|---|---|
| `id` | bigint (PK) | Automatikus azonosító |
| `name` | string | Felhasználónév |
| `email` | string (unique) | E-mail cím |
| `password` | string | Hashelt jelszó |
| `role` | enum: `user`, `admin` | Jogosultsági szint |
| `is_banned` | boolean | Kitiltott-e |
| `created_at`, `updated_at` | timestamp | Időbélyegek |
| `deleted_at` | timestamp (nullable) | Soft delete |

#### `categories`
| Mező | Típus | Leírás |
|---|---|---|
| `id` | bigint (PK) | Automatikus azonosító |
| `name` | string (unique) | Kategória neve |
| `description` | text (nullable) | Leírás |
| `deleted_at` | timestamp (nullable) | Soft delete |

#### `reports`
| Mező | Típus | Leírás |
|---|---|---|
| `id` | bigint (PK) | Automatikus azonosító |
| `user_id` | FK → users | Létrehozó felhasználó |
| `category_id` | FK → categories | Kategória |
| `title` | string | Bejelentés címe |
| `description` | text | Részletes leírás |
| `date` | date | Az észlelés dátuma |
| `latitude` | decimal (nullable) | GPS szélesség (-90 – 90) |
| `longitude` | decimal (nullable) | GPS hosszúság (-180 – 180) |
| `witnesses` | integer (nullable) | Tanúk száma |
| `status` | enum: `pending`, `approved`, `rejected` | Státusz (alapért.: pending) |
| `deleted_at` | timestamp (nullable) | Soft delete |

#### `report_images`
| Mező | Típus | Leírás |
|---|---|---|
| `id` | bigint (PK) | Automatikus azonosító |
| `report_id` | FK → reports | Kapcsolódó bejelentés |
| `image_path` | string | Fájl elérési útja |
| `created_at` | timestamp | Feltöltés időpontja |
| `deleted_at` | timestamp (nullable) | Soft delete |

#### `votes`
| Mező | Típus | Leírás |
|---|---|---|
| `id` | bigint (PK) | Automatikus azonosító |
| `report_id` | FK → reports | Szavazott bejelentés |
| `user_id` | FK → users | Szavazó felhasználó |
| `vote_type` | enum: `up`, `down` | Szavazat típusa |
| `created_at` | timestamp | Szavazás időpontja |
| `deleted_at` | timestamp (nullable) | Soft delete |

> **Unique constraint**: Egy felhasználó egy bejelentésre csak egyetlen szavazatot adhat (`report_id` + `user_id`).

---

## 4. Modellek és kapcsolatok

### User
```php
// Kapcsolatok
reports(): HasMany → Report
votes(): HasMany → Vote

// Segédfüggvény
isAdmin(): bool  // role === 'admin'
```

### Report
```php
// Kapcsolatok
user(): BelongsTo → User
category(): BelongsTo → Category
images(): HasMany → ReportImage
votes(): HasMany → Vote

// Kalkulált mező
credibility_score = upvote-ok száma − downvote-ok száma
```

### Category
```php
reports(): HasMany → Report
// Lekérdezéseknél withCount('reports') a bejelentések számáért
```

### ReportImage
```php
report(): BelongsTo → Report
image_url: accessor → Storage::disk('public')->url(image_path)
```

### Vote
```php
report(): BelongsTo → Report
user(): BelongsTo → User
```

---

## 5. API végpontok

### 5.1 Autentikáció

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `POST` | `/api/register` | ✗ | Regisztráció |
| `POST` | `/api/login` | ✗ | Bejelentkezés → Bearer token |
| `POST` | `/api/logout` | ✓ | Kijelentkezés (token törlés) |
| `GET` | `/api/user` | ✓ | Aktuális felhasználó adatai |

**Regisztráció – példa kérés:**
```json
POST /api/register
{
    "name": "Teszt Felhasználó",
    "email": "teszt@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Bejelentkezés – példa válasz:**
```json
{
    "user": {
        "id": 1,
        "name": "Teszt Felhasználó",
        "email": "teszt@example.com",
        "role": "user"
    },
    "token": "1|abc123def456..."
}
```

### 5.2 Bejelentések (Reports)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/reports` | ✗ | Jóváhagyott bejelentések listája |
| `GET` | `/api/reports/{id}` | ✗* | Egy bejelentés részletei |
| `POST` | `/api/reports` | ✓ | Új bejelentés létrehozása |
| `PUT` | `/api/reports/{id}` | ✓ | Bejelentés szerkesztése (tulajdonos/admin) |
| `DELETE` | `/api/reports/{id}` | ✓ | Bejelentés törlése (tulajdonos/admin) |
| `GET` | `/api/map/reports` | ✗ | Térképen megjeleníthető bejelentések |

> *Nem jóváhagyott bejelentést csak a tulajdonos vagy admin tekintheti meg.

**Szűrési lehetőségek** (`GET /api/reports`):
| Paraméter | Típus | Leírás |
|---|---|---|
| `category_id` | integer | Kategória szerinti szűrés |
| `date_from` | date | Dátum alsó határ |
| `date_to` | date | Dátum felső határ |
| `sort_by` | string | Rendezés: `created_at`, `date`, `title`, `credibility` |
| `sort_dir` | string | Irány: `asc` / `desc` |

### 5.3 Képkezelés (Images)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/reports/{id}/images` | ✗ | Bejelentés képei |
| `POST` | `/api/reports/{id}/images` | ✓ | Képek feltöltése (max 10 db, max 5 MB/db) |
| `DELETE` | `/api/images/{id}` | ✓ | Kép törlése (tulajdonos/admin) |

**Feltöltés – korlátozások:**
- Maximálisan 10 fájl egyszerre
- Maximális fájlméret: 5 MB/kép
- Engedélyezett formátumok: `jpeg`, `png`, `gif`, `webp`
- Tárolás: `storage/app/public/report_images/`

### 5.4 Szavazás (Votes)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `POST` | `/api/reports/{id}/vote` | ✓ | Szavazás (up/down) |
| `GET` | `/api/reports/{id}/credibility` | ✗ | Hitelesség pontszám lekérése |

**Szavazás logika:**
1. Új szavazat → létrejön
2. Ugyanaz a szavazat újra → visszavonás (törlés)
3. Ellenkező szavazat → módosítás

### 5.5 Kategóriák (Categories)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/categories` | ✗ | Kategóriák listája (bejelentés számmal) |
| `GET` | `/api/categories/{id}` | ✗ | Kategória részletei |
| `POST` | `/api/admin/categories` | Admin | Új kategória |
| `PUT` | `/api/admin/categories/{id}` | Admin | Kategória szerkesztése |
| `DELETE` | `/api/admin/categories/{id}` | Admin | Kategória törlése |

### 5.6 Profil (Profile)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/profile` | ✓ | Profil lekérése (bejelentések számával) |
| `PUT` | `/api/profile` | ✓ | Profil módosítása (név, email, jelszó) |
| `GET` | `/api/users/{userId}/reports` | ✓ | Felhasználó bejelentései |

### 5.7 Statisztikák (Statistics)

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/statistics` | ✗ | Publikus statisztikák |
| `GET` | `/api/admin/statistics` | Admin | Admin statisztikák |

**Publikus statisztikák tartalma:**
- Összes jóváhagyott bejelentés száma
- Összes felhasználó száma
- Összes szavazat száma
- Státusz szerinti megoszlás
- Kategória szerinti bontás
- Top 5 leghitelesebb bejelentés
- Legutóbbi bejelentések

### 5.8 Admin – Bejelentés moderálás

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/admin/reports` | Admin | Összes bejelentés (státusz szűréssel) |
| `PUT` | `/api/admin/reports/{id}/approve` | Admin | Jóváhagyás |
| `PUT` | `/api/admin/reports/{id}/reject` | Admin | Elutasítás |
| `DELETE` | `/api/admin/reports/{id}` | Admin | Törlés |

### 5.9 Admin – Felhasználó kezelés

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| `GET` | `/api/admin/users` | Admin | Felhasználók listája (keresés: név/email) |
| `PUT` | `/api/admin/users/{id}/ban` | Admin | Felhasználó kitiltása |
| `PUT` | `/api/admin/users/{id}/unban` | Admin | Kitiltás feloldása |

> Admin felhasználót nem lehet kitiltani.

---

## 6. Middleware (Köztes szoftver)

### AdminMiddleware
- Ellenőrzi, hogy a bejelentkezett felhasználó admin-e (`isAdmin()`)
- **403 Forbidden** válasz, ha nem admin
- Az `/api/admin/*` útvonalakra van regisztrálva

### CheckBanned
- Ellenőrzi az `is_banned` flag-et
- **403 Forbidden** válasz kitiltott felhasználónak
- Minden `auth:sanctum` csoportba tartozó végpontra alkalmazva

---

## 7. Validáció (Form Requests)

### RegisterRequest
```
name:     required | string | max:255
email:    required | email  | unique:users
password: required | string | min:8 | confirmed
```

### StoreReportRequest
```
category_id: required | exists:categories,id
title:       required | string | max:255
description: required | string
date:        required | date   | before_or_equal:today
latitude:    nullable | numeric | between:-90,90
longitude:   nullable | numeric | between:-180,180
witnesses:   nullable | integer | min:0
```

### UpdateReportRequest
- Ugyanaz, mint a StoreReportRequest, de minden mező `sometimes` (részleges módosítás)

### StoreCategoryRequest
```
name:        required | string | max:255 | unique:categories
description: nullable | string
```

### UpdateCategoryRequest
```
name:        sometimes | string | max:255 | unique:categories (ignore:current)
description: nullable  | string
```

### VoteRequest
```
vote_type: required | in:up,down
```

---

## 8. Tesztadat (Seeder-ek)

A projekt előre konfigurált tesztadatokkal rendelkezik, amelyek a `php artisan db:seed` paranccsal tölthetők be.

### Felhasználók

| Email | Jelszó | Jogkör |
|---|---|---|
| admin@ufo.hu | password | Admin |
| patrik@ufo.hu | password | Felhasználó |
| odett@ufo.hu | password | Felhasználó |
| kisspeter@ufo.hu | password | Felhasználó |
| horvatheva@ufo.hu | password | Felhasználó |
| soselemer@ufo.hu | password | Felhasználó |
| alimihaly@ufo.hu | password | Felhasználó |

### Kategóriák (9 db)
1. UFO Észlelés
2. Földönkívüli
3. Kísértet / Szellem
4. Crop Circle
5. Bigfoot / Sasquatch
6. Tengeri Szörny
7. Poltergeist
8. Időhurok / Anomália
9. Egyéb Paranormális

### Bejelentések (12 db)
Valós magyar helyszínek GPS-koordinátákkal (Debrecen, Pécs, Bucsa, Mátra stb.), vegyes státuszokkal és szavazatokkal.

---

## 9. Tesztelés Postman-nel

A projekthez tartozik egy **Postman gyűjtemény** (`ufo-api.postman_collection.json`), amely az összes végpont tesztelésére szolgál.

### Importálás
1. Nyisd meg a Postmant
2. **Import** → válaszd ki a `backend/ufo-api.postman_collection.json` fájlt
3. A gyűjtemény automatikusan betöltődik

### Gyűjtemény struktúra

A gyűjtemény az alábbi mappákra tagolódik:

| Mappa | Tartalom |
|---|---|
| **Auth** | Regisztráció, bejelentkezés, kijelentkezés, aktuális felhasználó |
| **Reports** | CRUD műveletek bejelentésekre, térkép végpont |
| **Images** | Képek listázása, feltöltése, törlése |
| **Voting** | Szavazás és hitelesség lekérése |
| **Categories** | Kategóriák listázása és részletek |
| **Profile** | Profiladatok és felhasználó bejelentései |
| **Admin** | Moderálás, felhasználó kezelés, admin statisztikák, kategória CRUD |

### Változók (Variables)

| Változó | Alapértelmezett | Leírás |
|---|---|---|
| `base_url` | `http://localhost:8000/api` | API alap URL |
| `token` | – | Felhasználói Bearer token |
| `admin_token` | – | Admin Bearer token |
| `report_id` | – | Aktuális bejelentés ID |
| `category_id` | – | Aktuális kategória ID |
| `user_id` | – | Aktuális felhasználó ID |
| `image_id` | – | Aktuális kép ID |

> A tesztszkriptek automatikusan mentik a tokeneket és ID-ket a válaszokból.

### Tesztelési folyamat

```
1. Login (admin@ufo.hu / password)
       │
       ▼
   Token automatikusan mentve → admin_token
       │
       ▼
2. Reports → GET /api/reports
       → Listázás, szűrés tesztelése
       │
       ▼
3. Reports → POST /api/reports
       → Új bejelentés létrehozása
       → report_id automatikusan mentve
       │
       ▼
4. Images → POST /api/reports/{id}/images
       → Képek feltöltése a bejelentéshez
       │
       ▼
5. Voting → POST /api/reports/{id}/vote
       → Szavazás tesztelése
       │
       ▼
6. Admin → PUT /api/admin/reports/{id}/approve
       → Bejelentés jóváhagyása
       │
       ▼
7. Admin Users → GET/PUT
       → Felhasználó tiltás tesztelése
```

### Példa: Bejelentkezés tesztelése

**Kérés:**
```
POST {{base_url}}/login
Content-Type: application/json

{
    "email": "admin@ufo.hu",
    "password": "password"
}
```

**Elvárt válasz (200 OK):**
```json
{
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@ufo.hu",
        "role": "admin",
        "is_banned": false
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

### Példa: Bejelentés létrehozás tesztelése

**Kérés:**
```
POST {{base_url}}/reports
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "category_id": 1,
    "title": "Fényes objektum az égen",
    "description": "Háromszög alakú, csendes...",
    "date": "2026-03-15",
    "latitude": 47.5316,
    "longitude": 21.6273,
    "witnesses": 3
}
```

**Elvárt válasz (201 Created):**
```json
{
    "id": 13,
    "title": "Fényes objektum az égen",
    "status": "pending",
    "category": { "id": 1, "name": "UFO Észlelés" },
    "user": { "id": 2, "name": "Patrik" }
}
```

### Hibás kérés tesztelés

**Hiányzó mezők (422 Unprocessable Entity):**
```json
POST /api/reports
{
    "title": "Teszt"
}
→ Válasz:
{
    "message": "Validation failed",
    "errors": {
        "category_id": ["A kategória mező kitöltése kötelező."],
        "description": ["A leírás mező kitöltése kötelező."],
        "date": ["A dátum mező kitöltése kötelező."]
    }
}
```

**Jogosulatlan hozzáférés (401 Unauthorized):**
```
POST /api/reports (token nélkül)
→ { "message": "Unauthenticated." }
```

**Admin végpont nem admin felhasználóval (403 Forbidden):**
```
PUT /api/admin/reports/1/approve
Authorization: Bearer {{user_token}}
→ { "message": "Forbidden." }
```

---

## 11. Feature tesztek

A projekt a Laravel beépített PHPUnit tesztelési keretrendszerét használja.

### Tesztek futtatása

```bash
php artisan test
# vagy
./vendor/bin/phpunit
```

### Tesztelendő területek

| Terület | Tesztelési szempont |
|---|---|
| **Autentikáció** | Regisztráció, bejelentkezés, token generálás, kijelentkezés |
| **Bejelentések CRUD** | Létrehozás, olvasás, módosítás, törlés, jogosultság ellenőrzés |
| **Képfeltöltés** | Fájlméret, formátum, maximum darabszám validáció |
| **Szavazás** | Up/down, visszavonás, módosítás, unique constraint |
| **Admin műveletek** | Jóváhagyás, elutasítás, tiltás, jogosultság ellenőrzés |
| **Validáció** | Kötelező mezők, formátum, tartomány ellenőrzés |

### Feature teszt példa – Bejelentés létrehozás

```php
public function test_authenticated_user_can_create_report(): void
{
    $user = User::factory()->create();
    $category = Category::first();

    $response = $this->actingAs($user)->postJson('/api/reports', [
        'category_id' => $category->id,
        'title'       => 'Teszt bejelentés',
        'description' => 'Részletes leírás a paranormális eseményről.',
        'date'        => '2026-03-15',
        'latitude'    => 47.4979,
        'longitude'   => 19.0402,
        'witnesses'   => 2,
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment(['title' => 'Teszt bejelentés']);
}
```

## 12. Mappastruktúra

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php        # Regisztráció, login, logout
│   │   │       ├── ReportController.php      # Bejelentések CRUD
│   │   │       ├── CategoryController.php    # Kategóriák CRUD
│   │   │       ├── ImageController.php       # Képfeltöltés és törlés
│   │   │       ├── VoteController.php        # Szavazás kezelése
│   │   │       ├── ProfileController.php     # Profil kezelés
│   │   │       ├── StatisticsController.php  # Publikus statisztikák
│   │   │       └── Admin/
│   │   │           ├── ReportController.php      # Moderálás
│   │   │           ├── UserController.php        # Felhasználó kezelés
│   │   │           └── StatisticsController.php  # Admin statisztikák
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php           # Admin jogosultság
│   │   │   └── CheckBanned.php              # Kitiltott user blokkol
│   │   └── Requests/
│   │       ├── RegisterRequest.php
│   │       ├── StoreReportRequest.php
│   │       ├── UpdateReportRequest.php
│   │       ├── StoreCategoryRequest.php
│   │       ├── UpdateCategoryRequest.php
│   │       └── VoteRequest.php
│   └── Models/
│       ├── User.php
│       ├── Report.php
│       ├── Category.php
│       ├── ReportImage.php
│       └── Vote.php
├── database/
│   ├── migrations/                           # Adatbázis séma
│   └── seeders/                              # Tesztadatok
├── routes/
│   └── api.php                               # API útvonalak definíciója
├── config/
│   ├── cors.php                              # CORS beállítások
│   ├── sanctum.php                           # Autentikáció konfiguráció
│   └── database.php                          # Adatbázis konfiguráció
├── storage/
│   └── app/public/report_images/             # Feltöltött képek
├── tests/
│   ├── Feature/                              # Feature tesztek
│   └── Unit/                                 # Unit tesztek
└── ufo-api.postman_collection.json           # Postman gyűjtemény
```

---

## 13. Hasznos Artisan parancsok

```bash
# Migrációk futtatása
php artisan migrate

# Migrációk visszavonása és újrafuttatás
php artisan migrate:fresh --seed

# Seeder-ek futtatása
php artisan db:seed

# Útvonalak listázása
php artisan route:list

# Konfiguráció cache-elés
php artisan config:cache

# Storage symlink létrehozása
php artisan storage:link
```


## 14. Összefoglaló

| Szempont | Részlet |
|---|---|
| **Végpontok száma** | ~26 REST API végpont |
| **Modellek** | 5 (User, Report, Category, ReportImage, Vote) |
| **Middleware** | 2 egyéni (Admin, CheckBanned) + Sanctum |
| **Validáció** | 6 Form Request osztály |
| **Jogosultsági szintek** | 3 (vendég, felhasználó, admin) |
| **Tesztelés** | Postman gyűjtemény + PHPUnit Feature tesztek |
| **Adatbázis** | SQLite (fejlesztéshez) / MySQL (éles) |
