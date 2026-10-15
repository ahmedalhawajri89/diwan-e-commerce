<?php

namespace Database\Seeders;

/**
 * The words of the demonstration catalogue, in both languages.
 *
 * This replaces faker's Latin. A shop judged on its demo cannot show "Pariatur
 * quia ipsam" under a product, and the Arabic site cannot show English names
 * under Arabic headings.
 *
 * The copy describes the shape of each kind of piece and what it is for. It
 * deliberately makes no claim nobody could stand behind: no material, no
 * warranty, no lead time, no figure. Those are the owner's to supply.
 */
final class DemoCatalogue
{
    /** slug => [English name, Arabic name, English description, Arabic description] */
    public const CATEGORIES = [
        'living-room' => [
            'Living Room', 'غرفة المعيشة',
            'Sofas, armchairs and the pieces around them, for the room a home is lived in.',
            'كنبات وكراسٍ بذراعين وما حولها، للغرفة التي يُعاش فيها البيت.',
        ],
        'bedroom' => [
            'Bedroom', 'غرفة النوم',
            'Beds, and the storage that stands beside them.',
            'أسرّة، وما يقف بجانبها من تخزين.',
        ],
        'dining-room' => [
            'Dining Room', 'غرفة الطعام',
            'Tables, chairs and stools for the meals that bring people together.',
            'طاولات وكراسٍ ومقاعد للوجبات التي تجمع الناس.',
        ],
        'office' => [
            'Office', 'المكتب',
            'Desks, chairs and shelves for work done at home.',
            'مكاتب وكراسٍ ورفوف للعمل من البيت.',
        ],
        'storage' => [
            'Storage', 'التخزين',
            'Cabinets and shelves: a place for things, so the floor stays clear.',
            'خزائن ورفوف: مكان للأشياء، لتبقى الأرض خالية.',
        ],
        'lighting' => [
            'Lighting', 'الإضاءة',
            'Lamps that stand on the floor and light a corner.',
            'مصابيح تقف على الأرض وتضيء الزوايا.',
        ],
    ];

