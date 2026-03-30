<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('email', 'admin@ufo.hu')->first();
        $patrik   = User::where('email', 'patrik@ufo.hu')->first();
        $odett    = User::where('email', 'odett@ufo.hu')->first();
        $kissP    = User::where('email', 'kisspeter@ufo.hu')->first();
        $horvath  = User::where('email', 'horvatheva@ufo.hu')->first();
        $sos      = User::where('email', 'soselemer@ufo.hu')->first();
        $ali      = User::where('email', 'alimihaly@ufo.hu')->first();

        $categories = Category::all()->keyBy('name');

        $reports = [
            [
                'user_id'     => $patrik->id,
                'category_id' => $categories['UFO Észlelés']->id,
                'title'       => 'Háromszögű tárgy Debrecen felett',
                'description' => 'Tegnap este 22:30-kor egy háromszögű, fények által megvilágított tárgyat láttam a debreceni égbolt felett. Teljes csöndben mozgott és mintegy 3 percig volt látható.',
                'latitude'    => 47.5316,
                'longitude'   => 21.6273,
                'date'        => '2025-12-15',
                'witnesses'   => 3,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $odett->id,
                'category_id' => $categories['Kísértet / Szellem']->id,
                'title'       => 'Furcsa hangok a pécsi várban',
                'description' => 'Éjjeli látogatáskor ismeretlen hangokat hallottunk a várpincében. Mintha valakit vonszoltak volna a kövön.',
                'latitude'    => 46.0747,
                'longitude'   => 18.2214,
                'date'        => '2025-11-20',
                'witnesses'   => 5,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $odett->id,
                'category_id' => $categories['Crop Circle']->id,
                'title'       => 'Búzakör Győr mellett',
                'description' => 'Egy tanyán élő ismerős talált egy tökéletes kör alakzatot a búzamezőn. Átmérője kb. 25 méter.',
                'latitude'    => 47.6875,
                'longitude'   => 17.6504,
                'date'        => '2025-07-10',
                'witnesses'   => 2,
                'status'      => 'pending',
            ],
            [
                'user_id'     => $patrik->id,
                'category_id' => $categories['UFO Észlelés']->id,
                'title'       => 'Villogó háromszög Győr felett',
                'description' => 'Győr külvárosában láttam egy háromszög alakú, villogó fényekkel rendelkező objektumot. Teljesen hangtalanul mozgott, majd hirtelen felgyorsult és eltűnt az égbolton.',
                'latitude'    => 47.6875,
                'longitude'   => 17.6504,
                'date'        => '2025-10-05',
                'witnesses'   => 2,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $kissP->id,
                'category_id' => $categories['Kísértet / Szellem']->id,
                'title'       => 'Árnyalak a kastély ablakában (Nógrád)',
                'description' => 'Egy elhagyatott kastélynál egy emberi alakot láttam az egyik emeleti ablakban. Amikor közelebb mentem, eltűnt, de az ablak mögött még mozgást hallottam.',
                'latitude'    => 47.9000,
                'longitude'   => 19.5000,
                'date'        => '2025-09-18',
                'witnesses'   => 1,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $kissP->id,
                'category_id' => $categories['Poltergeist']->id,
                'title'       => 'Maguktól mozgó tárgyak egy panelben (Miskolc)',
                'description' => 'Egy miskolci lakásban tárgyak kezdtek el maguktól leesni a polcokról. Az ajtók becsapódtak, miközben senki nem volt a közelben. Többször is megtörtént egy este alatt.',
                'latitude'    => 48.1031,
                'longitude'   => 20.7784,
                'date'        => '2025-11-01',
                'witnesses'   => 4,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $sos->id,
                'category_id' => $categories['Időhurok / Anomália']->id,
                'title'       => 'Ismétlődő percek az M7-esen',
                'description' => 'Az M7-es autópályán haladva ugyanazt a szakaszt láttam háromszor egymás után, mintha visszakerültem volna az időben. Az óra is ugyanazt az időt mutatta percekig.',
                'latitude'    => 47.0000,
                'longitude'   => 18.3000,
                'date'        => '2025-08-22',
                'witnesses'   => 1,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $horvath->id,
                'category_id' => $categories['Bigfoot / Sasquatch']->id,
                'title'       => 'Hatalmas lény nyomai a Mátrában',
                'description' => 'A Mátrában túrázva hatalmas, emberhez hasonló lábnyomokat találtam az erdőben. Nem sokkal később furcsa, mély morgást hallottam a fák közül.',
                'latitude'    => 47.9009,
                'longitude'   => 20.0570,
                'date'        => '2025-06-14',
                'witnesses'   => 3,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $ali->id,
                'category_id' => $categories['Egyéb Paranormális']->id,
                'title'       => 'Furcsa fénygömbök egy alföldi falunál',
                'description' => 'Egy alföldi faluban több fénygömb jelent meg az égen, amelyek össze-vissza mozogtak. Néha eltűntek, majd újra felbukkantak más helyen.',
                'latitude'    => 46.5000,
                'longitude'   => 20.0000,
                'date'        => '2025-10-30',
                'witnesses'   => 6,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $ali->id,
                'category_id' => $categories['Tengeri Szörny']->id,
                'title'       => 'Rejtélyes lény a bucsa horgásztóban',
                'description' => 'A Bucsa horgásztónál több szemtanú egy hatalmas, sötét, hosszú testű lényt látott a víz felszínén. A lény rövid ideig mozdulatlan volt, majd lassan alámerült. A víz erősen fodrozódott utána, mintha valami nagy mozogna a mélyben. Egyesek szerint halk, furcsa hang is hallatszott a víz felől.',
                'latitude'    => 47.2165,
                'longitude'   => 21.0030,
                'date'        => '2025-12-01',
                'witnesses'   => 5,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $sos->id,
                'category_id' => $categories['UFO Észlelés']->id,
                'title'       => 'Ismeretlen fény Sopron felett',
                'description' => 'Sopron határában egy vakítóan fehér fénypont jelent meg az égbolton, amely néhány percig mozdulatlanul állt, majd hirtelen eltűnt. Semmiféle hangot nem hallottam közben.',
                'latitude'    => 47.6813,
                'longitude'   => 16.5845,
                'date'        => '2025-10-12',
                'witnesses'   => 2,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $ali->id,
                'category_id' => $categories['Kísértet / Szellem']->id,
                'title'       => 'Furcsa jelenség egy elhagyatott gyárban (Tatabánya)',
                'description' => 'Egy elhagyatott tatabányai gyárépületben fotózás közben emberi sziluettet láttam az egyik folyosón. Amikor odamentem, senkit sem találtam, de hideg volt és nyomasztó érzés kerített hatalmába.',
                'latitude'    => 47.5862,
                'longitude'   => 18.4043,
                'date'        => '2025-11-05',
                'witnesses'   => 1,
                'status'      => 'approved',
            ],
        ];

        foreach ($reports as $data) {
            Report::firstOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}


