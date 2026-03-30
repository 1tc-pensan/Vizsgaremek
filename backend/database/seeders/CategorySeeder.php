<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'UFO Észlelés',      'description' => 'Azonosítatlan repülő tárgyak és légi jelenségek bejelentései.'],
            ['name' => 'Földönkívüli',       'description' => 'Idegen lények találkozásai és emlékezetek.'],
            ['name' => 'Kísértet / Szellem', 'description' => 'Természetfeletti entitások és kisértett helyek.'],
            ['name' => 'Crop Circle',        'description' => 'Rejtélyes búzamező alakzatok és leszállási nyomok.'],
            ['name' => 'Bigfoot / Sasquatch','description' => 'Nagy emberszabású lény észlelések.'],
            ['name' => 'Tengeri Szörny',     'description' => 'Azonosítatlan vízi lények.'],
            ['name' => 'Poltergeist',        'description' => 'Zajokkal és tárgyak mozgásával járó jelenségek.'],
            ['name' => 'Időhurok / Anomália','description' => 'Idővel kapcsolatos furcsa tapasztalatok.'],
            ['name' => 'Egyéb Paranormális', 'description' => 'Minden más megmagyarázhatatlan jelenség.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
