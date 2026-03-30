# UFO Észlelés Jelentő Rendszer – Vizsgaremek Prezentáció

---

## 1. dia – Címlap

**UFO Észlelés Jelentő Rendszer**
Paranormális jelenségek bejelentő és szavazó webalkalmazás

Készítette: [Neved]
Képzés: [Képzés neve]
Év: 2026

---

## 2. dia – Mi ez a projekt?

- Egy teljes értékű **full-stack webalkalmazás**, amelyben a felhasználók **paranormális jelenségeket** jelenthetnek be
- UFO-észlelések, szellemek, Bigfoot, crop circle-ök és más anomáliák rögzítése
- Közösségi **szavazó rendszer** az egyes bejelentések hitelességének értékelésére
- **Adminisztrációs felület** a tartalom moderálásához
- **Interaktív térkép** az észlelések helyének megjelölésére

---

## 3. dia – Felhasznált technológiák

| Réteg | Technológia |
|-------|-------------|
| **Backend** | Laravel 11 (PHP 8.3) |
| **Frontend** | Angular 21 (TypeScript 5.9) |
| **Adatbázis** | SQLite / MySQL |
| **Autentikáció** | Laravel Sanctum (API token) |
| **Térkép** | Leaflet 1.9.4 + OpenStreetMap |
| **UI keretrendszer** | Bootstrap 5.3 (sci-fi téma) |
| **Ikonok** | Bootstrap Icons 1.11 |
| **Betűtípusok** | Google Fonts: Orbitron, Share Tech Mono, Inter |

---

## 4. dia – Tervezési fázis: Adatbázis terv

### 5 fő tábla:

- **users** – felhasználók (name, email, role: user/admin, is_banned)
- **categories** – kategóriák (9 beépített: UFO Észlelés, Földönkívüli, Kísértet, Crop Circle, Bigfoot, Tengeri Szörny, Poltergeist, Időhurok, Egyéb)
- **reports** – bejelentések (cím, leírás, dátum, helyszín GPS-koordinátákkal, tanúk száma, státusz: pending/approved/rejected)
- **report_images** – képek a bejelentésekhez (max 10 kép, egyenként max 5 MB)
- **votes** – szavazatok (up/down, egyedi korlát: egy felhasználó – egy szavazat/bejelentés)

### Kapcsolatok:
- Egy felhasználónak → több bejelentése és szavazata lehet
- Egy kategóriába → több bejelentés tartozhat
- Egy bejelentéshez → több kép és szavazat tartozhat

---

## 5. dia – Tervezési fázis: Architektúra

```
┌──────────────────┐       REST API        ┌──────────────────┐
│                  │  ◄──────────────────►  │                  │
│  Angular 21 SPA  │   JSON + Bearer Token  │  Laravel 11 API  │
│  (localhost:4200) │                       │  (localhost:8000) │
│                  │                        │                  │
│  - Komponensek   │                        │  - Controllers   │
│  - Szolgáltatások│                        │  - Models        │
│  - Guardok       │                        │  - Middleware     │
│  - Interceptor   │                        │  - Form Requests │
└──────────────────┘                        └──────────────────┘
                                                     │
                                              ┌──────┴──────┐
                                              │  Adatbázis  │
                                              │  + Fájlok   │
                                              └─────────────┘
```

---

## 6. dia – Felhasználói szerepkörök

### Vendég (nem bejelentkezett):
- Jóváhagyott bejelentések böngészése
- Kategóriák megtekintése
- Statisztikák megtekintése
- Regisztráció / Bejelentkezés

### Bejelentkezett felhasználó:
- Új bejelentés létrehozása (képekkel, GPS koordinátákkal)
- Szavazás (up/down) bejelentésekre
- Saját bejelentések szerkesztése / törlése
- Profil kezelése

### Adminisztrátor:
- Bejelentések jóváhagyása / elutasítása
- Felhasználók tiltása / feloldása
- Kategóriák kezelése (CRUD)
- Admin statisztikák megtekintése

---

## 7. dia – Autentikáció és biztonság

- **Laravel Sanctum** token-alapú autentikáció
- Regisztráció: név, email (egyedi), jelszó (min. 8 karakter, megerősítéssel)
- Bejelentkezéskor API token generálódik → `sessionStorage`-ban tárolódik
- Minden védett kéréshez `Authorization: Bearer {token}` header
- **Angular auth interceptor** automatikusan csatolja a tokent
- **Guardok**: auth-guard (bejelentkezés ellenőrzés), admin-guard (admin jogosultság)
- **CheckBanned middleware**: kitiltott felhasználók nem érhetik el az API-t
- **Soft delete**: adatok nem törlődnek véglegesen, visszaállíthatók