/*

Kategória: UFO Észlelés
Cím: Villogó háromszög Győr felett
Leírás:
Győr külvárosában láttam egy háromszög alakú, villogó fényekkel rendelkező objektumot. Teljesen hangtalanul mozgott, majd hirtelen felgyorsult és eltűnt az égbolton.
Szélesség: 47.6875
Hosszúság: 17.6504

Kategória: Kísértet / Szellem
Cím: Árnyalak a kastély ablakában (Nógrád)
Leírás:
Egy elhagyatott kastélynál egy emberi alakot láttam az egyik emeleti ablakban. Amikor közelebb mentem, eltűnt, de az ablak mögött még mozgást hallottam.
Szélesség: 47.9000
Hosszúság: 19.5000

Kategória: Poltergeist
Cím: Maguktól mozgó tárgyak egy panelben (Miskolc)
Leírás:
Egy miskolci lakásban tárgyak kezdtek el maguktól leesni a polcokról. Az ajtók becsapódtak, miközben senki nem volt a közelben. Többször is megtörtént egy este alatt.
Szélesség: 48.1031
Hosszúság: 20.7784

Kategória: Időhurok / Anomália
Cím: Ismétlődő percek az M7-esen
Leírás:
Az M7-es autópályán haladva ugyanazt a szakaszt láttam háromszor egymás után, mintha visszakerültem volna az időben. Az óra is ugyanazt az időt mutatta percekig.
Szélesség: 47.0000
Hosszúság: 18.3000

Kategória: Bigfoot / Sasquatch
Cím: Hatalmas lény nyomai a Mátrában
Leírás:
A Mátrában túrázva hatalmas, emberhez hasonló lábnyomokat találtam az erdőben. Nem sokkal később furcsa, mély morgást hallottam a fák közül.
Szélesség: 47.9009
Hosszúság: 20.0570

Kategória: Egyéb Paranormális
Cím: Furcsa fénygömbök egy alföldi falunál
Leírás:
Egy alföldi faluban több fénygömb jelent meg az égen, amelyek össze-vissza mozogtak. Néha eltűntek, majd újra felbukkantak más helyen.
Szélesség: 46.5000
Hosszúság: 20.0000
Patrik
Kategória: Tengeri Szörny
Cím: Rejtélyes lény a bucsa horgásztóban
Leírás:
A Bucsa horgásztónál több szemtanú egy hatalmas, sötét, hosszú testű lényt látott a víz felszínén. A lény rövid ideig mozdulatlan volt, majd lassan alámerült. A víz erősen fodrozódott utána, mintha valami nagy mozogna a mélyben. Egyesek szerint halk, furcsa hang is hallatszott a víz felől.
Szélesség: 47.2165
Hosszúság: 21.0030



*/