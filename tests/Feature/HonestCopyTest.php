<?php

/**
 * The shop says only what it can stand behind. These lines were in the copy
 * and were not true: an origin story that never happened, a rule about
 * returns stated as if measured, a making process nobody recorded, and a
 * promise that no piece is listed without its dimensions (the catalogue does
 * list them, marked "not recorded"). None of them may come back.
 */
dataset('untrue', [
    'Diwan began with a returned sofa',
    'eleven centimetres too long',
    'Nobody returns a sofa because the colour was wrong',
    'Joints fitted, edges eased',
    'No piece is listed until',
    'measured before it is photographed',
    'بدأ ديوان بأريكة مُرجَعة',
    'بأحد عشر سنتيمتراً',
    'لا أحد يُرجع أريكة لأن لونها',
    'مفاصل مركّبة، وحواف مهذّبة',
    'لا تُعرض قطعة قبل تسجيل',
    'تُقاس قبل أن تُصوَّر',
]);

it('no longer says what it cannot stand behind', function (string $line) {
    foreach (['en', 'ar'] as $locale) {
        foreach ([route('furnish.index'), route('furnish.about')] as $page) {
            $this->withSession(['locale' => $locale])->get($page)->assertOk()->assertDontSee($line, false);
        }
    }
})->with('untrue');

it('tells the story by what the shop does, in both languages', function () {
    $this->withSession(['locale' => 'en'])->get(route('furnish.about'))
        ->assertSee(__('diwan.about.body_one', [], 'en'))
        ->assertSee(__('diwan.about.p_two_t', [], 'en'));

    $this->withSession(['locale' => 'ar'])->get(route('furnish.about'))
        ->assertSee(__('diwan.about.body_one', [], 'ar'));
});
