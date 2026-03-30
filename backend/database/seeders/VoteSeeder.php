<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    public function run(): void
    {
        $patrik  = User::where('email', 'patrik@ufo.hu')->first();
        $odett   = User::where('email', 'odett@ufo.hu')->first();
        $kissP   = User::where('email', 'kisspeter@ufo.hu')->first();
        $horvath = User::where('email', 'horvatheva@ufo.hu')->first();
        $sos     = User::where('email', 'soselemer@ufo.hu')->first();
        $ali     = User::where('email', 'alimihaly@ufo.hu')->first();

        $reports = Report::all()->keyBy('title');

        // Segédfüggvény
        $vote = function (string $title, $user, string $type) use ($reports) {
            $report = $reports[$title] ?? null;
            if (!$report || !$user) return;
            Vote::firstOrCreate(
                ['report_id' => $report->id, 'user_id' => $user->id],
                ['vote_type' => $type]
            );
        };

        $votes = [
            // Bucsa (5 up)
            ['Rejtélyes lény a bucsa horgásztóban', $patrik,  'up'],
            ['Rejtélyes lény a bucsa horgásztóban', $odett,   'up'],
            ['Rejtélyes lény a bucsa horgásztóban', $kissP,   'up'],
            ['Rejtélyes lény a bucsa horgásztóban', $horvath, 'up'],
            ['Rejtélyes lény a bucsa horgásztóban', $sos,     'up'],
            // Debrecen (3 up, 1 down)
            ['Háromszögű tárgy Debrecen felett', $odett,   'up'],
            ['Háromszögű tárgy Debrecen felett', $kissP,   'up'],
            ['Háromszögű tárgy Debrecen felett', $horvath, 'up'],
            ['Háromszögű tárgy Debrecen felett', $ali,     'down'],
            // Pécs (2 up, 1 down)
            ['Furcsa hangok a pécsi várban', $patrik, 'up'],
            ['Furcsa hangok a pécsi várban', $kissP,  'down'],
            ['Furcsa hangok a pécsi várban', $sos,    'up'],
            // Mátra (3 up, 1 down)
            ['Hatalmas lény nyomai a Mátrában', $patrik, 'up'],
            ['Hatalmas lény nyomai a Mátrában', $kissP,  'up'],
            ['Hatalmas lény nyomai a Mátrában', $sos,    'up'],
            ['Hatalmas lény nyomai a Mátrában', $ali,    'down'],
            // Győr (1 up, 2 down)
            ['Villogó háromszög Győr felett', $odett, 'up'],
            ['Villogó háromszög Győr felett', $sos,   'down'],
            ['Villogó háromszög Győr felett', $ali,   'down'],
            // Sopron (2 up, 1 down)
            ['Ismeretlen fény Sopron felett', $patrik, 'down'],
            ['Ismeretlen fény Sopron felett', $odett,  'up'],
            ['Ismeretlen fény Sopron felett', $ali,    'up'],
            // M7 (1 up, 2 down)
            ['Ismétlődő percek az M7-esen', $patrik,  'down'],
            ['Ismétlődő percek az M7-esen', $horvath, 'up'],
            ['Ismétlődő percek az M7-esen', $ali,     'down'],
            // Nógrád (1 up, 3 down)
            ['Árnyalak a kastély ablakában (Nógrád)', $patrik,  'down'],
            ['Árnyalak a kastély ablakában (Nógrád)', $odett,   'up'],
            ['Árnyalak a kastély ablakában (Nógrád)', $sos,     'down'],
            ['Árnyalak a kastély ablakában (Nógrád)', $horvath, 'down'],
            // Alföld (1 up, 2 down)
            ['Furcsa fénygömbök egy alföldi falunál', $patrik, 'up'],
            ['Furcsa fénygömbök egy alföldi falunál', $kissP,  'down'],
            ['Furcsa fénygömbök egy alföldi falunál', $sos,    'down'],
            // Miskolc (1 up, 3 down)
            ['Maguktól mozgó tárgyak egy panelben (Miskolc)', $patrik,  'down'],
            ['Maguktól mozgó tárgyak egy panelben (Miskolc)', $odett,   'down'],
            ['Maguktól mozgó tárgyak egy panelben (Miskolc)', $horvath, 'up'],
            ['Maguktól mozgó tárgyak egy panelben (Miskolc)', $ali,     'down'],
            // Tatabánya (0 up, 4 down)
            ['Furcsa jelenség egy elhagyatott gyárban (Tatabánya)', $patrik, 'down'],
            ['Furcsa jelenség egy elhagyatott gyárban (Tatabánya)', $odett,  'down'],
            ['Furcsa jelenség egy elhagyatott gyárban (Tatabánya)', $kissP,  'down'],
            ['Furcsa jelenség egy elhagyatott gyárban (Tatabánya)', $sos,    'down'],
        ];

        foreach ($votes as [$title, $user, $type]) {
            $vote($title, $user, $type);
        }
    }
}