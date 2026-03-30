# UFO Észlelés Jelentő Rendszer – Frontend Dokumentáció

**GitHub Repository**: [https://github.com/1tc-pensan/Vizsgaremek](https://github.com/1tc-pensan/Vizsgaremek)  
**Elérési út a repóban**: `/frontend`

---

## 1. Projekt áttekintés

A frontend egy **Angular 21** keretrendszerre épülő egyoldalas alkalmazás (SPA), amely a Laravel backend REST API-jával kommunikál. A felhasználók böngészhetik, létrehozhatják és szavazhatják a paranormális jelenségekről szóló bejelentéseket, míg az adminok moderálhatják a tartalmat.

### Fő technológiák

| Technológia | Verzió | Szerepe |
|---|---|---|
| Angular | 21.0 | Frontend keretrendszer |
| TypeScript | 5.9 | Programnyelv |
| Bootstrap | 5.3.3 | UI keretrendszer |
| Bootstrap Icons | 1.11.3 | Ikonok |
| Leaflet | 1.9.4 | Interaktív térkép |
| RxJS | 7.8 | Reaktív programozás |
| Google Fonts | – | Betűtípusok (Orbitron, Inter, Share Tech Mono) |

### Dizájn koncepció
Egyedi **sci-fi / űrtéma** sötét kék-lila színvilággal, izzó (glow) effektekkel és futurisztikus betűtípusokkal.

---

## 2. Telepítés és futtatás

```bash
# 1. Függőségek telepítése
npm install

# 2. Fejlesztői szerver indítása
ng serve
# → http://localhost:4200

# 3. Éles build
ng build
```

> **Előfeltétel**: A backend szervernek futnia kell a `http://localhost:8000` címen.

---

## 3. Alkalmazás architektúra

### 3.1 Magas szintű felépítés

```
┌─────────────────────────────────────────────┐
│              Angular 21 SPA                 │
│                                             │
│  ┌─────────┐  ┌──────────┐  ┌───────────┐  │
│  │ Guards  │  │Interceptor│  │ Services  │  │
│  │ (auth,  │  │ (token   │  │ (API hív.)│  │
│  │  admin) │  │  csatolás)│  │           │  │
│  └────┬────┘  └─────┬────┘  └─────┬─────┘  │
│       │              │              │        │
│  ┌────┴──────────────┴──────────────┴────┐  │
│  │           Komponensek (Pages)          │  │
│  │  Navbar │ ReportList │ ReportDetail    │  │
│  │  Login  │ Register   │ ReportForm     │  │
│  │  Profile│ Statistics │ Map            │  │
│  │  AdminReports │ AdminUsers │ AdminCat.│  │
│  └───────────────────────────────────────┘  │
│                      │                       │
│               HTTP kérések                   │
│           (JSON + Bearer token)              │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
              Laravel 12 REST API
            http://localhost:8000/api
```

### 3.2 Standalone komponensek

Az alkalmazás teljes egészében **standalone komponenseket** használ (Angular 21 ajánlás) – nincs NgModule.

### 3.3 Signal-alapú állapotkezelés

A felhasználói állapotot Angular **Signals** segítségével kezeli (modern reaktív minta), ami hatékonyabb változás-detektálást biztosít.

---

## 4. Útvonalak (Routing)

### Publikus útvonalak

| Útvonal | Komponens | Leírás |
|---|---|---|
| `/` | ReportList | Főoldal – bejelentések listája |
| `/login` | Login | Bejelentkezés |
| `/register` | Register | Regisztráció |
| `/reports/:id` | ReportDetail | Bejelentés részletei |
| `/statistics` | Statistics | Publikus statisztikák |

### Védett útvonalak (bejelentkezés szükséges)

| Útvonal | Komponens | Guard |
|---|---|---|
| `/reports/create` | ReportForm | `authGuard` |
| `/reports/:id/edit` | ReportForm | `authGuard` |
| `/profile` | Profile | `authGuard` |

### Admin útvonalak

| Útvonal | Komponens | Guard |
|---|---|---|
| `/admin/reports` | AdminReports | `adminGuard` |
| `/admin/users` | AdminUsers | `adminGuard` |
| `/admin/categories` | AdminCategories | `adminGuard` |

> Minden komponens **lazy-loaded** (csak szükség esetén töltődik be).  
> Ismeretlen útvonal → átirányítás a főoldalra (`**` → `/`).

---

## 5. Autentikáció és biztonság

### 5.1 Auth Service (`services/auth.ts`)

Az autentikáció központi szolgáltatása:

```typescript
// Állapot (Signal-alapú)
currentUser = signal<User | null>(null);

// Műveletek
register(data) → POST /api/register
login(data)    → POST /api/login → token mentése sessionStorage-ba
logout()       → POST /api/logout → token törlése
isLoggedIn()   → boolean
isAdmin()      → boolean (role === 'admin')
getToken()     → string | null (sessionStorage-ból)
```

### 5.2 Auth Interceptor (`interceptors/auth-interceptor.ts`)

Minden HTTP kéréshez automatikusan csatolja a Bearer tokent:

```typescript
// Funkcionális interceptor (Angular 15+ minta)
Ha van token → Authorization: Bearer {token} header hozzáadása
Ha nincs → kérés változatlanul továbbítva
```

### 5.3 Route Guardok

| Guard | Fájl | Működés |
|---|---|---|
| `authGuard` | `guards/auth-guard.ts` | Bejelentkezés ellenőrzés → `/login` átirányítás |
| `adminGuard` | `guards/admin-guard.ts` | Admin jogosultság + bejelentkezés → `/` átirányítás |

### 5.4 Token tárolás
- **sessionStorage** – böngésző bezárásakor törlődik
- Automatikus visszaállítás oldal újratöltésekor (ha a session még él)

---

## 6. Szolgáltatások (Services)

### 6.1 Report Service (`services/report.ts`)

| Metódus | API hívás | Leírás |
|---|---|---|
| `getAll(filters?)` | `GET /api/reports` | Szűrőkkel (kategória, dátum, rendezés) |
| `getOne(id)` | `GET /api/reports/{id}` | Részletek |
| `create(data)` | `POST /api/reports` | Új bejelentés |
| `update(id, data)` | `PUT /api/reports/{id}` | Módosítás |
| `delete(id)` | `DELETE /api/reports/{id}` | Törlés |
| `getMapReports()` | `GET /api/map/reports` | Térképes megjelenítéshez |
| `uploadImages(id, files)` | `POST /api/reports/{id}/images` | Képfeltöltés (FormData) |

### 6.2 Vote Service (`services/vote.ts`)

| Metódus | API hívás | Leírás |
|---|---|---|
| `vote(reportId, voteType)` | `POST /api/reports/{id}/vote` | Szavazás (up/down) |

### 6.3 Category Service (`services/category.ts`)

| Metódus | API hívás | Leírás |
|---|---|---|
| `getAll()` | `GET /api/categories` | Kategóriák listája |
| `create(data)` | `POST /api/admin/categories` | Kategória létrehozás |
| `update(id, data)` | `PUT /api/admin/categories/{id}` | Kategória módosítás |
| `delete(id)` | `DELETE /api/admin/categories/{id}` | Kategória törlés |

### 6.4 Admin Service (`services/admin.ts`)

| Metódus | API hívás | Leírás |
|---|---|---|
| `getReports(status?)` | `GET /api/admin/reports` | Bejelentések (szűrés) |
| `approveReport(id)` | `PUT /api/admin/reports/{id}/approve` | Jóváhagyás |
| `rejectReport(id)` | `PUT /api/admin/reports/{id}/reject` | Elutasítás |
| `deleteReport(id)` | `DELETE /api/admin/reports/{id}` | Törlés |
| `getUsers(search?)` | `GET /api/admin/users` | Felhasználók keresése |
| `banUser(id)` | `PUT /api/admin/users/{id}/ban` | Kitiltás |
| `unbanUser(id)` | `PUT /api/admin/users/{id}/unban` | Feloldás |

---

## 7. Oldalak és komponensek

### 7.1 Főoldal – Bejelentések listája (`pages/report-list`)

**Funkciók:**
- **Top 3 körhinta** – A leghitelesebb bejelentések automatikus, 5 mp-es váltakozással
  - Manuális navigáció (nyilak + pontok)
  - Egér felé vitelekor szünetelés
  - Kép overlay címmel és leírással

- **Szűrőpanel:**
  - Kategória legördülő lista
  - Dátum tartomány (tól–ig)
  - Rendezés: létrehozás dátuma, esemény dátuma, cím, hitelesség
  - Irány: növekvő / csökkenő
  - Szűrők törlése gomb
  - **Debounced szűrés** (400 ms késleltetés – szerverterhelés csökkentés)

- **Kártyás megjelenítés:**
  - Kép előnézet (thumbnail)
  - Kategória badge
  - Cím, rövidített leírás
  - Esemény dátuma, szavazatok
  - Hitelesség pontszám

- **Lapozás:** 9 bejelentés oldalanként

**Képernyő működés:**
```
┌────────────────────────────────────────────────┐
│  ◄  [Top 3 Körhinta - leghitelesebb]    ►     │
│     ● ○ ○                                      │
├────────────────────────────────────────────────┤
│ Kategória: [▼ Összes]  Dátum: [tól] - [ig]    │
│ Rendezés: [▼ Dátum]    Irány: [▼ Csökk.]      │
├──────────┬──────────┬──────────────────────────┤
│ ┌──────┐ │ ┌──────┐ │ ┌──────┐                │
│ │ Kép  │ │ │ Kép  │ │ │ Kép  │                │
│ ├──────┤ │ ├──────┤ │ ├──────┤                │
│ │Cím   │ │ │Cím   │ │ │Cím   │                │
│ │Leírás│ │ │Leírás│ │ │Leírás│                │
│ │👍 3 👎1│ │ │👍 5 👎0│ │ │👍 2 👎1│                │
│ └──────┘ │ └──────┘ │ └──────┘                │
├──────────┴──────────┴──────────────────────────┤
│           [ 1 ] [ 2 ] [ 3 ] →                  │
└────────────────────────────────────────────────┘
```

### 7.2 Bejelentés részletei (`pages/report-detail`)

**Funkciók:**
- Teljes bejelentés megjelenítése (cím, leírás, minden adat)
- **Metaadat tábla:** dátum, helyszín, tanúk száma, bejelentő neve
- **Interaktív térkép** – Leaflet marker az észlelés helyén (ha van koordináta)
- **Szavazás** – Upvote/downvote gombok vizuális visszajelzéssel
  - Nem bejelentkezett → átirányítás a login oldalra
- **Képgaléria:**
  - Thumbnail rács (130×130 px)
  - **Lightbox nézet:** kattintásra teljes képernyős megjelenítés
  - Nyilakkal navigáció (billentyűzet: ←, →, Esc)
  - Kép számláló: „X / Összes"
- **Tulajdonos/admin vezérlők:** szerkesztés és törlés gombok
- Státusz badge (pending / approved / rejected)

### 7.3 Bejelentés űrlap (`pages/report-form`)

**Létrehozás mód:**
- Kötelező mezők: kategória, cím, leírás, dátum
- Opcionális mezők: GPS koordináták, tanúk száma
- **Kattintható térkép** – koordináta kiválasztás térképen kattintással
- **Képfeltöltés:**
  - Fájlválasztó (többszörös kijelölés)
  - Előnézet (thumbnail) minden kiválasztott képről
  - Törlés lehetőség feltöltés előtt
  - Korlátozások: max 10 fájl, max 5 MB/db, jpeg/png/gif/webp

**Szerkesztés mód:**
- Meglévő adatok előtöltése
- Meglévő képek megjelenítése + egyenkénti törlés
- Új képek hozzáadása

**Képernyő működés:**
```
┌──────────────────────────────────────┐
│         Új bejelentés                │
├──────────────────────────────────────┤
│ Kategória:    [▼ UFO Észlelés     ] │
│ Cím:          [__________________  ] │
│ Leírás:       [                    ] │
│               [                    ] │
│ Dátum:        [2026-03-20         ] │
│ Tanúk:        [3                  ] │
├──────────────────────────────────────┤
│ Helyszín kiválasztás:                │
│ ┌────────────────────────────┐       │
│ │      🗺️ Leaflet térkép      │       │
│ │    (kattints a helyszínre) │       │
│ │         📍                  │       │
│ └────────────────────────────┘       │
│ Lat: 47.4979   Lng: 19.0402         │
├──────────────────────────────────────┤
│ Képek:  [Fájlok kiválasztása]        │
│ ┌─────┐ ┌─────┐ ┌─────┐             │
│ │ 🖼️  │ │ 🖼️  │ │ 🖼️  │             │
│ │  ✕  │ │  ✕  │ │  ✕  │             │
│ └─────┘ └─────┘ └─────┘             │
├──────────────────────────────────────┤
│            [ Beküldés ]              │
└──────────────────────────────────────┘
```

### 7.4 Bejelentkezés és regisztráció (`pages/login`, `pages/register`)

**Bejelentkezés:**
- Mezők: email, jelszó
- Hibaüzenet megjelenítés (helytelen adatok)
- Sikeres bejelentkezés → átirányítás a főoldalra
- Link a regisztrációhoz

**Regisztráció:**
- Mezők: név, email, jelszó (min. 8 karakter), jelszó megerősítés
- Backend validációs hibák megjelenítése mezőnként
- Sikeres regisztráció → automatikus bejelentkezés → főoldal
- Link a bejelentkezéshez

### 7.5 Profil (`pages/profile`)

- **Profilszerkesztő:**
  - Név és email módosítás
  - Jelszó módosítás (opcionális, megerősítéssel)
  - Sikeres/Hiba üzenetek
- **Saját bejelentések lista:**
  - Összes saját bejelentés (státusz badge-dzsel)
  - Szavazat számok
  - Hivatkozás a bejelentés részleteire

### 7.6 Statisztikák (`pages/statistics`)

- **Összesítő kártyák:** összes bejelentés, felhasználók, szavazatok
- **Státusz megoszlás:** függőben / jóváhagyott / elutasított
- **Kategória sávdiagram:** vízszintes sávok relatív szélességgel
- **Top 5 leghitelesebb:** rangsorolt lista pontszámmal
- **Legutóbbi bejelentések:** friss bejegyzések listája

### 7.7 Admin – Bejelentések kezelése (`pages/admin-reports`)

- **Státusz szűrő:** összes / függőben / jóváhagyott / elutasított
- **Táblázat:** ID, cím (link), bejelentő email, kategória, dátum, státusz badge
- **Akciók:** Jóváhagyás / Elutasítás / Törlés gombok
- Törlés előtt megerősítési párbeszéd

### 7.8 Admin – Felhasználók kezelése (`pages/admin-users`)

- **Keresés:** név vagy email alapján
- **Táblázat:** ID, név, email, jogkör, bejelentések száma, regisztráció dátuma, státusz
- **Akciók:** Tiltás / Feloldás (admin felhasználóra nem alkalmazható)
- Kitiltott felhasználók piros kiemelése

### 7.9 Admin – Kategóriák kezelése (`pages/admin-categories`)

- **Kétoszlopos elrendezés:**
  - **Bal oldal:** létrehozás/szerkesztés űrlap (név, leírás)
  - **Jobb oldal:** meglévő kategóriák listája (név, leírás, bejelentés szám)
- **Inline szerkesztés:** kategória kiválasztása → az űrlap feltöltődik az adatokkal
- Szerkesztés / Törlés gombok minden kategóriánál

---

## 8. Megosztott komponensek

### 8.1 Navbar (`components/navbar`)

- **Márkanév:** „UFO // REPORT" sci-fi ikonnal
- **Navigációs menü:**
  - Bejelentések (főoldal) – mindig látható
  - Statisztikák – mindig látható
  - + Új bejelentés – csak bejelentkezett felhasználóknak
  - Admin legördülő (Bejelentések, Felhasználók, Kategóriák) – csak adminnak
- **Jobb oldal:**
  - Felhasználó neve (link a profilra) + Kijelentkezés – bejelentkezve
  - Bejelentkezés / Regisztráció linkek – kijelentkezett állapotban
- **Reszponzív:** Bootstrap hamburger menü mobilon

### 8.2 Térkép komponens (`components/map`)

- **Leaflet.js** integráció OpenStreetMap csempékkel
- **Input paraméterek:**
  - `lat`, `lng` – koordináták
  - `clickable` – kattintható mód (űrlapon)
  - `height` – magasság (alapért.: 300px)
- **Output:** `mapClick` esemény → `[lat, lng]` pár
- **Optimalizáció:** Angular `NgZone`-on kívüli futtatás
- **Alapértelmezett nézet:** Budapest (47.4979, 19.0402), zoom: 5

---

## 9. Hibakezelés

### 9.1 Backend validációs hibák megjelenítése

Amikor a backend **422 Unprocessable Entity** választ küld, a frontend megjeleníti a hibákat a megfelelő mezők alatt:

```
Hibás regisztráció:
┌──────────────────────────────────────┐
│ Email:    [nemvalid]                 │
│ ⚠ Az email cím formátuma érvénytelen│
│                                      │
│ Jelszó:   [rövid]                   │
│ ⚠ A jelszónak legalább 8 karakternek│
│   kell lennie                        │
└──────────────────────────────────────┘
```

### 9.2 Autentikációs hibák

| Hiba | Kezelés |
|---|---|
| **401 Unauthorized** | Átirányítás a bejelentkezési oldalra |
| **403 Forbidden** | „Nincs jogosultságod" üzenet / átirányítás a főoldalra |
| Kitiltott felhasználó | „A fiókod ki lett tiltva" üzenet |

### 9.3 Hálózati hibák

- Sikertelen API kérés → hibaüzenet megjelenítés a felhasználónak
- Loading state (betöltés jelző) minden aszinkron művelet alatt

### 9.4 Tipikus hibák és megoldásuk

#### Helytelen bejelentkezési adatok
```
POST /api/login → 401
Megjelenítés: "Hibás email vagy jelszó."
```

#### Nem kitöltött kötelező mezők
```
POST /api/reports (üres body) → 422
Megjelenítés: Mezőnkénti hibaüzenetek magyarul
```

#### Admin végpont hozzáférés normál felhasználóként
```
Guard ellenőrzés → adminGuard elutasítja → átirányítás "/"
Ha mégis API hívás történik → 403 Forbidden válasz
```

#### Token lejárat / érvénytelen token
```
Bármely védett végpont → 401
Felhasználó kijelentkeztetése, átirányítás "/login"
```

---

## 10. Stílusok és téma

### Globális CSS változók

```css
--sf-bg:       #0b0e14    /* Fő háttérszín */
--sf-surface:  #111620    /* Kártya/panel háttér */
--sf-surface2: #161c2a    /* Kártya fejléc/lábléc */
--sf-border:   #1e2d45    /* Szegélyek */
--sf-accent:   #3a8fc8    /* Elsődleges szín (gombok, kiemelések) */
--sf-accent2:  #4db8c8    /* Másodlagos szín */
--sf-text:     #dde6f5    /* Fő szövegszín */
--sf-text-dim: #8a9dba    /* Halvány szöveg */
--sf-glow:     rgba(58, 143, 200, 0.15)  /* Izzó effekt */
```

### Tipográfia

| Alkalmazás | Betűtípus | Forrás |
|---|---|---|
| Címek (h1-h6) | Orbitron (700) | Google Fonts |
| Szövegtörzs | Inter (400/500/600) | Google Fonts |
| Kód/technikai | Share Tech Mono | Google Fonts |

### Reszponzív design

A teljes alkalmazás reszponzív – Bootstrap 5.3 grid rendszerrel:
- **Asztali:** 3 oszlopos kártya elrendezés, széles táblázatok
- **Tablet:** 2 oszlopos elrendezés, kisebb kártyák
- **Mobil:** 1 oszlopos, hamburger menü, teljes szélességű elemek

---

## 11. Mappastruktúra

```
frontend/
├── src/
│   ├── index.html                    # Fő HTML (Bootstrap, Google Fonts CDN)
│   ├── main.ts                       # Alkalmazás bootstrap
│   ├── styles.css                    # Globális stílusok és sci-fi téma
│   ├── environments/
│   │   └── environment.ts            # API URL konfiguráció
│   └── app/
│       ├── app.ts                    # Gyökér komponens
│       ├── app.config.ts             # Alkalmazás konfiguráció (providers)
│       ├── app.routes.ts             # Útvonalak definíciója
│       ├── app.css                   # App-szintű stílusok
│       ├── components/
│       │   ├── navbar/               # Navigációs sáv
│       │   │   ├── navbar.ts
│       │   │   ├── navbar.html
│       │   │   └── navbar.css
│       │   └── map/                  # Leaflet térkép komponens
│       │       ├── map.ts
│       │       ├── map.html
│       │       └── map.css
│       ├── guards/
│       │   ├── auth-guard.ts         # Bejelentkezés guard
│       │   └── admin-guard.ts        # Admin jogosultság guard
│       ├── interceptors/
│       │   └── auth-interceptor.ts   # Bearer token csatolás
│       ├── services/
│       │   ├── auth.ts               # Autentikáció
│       │   ├── report.ts             # Bejelentések CRUD
│       │   ├── vote.ts               # Szavazás
│       │   ├── category.ts           # Kategóriák
│       │   └── admin.ts              # Admin műveletek
│       └── pages/
│           ├── login/                # Bejelentkezés oldal
│           ├── register/             # Regisztráció oldal
│           ├── report-list/          # Főoldal (bejelentések)
│           ├── report-detail/        # Bejelentés részletei
│           ├── report-form/          # Bejelentés létrehozás/szerkesztés
│           ├── profile/              # Profil kezelés
│           ├── statistics/           # Statisztikák
│           ├── admin-reports/        # Admin: bejelentés moderálás
│           ├── admin-users/          # Admin: felhasználó kezelés
│           └── admin-categories/     # Admin: kategória kezelés
├── public/                           # Statikus fájlok
├── angular.json                      # Angular projekt konfiguráció
├── package.json                      # NPM függőségek
├── tsconfig.json                     # TypeScript konfiguráció
├── tsconfig.app.json                 # App-specifikus TS konfig
└── tsconfig.spec.json                # Teszt TS konfig
```

---

## 12. Környezeti konfiguráció

### Development (`environment.ts`)
```typescript
export const environment = {
    production: false,
    apiUrl: 'http://localhost:8000/api',
    storageUrl: 'http://localhost:8000/storage'
};
```

### Angular konfiguráció (`angular.json`)
- **Leaflet CSS** globálisan beágyazva
- **Production build:** optimalizáció, tree-shaking, budget korlátok
  - Initial bundle: max 500 KB (figyelmeztetés), max 1 MB (hiba)
- **Development:** source maps, optimalizáció kikapcsolva

### TypeScript (`tsconfig.json`)
- **Strict mód:** minden strict ellenőrzés bekapcsolva
- **Angular compiler:** strictInjectionParameters, strictTemplates, strictInputAccessors

---

## 13. Összefoglaló

| Szempont | Részlet |
|---|---|
| **Oldalak száma** | 10 (4 publikus + 3 védett + 3 admin) |
| **Szolgáltatások** | 5 (Auth, Report, Vote, Category, Admin) |
| **Megosztott komponensek** | 2 (Navbar, Map) |
| **Guardok** | 2 (authGuard, adminGuard) |
| **Interceptor** | 1 (authInterceptor - token csatolás) |
| **Routing** | Lazy-loaded, 3 szintű jogosultság |
| **Állapotkezelés** | Angular Signals |
| **Térkép** | Leaflet + OpenStreetMap |
| **Dizájn** | Egyedi sci-fi téma, Bootstrap 5.3, reszponzív |
| **Betűtípusok** | Orbitron, Inter, Share Tech Mono |
