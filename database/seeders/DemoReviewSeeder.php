<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Reviews for a developer's machine, so the review list, the rating summary
 * and the home page's quote can be seen and designed against.
 *
 * NEVER on a real shop. design.md's honesty rules forbid invented testimonials,
 * and these are invented: written by the developer, signed by accounts that
 * belong to nobody. So the seeder refuses to run anywhere but `local` and
 * `testing`, exactly as AdminUserSeeder does, and SeederTest holds it to that.
 */
class DemoReviewSeeder extends Seeder
{
    /** Demo reviewers. Plain names, not "John Doe"; `.test` addresses that reach no one. */
    private const REVIEWERS = [
        ['Lina Haddad', 'lina@diwan.test'],
        ['Omar Khatib', 'omar@diwan.test'],
        ['Sara Mansour', 'sara@diwan.test'],
        ['Yousef Abbas', 'yousef@diwan.test'],
        ['Maya Rizk', 'maya@diwan.test'],
        ['Karim Saleh', 'karim@diwan.test'],
    ];

    /** [rating, comment]. About fit and use — the things this shop is about. */
    private const REVIEWS = [
        [5, 'It went exactly where the drawing said it would, with the walkway to spare.'],
        [5, 'I set my room first and the page told me it was tight. It was, but it works.'],
        [4, 'Good piece. Check the doorway as well as the floor; we had to tilt it in.'],
        [5, 'The scale drawing next to the person made the size easy to picture.'],
        [4, 'Lovely colour in daylight, a little darker in the evening.'],
        [3, 'Fits well, but it sits lower than I expected. Look at the height before you buy.'],
        [5, 'دخلت المكان تمامًا كما أظهر الرسم، وبقي ممر الحركة واسعًا.'],
        [5, 'أدخلت مقاس غرفتي أولًا فأخبرتني الصفحة أنها ضيقة. كانت كذلك، لكنها مناسبة.'],
        [4, 'قطعة جميلة. افحص الباب كما تفحص الأرضية، اضطررنا لإمالتها لندخلها.'],
        [5, 'الرسم بجانب الإنسان جعل تصوّر الحجم سهلًا.'],
        [4, 'اللون جميل في ضوء النهار، وأغمق قليلًا في المساء.'],
        [3, 'مناسبة، لكنها أخفض مما توقعت. انتبه للارتفاع قبل الشراء.'],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('DemoReviewSeeder skipped: invented reviews never reach a real shop.');

            return;
        }

        // Once only: a second seed must not double the reviews.
        if (Review::query()->exists()) {
            return;
        }

        $reviewers = collect(self::REVIEWERS)->map(fn ($r) => User::firstOrCreate(
            ['email' => $r[1]],
            ['name' => $r[0], 'password' => Hash::make(Str::random(40))]
        ));

        // About half the catalogue, so pieces with no reviews show their empty state too.
        $products = Product::where('status', 'active')->inRandomOrder()->get();
        $reviewed = $products->take((int) ceil($products->count() / 2));

        foreach ($reviewed as $product) {
            foreach ($reviewers->random(random_int(1, 3)) as $reviewer) {
                [$rating, $comment] = Arr::random(self::REVIEWS);

                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $reviewer->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => 'approved',
                ]);
            }
        }
    }
}
