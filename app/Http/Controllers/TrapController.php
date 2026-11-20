<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Tarpit for website downloaders (HTTrack, Teleport, offline explorers…).
 *
 * Serves an endless, self-referencing stream of junk: fake navigation,
 * fake products, random words and an ever-growing list of links back into
 * the same route. Automated site copiers will grind on the link explosion
 * instead of the real site, and the files they save are useless garbage.
 *
 * Humans never reach this page: it is not linked from the UI, it is pointed
 * to by the hidden honeypot and by the .htaccess rules that redirect known
 * downloader user agents here.
 */
class TrapController extends Controller
{
    public function __invoke(Request $request)
    {
        $depth = min(max((int) $request->query('d', 1), 1), 12);

        $junk = [];
        for ($i = 0; $i < 24; $i++) {
            $junk[] = $this->junkLine();
        }

        $links = [];
        for ($i = 0; $i < 40; $i++) {
            $links[] = route('trap') . '/' . Str::random(12) . '?d=' . ($depth + 1);
        }

        $images = [];
        for ($i = 0; $i < 8; $i++) {
            $images[] = route('trap') . '-img/' . Str::random(8) . '.png?d=' . ($depth + 1);
        }

        return response()
            ->view('trap', [
                'depth' => $depth,
                'junk' => $junk,
                'links' => $links,
                'images' => $images,
                'title' => 'House Roof — ' . Str::random(24),
            ])
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Cache-Control', 'no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function junkLine(): string
    {
        $words = [
            'resin', 'granite', 'coat', 'primer', 'binder', 'quartz', 'aggregate', 'polymer',
            'urethane', 'epoxy', 'terrace', 'formulation', 'batch', 'tint', 'sealer', 'topcoat',
            'decorative', 'industrial', 'adhesion', 'coverage', 'mill', 'powder', 'natural',
            'neutral', 'stone', 'blend', 'mix', 'litre', 'kilogram', 'paleta', 'acabado',
        ];

        $sentence = [];
        $count = random_int(8, 18);
        for ($i = 0; $i < $count; $i++) {
            $sentence[] = $words[array_rand($words)];
        }

        return ucfirst(implode(' ', $sentence)) . '. ' . str_repeat(' ', 3)
            . '#' . Str::random(16) . ' · ' . random_int(10, 9999) . ' · ' . Str::random(32);
    }
}