    /**
     * Each kind of piece: its Arabic noun, whether that noun is feminine (the
     * adjective agrees with it), and what it is for.
     *
     * English noun => [Arabic noun, feminine, English description, Arabic description]
     */
    public const TYPES = [
        'Sofa' => [
            'كنبة', true,
            'A sofa for the main wall of a living room: deep enough to sit back in, low enough to keep the room open. Hold the drawing beside it up against your own wall before you choose a colour.',
            'كنبة للجدار الرئيسي في غرفة المعيشة: عميقة بما يكفي لتسترخي فيها، ومنخفضة بما يُبقي الغرفة مفتوحة. قارن الرسم بجانبها بجدارك قبل أن تختار اللون.',
        ],
        'Armchair' => [
            'كرسي بذراعين', false,
            'A single armchair for a reading corner or the side of a sofa. It turns a spare corner into a place to sit.',
            'كرسي بذراعين لركن القراءة أو لجانب الكنبة. يحوّل الزاوية الفارغة إلى مكان للجلوس.',
        ],
        'Chair' => [
            'كرسي', false,
            'An upright chair for the dining table or a desk. Count one for every place at the table, and leave room behind each to pull it out.',
            'كرسي مستقيم لطاولة الطعام أو للمكتب. احسب كرسيًا لكل مكان على الطاولة، واترك خلف كل واحد مساحة لسحبه.',
        ],
        'Stool' => [
            'مقعد مرتفع', false,
            'A stool for a kitchen counter, a bar, or a spare seat by the door. Measure the counter first: the seat should sit comfortably below it.',
            'مقعد مرتفع لطاولة المطبخ أو البار، أو مقعد إضافي قرب الباب. قِس ارتفاع الطاولة أولًا: يجب أن يكون المقعد أدنى منها بمسافة مريحة.',
        ],
        'Table' => [
            'طاولة طعام', true,
            'A dining table for everyday meals and longer evenings with guests. Leave space around it for the chairs; the fit check counts the walkway for you.',
            'طاولة طعام للوجبات اليومية وللسهرات الطويلة مع الضيوف. اترك حولها مساحة للكراسي، وفحص الملاءمة يحسب لك ممر الحركة.',
        ],
        'Desk' => [
            'مكتب', false,
            'A desk with a clear top for a screen, a lamp and the work in between. It belongs under a window or along the wall of a spare room.',
            'مكتب بسطح خالٍ لشاشة ومصباح وما بينهما من عمل. مكانه تحت النافذة أو بمحاذاة جدار في غرفة جانبية.',
        ],
        'Bed' => [
            'سرير', false,
            'A bed frame with a headboard to lean against. Check the bedroom door and the stairs as well as the floor: a bed has to get in before it can fit.',
            'هيكل سرير بلوح رأس تستند إليه. افحص باب الغرفة والدرج كما تفحص الأرضية: على السرير أن يدخل قبل أن يناسب.',
        ],
        'Cabinet' => [
            'خزانة', true,
            'A cabinet with doors, for what should be kept but not seen. It stands against a wall in a living room, a hallway or a bedroom.',
            'خزانة بأبواب لما يُحفظ ولا يُعرض. تقف بمحاذاة الجدار في غرفة المعيشة أو الممر أو غرفة النوم.',
        ],
        'Lamp' => [
            'مصباح أرضي', false,
            'A floor lamp for reading light beside a chair or a sofa. It takes almost no floor, so it stands where a table would not fit.',
            'مصباح أرضي لضوء القراءة بجانب كرسي أو كنبة. لا يأخذ من الأرض إلا القليل، فيقف حيث لا تتسع طاولة.',
        ],
        'Shelf' => [
            'وحدة رفوف', true,
            'Open shelves for books and the things you want to see. Tall rather than wide, so they hold a lot on a narrow wall.',
            'رفوف مفتوحة للكتب ولما تحب أن تراه. طويلة أكثر منها عريضة، فتتسع لكثير على جدار ضيق.',
        ],
    ];

    /** English adjective => [Arabic masculine, Arabic feminine] */
    public const ADJECTIVES = [
        'Modern' => ['عصري', 'عصرية'],
        'Classic' => ['كلاسيكي', 'كلاسيكية'],
        'Rustic' => ['ريفي', 'ريفية'],
        'Minimalist' => ['بسيط', 'بسيطة'],
        'Cozy' => ['مريح', 'مريحة'],
        'Elegant' => ['أنيق', 'أنيقة'],
        'Compact' => ['صغير الحجم', 'صغيرة الحجم'],
        'Vintage' => ['بطراز عتيق', 'بطراز عتيق'],
        'Luxury' => ['فاخر', 'فاخرة'],
    ];

    /** A demo price range per kind of piece, in the shop's currency. */
    public const PRICES = [
        'Sofa' => [1200, 2600],
        'Armchair' => [450, 1100],
        'Chair' => [120, 320],
        'Stool' => [80, 220],
        'Table' => [600, 1800],
        'Desk' => [400, 1200],
        'Bed' => [900, 2400],
        'Cabinet' => [500, 1500],
        'Lamp' => [90, 380],
        'Shelf' => [250, 900],
    ];

    /**
     * Colours a piece can come in. Each is a valid CSS colour name, because the
     * catalogue's swatch paints the value it is given.
     */
    public const COLOURS = ['Beige', 'Brown', 'Grey', 'Black', 'White', 'Olive', 'Navy', 'Tan'];

    /** "Cozy" + "Sofa" => "كنبة مريحة". */
    public static function arabicName(string $adjective, string $type): string
    {
        [$noun, $feminine] = self::TYPES[$type];

        return $noun.' '.self::ADJECTIVES[$adjective][$feminine ? 1 : 0];
    }
}