---

## 8. dia – Speciális funkció: Képfeltöltés rendszer

### Hogyan működik?
1. A felhasználó kiválaszt képeket (max **10 db**, egyenként max **5 MB**)
2. Támogatott formátumok: **JPEG, PNG, GIF, WebP**
3. A frontend **base64 előnézetet** generál a kiválasztott fájlokról
4. `FormData` objektumban küldés a backend felé
5. A Laravel a `storage/app/public/report_images/` mappába menti
6. Az adatbázisban az elérési út kerül tárolásra

### Frontend élmény:
- Feltöltés előtti **előnézet** (thumbnail)
- Szerkesztésnél: meglévő képek megjelenítése + új hozzáadása
- **Lightbox galéria**: képek nagyítása, nyilakkal navigálás (billentyűzet támogatás: ←, →, Esc)
- Képek törlése (tulajdonos vagy admin)

---

## 9. dia – Speciális funkció: Leaflet térkép integráció

### Interaktív térkép a bejelentésekhez

- **Leaflet 1.9.4** nyílt forráskódú térképkönyvtár
- **OpenStreetMap** csempék (tile layer)
- Két használati mód:
  1. **Olvasó mód**: Bejelentés helyének megjelenítése marker-rel
  2. **Szerkesztő mód**: Kattintással koordináta kiválasztás az űrlapon

### Technikai megoldások:
- Angular `NgZone` optimalizáció – a térkép események a zone-on kívül futnak (teljesítmény)
- Dinamikus marker elhelyezés és frissítés
- Webpack kompatibilis marker ikon fix
- Minden jóváhagyott, koordinátával rendelkező bejelentés megjelenik a térképen

---

## 10. dia – Speciális funkció: Szavazó és hitelesség rendszer

### Szavazás logika:
- Minden bejelentkezet felhasználó **egy szavazatot** adhat bejelentésenként
- **Upvote** (👍) vagy **Downvote** (👎)
- Ha ugyanazt választja újra → szavazat **visszavonása**
- Ha másikat választ → szavazat **módosítása**
- Adatbázis szinten egyedi korlát (unique constraint) biztosítja az integritást

### Hitelesség pontszám:
- **Képlet**: `upvote-ok száma – downvote-ok száma`
- Megjelenik minden bejelentés kártyáján
- Rendezés hitelesség alapján lehetséges
- **Top 3 leghitelesebb** bejelentés kiemelt megjelenítéssel a főoldalon (automatikus körhinta, 5 mp-es váltás)

---

## 11. dia – Bejelentés életciklus (státusz workflow)

```
Felhasználó létrehozza
        │
        ▼
   ┌──────────┐
   │ PENDING  │  ← Alapértelmezett státusz
   │ (függőben)│
   └────┬─────┘
        │
   Admin döntés
        │
   ┌────┴────┐
   ▼         ▼
┌────────┐ ┌────────┐
│APPROVED│ │REJECTED│
│(elfogad)│ │(elutasít)│
└────────┘ └────────┘
     │
     ▼
 Nyilvánosan
  látható
```

- Új bejelentés → automatikusan **„pending"** (függőben)
- Csak admin hagyhatja jóvá vagy utasíthatja el
- A nyilvános listában csak a **jóváhagyott** bejelentések jelennek meg
- A tulajdonos és az admin a függőben lévőket is látja

---

## 12. dia – Frontend: Főoldal és szűrés

### Főoldal funkciók:
- **Top 3 körhinta**: Automatikusan váltakozó, leghitelesebb bejelentések
- **Szűrők**: kategória, dátum tartomány (tól-ig), rendezés (dátum, cím, hitelesség)
- **Lapozás**: 9 bejelentés oldalanként
- **Debounced szűrés**: 400ms késleltetés (ne terhelje túl a szervert)
- **Kártyás megjelenítés**: kép előnézet, státusz badge, hitelesség pontszám

### Sci-fi téma:
- Sötét kék/lila színvilág
- Izzó (glow) effektek
- Orbitron betűtípus a címeknél
- Reszponzív: mobil, tablet, asztali

---

## 13. dia – Frontend: Bejelentés részletek

- Teljes bejelentés megtekintése minden adattal
- **Képgaléria lightbox**: kattintás → teljes képernyős nézet, nyilakkal navigáció
- **Szavazó gombok**: upvote/downvote vizuális visszajelzéssel
- **Térkép megjelenítés**: ha van koordináta, Leaflet térképen megjelenik
- Bejelentő felhasználó neve, kategória, dátum, tanúk száma
- Tulajdonos/admin: szerkesztés és törlés gombok

---

## 14. dia – Admin felület

### Bejelentések kezelése:
- Összes bejelentés listája (szűrhető státusz szerint)
- Jóváhagyás / Elutasítás gombok
- Törlés lehetőség

### Felhasználók kezelése:
- Kereshető felhasználó lista
- Tiltás / Feloldás (kitiltott felhasználó nem tud bejelentkezni)
- Bejelentések száma felhasználónként

### Kategóriák kezelése:
- Új kategória létrehozása
- Meglévő szerkesztése / törlése
- Inline szerkesztés az űrlapon

---

## 15. dia – Statisztikák

### Publikus statisztikák:
- Összes bejelentés, felhasználó, szavazat száma
- Státusz szerinti megoszlás (függőben / jóváhagyott / elutasított)
- **Kategória szerinti megoszlás** (vízszintes sávdiagram)
- Top 5 leghitelesebb bejelentés
- 5 legutóbbi jóváhagyott bejelentés

### Admin statisztikák:
- Kiterjesztett adatok: tiltott felhasználók száma, kategóriák száma
- Bejelentések kategória szerinti bontása

---

## 16. dia – Backend: API végpontok áttekintése

| Csoport | Végpontok | Példák |
|---------|----------|--------|
| **Publikus** | 8 végpont | `GET /api/reports`, `GET /api/categories`, `GET /api/statistics` |
| **Felhasználói** | 9 végpont | `POST /api/reports`, `POST /api/reports/{id}/vote`, `POST /api/reports/{id}/images` |
| **Admin** | 9 végpont | `PUT /api/admin/reports/{id}/approve`, `PUT /api/admin/users/{id}/ban` |
| **Összesen** | **~26 végpont** | RESTful konvenciók |

- Minden végpont **JSON** formátumban kommunikál
- **Form Request** validáció magyar hibaüzenetekkel
- **Soft delete** az összes felhasználói tartalomra

---

## 17. dia – Validáció és hibaüzenetek

### Backend validáció (Form Request-ek):
- **Regisztráció**: név (kötelező), email (egyedi), jelszó (min. 8 karakter + megerősítés)
- **Bejelentés**: kategória (létező), cím, leírás, dátum (nem jövőbeli), koordináták (helyes tartomány)
- **Képfeltöltés**: max méret, formátum ellenőrzés
- **Szavazat**: típus ellenőrzés (up/down)

### Frontend validáció:
- Űrlap mezők ellenőrzése küldés előtt
- Hibákat a backend is visszajelzi → felhasználóbarát megjelenítés

---

## 18. dia – Tesztadatok (Seeder-ek)

### Beépített tesztadatok:
- **Felhasználók**: admin@ufo.hu (admin), patrik@ufo.hu, odett@ufo.hu, kisspeter@ufo.hu stb.
- **9 kategória**: UFO Észlelés, Földönkívüli, Kísértet/Szellem, Crop Circle, Bigfoot, Tengeri Szörny, Poltergeist, Időhurok, Egyéb
- **Bejelentések**: valós magyar helyszínek (Debrecen, Pécs, Bucsa, Mátra) GPS koordinátákkal
- **Szavazatok**: előre generált up/down szavazatok hitelesség pontszámokkal

Parancs: `php artisan migrate:fresh --seed`

---

## 19. dia – Összefoglalás

### A projekt főbb jellemzői:
- **Full-stack SPA** alkalmazás (Angular + Laravel REST API)
- **3 jogosultsági szint**: vendég, felhasználó, admin
- **Speciális funkciók**: képfeltöltés, Leaflet térkép, szavazó rendszer, hitelesség pontszám
- **Modern technológiák**: Angular 21 Signals, Standalone Components, Laravel Sanctum
- **Reszponzív, egyedi sci-fi dizájn** Bootstrap 5.3 alapon
- **Biztonság**: token autentikáció, validáció, soft delete, tiltás rendszer
- **~26 REST API végpont** teljes CRUD műveletekkel

## 20. dia – Köszönöm a figyelmet!


/*
Készíts egy magyar nyelvű, 21 diás prezentációt az alábbi tartalom alapján. A téma egy "UFO Észlelés Jelentő Rendszer" nevű vizsgaremek projekt bemutatása. Stílus: sötét, sci-fi hangulatú, kék-lila színvilág, modern és letisztult. Használj ikonokat és vizuális elemeket a szöveg mellé. A kódrészletek helyett inkább diagramokat, folyamatábrákat és táblázatokat jeleníts meg. A diák ne legyenek túlzsúfoltak – kevés szöveg, nagy betűméret, vizuális fókusz. Az architektúra diánál és a státusz workflow diánál használj folyamatábrát. A technológiák diánál logókat ha lehetséges.
*/