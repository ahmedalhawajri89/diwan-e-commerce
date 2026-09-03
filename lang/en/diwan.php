<?php

/**
 * Diwan — storefront copy, English.
 *
 * Every string the storefront renders lives here. Keep the Arabic file's keys
 * identical; a missing key falls back to the key name, which ships broken UI.
 */

return [

    'brand' => [
        'name'     => 'DIWAN',
        'strap'    => 'Furniture, measured',
        'tagline'  => 'Every piece measured before it is photographed.',
        'colophon' => 'Made to be lived with',
    ],

    'nav' => [
        'home'      => 'Home',
        'catalogue' => 'Catalogue',
        'rooms'     => 'Rooms',
        'lookbook'  => 'Lookbook',
        'story'     => 'Our story',
        'reviews'   => 'Reviews',
        'contact'   => 'Contact',
        'menu'      => 'Menu',
        'close'     => 'Close',
        'primary'   => 'Primary',
        'mobile'    => 'Mobile',
        'language'  => 'Language',
        'browse'    => 'Browse',
        'shop_all'  => 'See everything',
        'by_room'   => 'By room',
        'by_piece'  => 'By piece',
        'help'      => 'Help',
    ],

    'account' => [
        'signed_in_as' => 'Signed in as',
        'dashboard'    => 'Dashboard',
        'profile'      => 'Profile',
        'orders'       => 'Orders',
        'sign_out'     => 'Sign out',
        'sign_in'      => 'Sign in',
        'register'     => 'Create account',
        'menu_for'     => 'Account menu for :name',
    ],

    'util' => [
        'search'      => 'Search',
        'saved'       => 'Saved',
        'saved_items' => 'Saved pieces',
        'bag'         => 'Bag',
        'bag_count'   => 'Bag, :count items',
        'bag_empty'   => 'Bag, empty',
    ],

    /* ── The fit system ──────────────────────────────────────────────── */

    'room' => [
        'label'        => 'My room',
        'title'        => 'Measure once. Every price tag answers.',
        'body'         => 'Give us the two numbers and every piece on the site tells you whether it fits, before you scroll past it.',
        'width'        => 'Width',
        'length'       => 'Length',
        'unit'         => 'cm',
        'save'         => 'Save room',
        'clear'        => 'Clear',
        'saved'        => 'Room saved. Fit is now shown on every piece.',
        'cleared'      => 'Room cleared.',
        'current'      => ':width × :length cm',
        'floor'        => 'Floor area',
        'unset'        => 'Not set',
        'set_prompt'   => 'Set your room',
        'change'       => 'Change',
        'hint'         => 'Between 100 and 2000 cm. Nothing is stored on your account.',
        'why'          => 'Why we ask',
    ],

    'fit' => [
        'fits'      => 'Fits',
        'tight'     => 'Tight',
        'no'        => 'Will not fit',
        'unknown'   => 'Not recorded',
        'no_room'   => 'Set your room',
        'set_room'  => 'Set your room',
        'legend'    => 'Fit against your room',
        'note' => [
            'too_long' => 'Longer than your longest wall.',
            'doorway'  => 'Wider than a standard 80 cm doorway on every face.',
            'walkway'  => 'Leaves under 75 cm to walk past.',
            'crowded'  => 'Takes more than a third of your floor.',
            'dominates' => 'Takes over half your floor on its own.',
        ],
        'explain' => [
            'fits'    => 'Goes against your longest wall and still leaves a walkway.',
            'unknown' => 'This piece has no dimensions on file. We will not guess them.',
        ],
        'share'     => ':percent% of your floor',
        'footprint' => 'Footprint',
    ],

    'scale' => [
        'label'        => 'Dimensions',
        'width'        => 'W',
        'depth'        => 'D',
        'height'       => 'H',
        'not_recorded' => 'Dimensions not recorded',
        'human'        => 'Shown beside a 175 cm person',
        'human_short'  => '175 cm',
        'caption'      => 'Drawn to scale from the recorded measurements.',
    ],

    /* ── Search ──────────────────────────────────────────────────────── */

    'search' => [
        'placeholder' => 'Search the catalogue',
        'label'       => 'Search products',
        'searching'   => 'Searching',
        'none'        => 'Nothing matches “:query”.',
        'error'       => 'Search is unavailable right now.',
        'browse_all'  => 'Browse the whole catalogue',
    ],

    'wishlist' => [
        'title'          => 'Saved pieces',
        'empty'          => 'Nothing saved yet.',
        'browse'         => 'Browse the catalogue',
        'loading'        => 'Loading',
        'move_to_bag'    => 'Move to bag',
        'move_named'     => 'Move :name to the bag',
        'remove'         => 'Remove',
        'remove_named'   => 'Remove :name',
        'save_named'     => 'Save :name',
        'update_failed'  => 'Could not update your saved pieces.',
        'move_failed'    => 'Could not move that to the bag.',
        'room_total'     => 'These pieces together',
        'unmeasured'     => ':count without dimensions on file',
    ],

    'newsletter' => [
        'title'       => 'One letter a month',
        'body'        => 'New pieces, what they are made of, and what they measure. No sales.',
        'email'       => 'Email address',
        'placeholder' => 'you@example.com',
        'submit'      => 'Subscribe',
    ],

    /* ── Home ────────────────────────────────────────────────────────── */

    'home' => [
        'title'         => 'Furniture, measured',
        'lede'          => 'Made once, properly.',
        'hero_body'     => 'Every piece is finished by hand and measured before it is photographed. You get the real numbers, not a mood board.',
        'hero_cta'      => 'See the catalogue',
        'hero_second'   => 'How fit works',
        'hero_alt'      => 'A linen sofa and a glass table in a bright living room.',
        'fit_title'     => 'The number that actually decides it',
        'fit_body'      => 'Nobody returns a sofa because the colour was wrong. They return it because it did not fit. So we put the answer on every card, before you click.',
        'fit_cta'       => 'Set your room',
        'most_saved'    => 'Most saved',
        'most_saved_note' => 'Ordered by how often they are set aside',
        'catalogue_cta' => 'See all pieces',
        'craft_title'   => 'Measured, then made',
        'craft_body'    => 'Joints fitted, edges eased, surfaces oiled. Nothing is rushed to hit a season, and nothing ships without its dimensions on file.',
        'craft_alt'     => 'A workshop bench with hand tools and an unfinished oak frame.',
        'craft_cta'     => 'Read our story',
        'reviews_title' => 'What buyers said',
        'reviews_all'   => 'All reviews',
        'review_on'     => 'on :product',
    ],

    /* ── Catalogue ───────────────────────────────────────────────────── */

    'catalogue' => [
        'title'        => 'Catalogue',
        'intro'        => 'Every piece we make, with its real measurements.',
        'count'        => ':count pieces',
        'showing'      => 'Showing :from–:to of :total',
        'filters'      => 'Filters',
        'filter_open'  => 'Filters and sorting',
        'apply'        => 'Apply',
        'clear'        => 'Clear all',
        'category'     => 'Category',
        'price'        => 'Price',
        'colour'       => 'Colour',
        'fit_filter'   => 'Fit',
        'fit_only'     => 'Only what fits my room',
        'sort'         => 'Sort',
        'sort_by'      => 'Sort by',
        'empty'        => 'Nothing matches those filters.',
        'empty_cta'    => 'Clear the filters',
        'search_here'  => 'Search the catalogue',
        'under'        => 'Under $:amount',
        'between'      => '$:from to $:to',
        'above'        => 'Over $:amount',
        'sorts' => [
            'featured'  => 'Featured',
            'new'       => 'Newest first',
            'old'       => 'Oldest first',
            'az'        => 'A to Z',
            'za'        => 'Z to A',
        ],
    ],

    'product' => [
        'add_to_bag'    => 'Add to bag',
        'save'          => 'Save',
        'saved'         => 'Saved',
        'sign_in_first' => 'Sign in to buy',
        'quantity'      => 'Quantity',
        'in_stock'      => ':count in stock',
        'low_stock'     => 'Only :count left',
        'out_of_stock'  => 'Out of stock',
        'no_price'      => 'Price on request',
        'colour'        => 'Colour',
        'description'   => 'Description',
        'specs'         => 'Specification',
        'reviews'       => 'Reviews',
        'review_count'  => ':count reviews',
        'no_reviews'    => 'No reviews yet.',
        'write_review'  => 'Write a review',
        'your_rating'   => 'Your rating',
        'your_review'   => 'Your review',
        'submit_review' => 'Submit review',
        'sign_in_review'=> 'Sign in to leave a review',
        'related'       => 'Related pieces',
        'back'          => 'Back to the catalogue',
        'category'      => 'Category',
        'sku'           => 'Reference',
    ],

    /* ── Bag, checkout ───────────────────────────────────────────────── */

    'bag' => [
        'title'      => 'Your bag',
        'empty'      => 'Your bag is empty.',
        'empty_cta'  => 'Browse the catalogue',
        'item'       => 'Piece',
        'price'      => 'Price',
        'quantity'   => 'Quantity',
        'total'      => 'Total',
        'subtotal'   => 'Subtotal',
        'shipping'   => 'Shipping',
        'free'       => 'Free',
        'grand'      => 'To pay',
        'update'     => 'Update',
        'remove'     => 'Remove',
        'checkout'   => 'Checkout',
        'continue'   => 'Keep looking',
        'summary'    => 'Summary',
        'together'   => 'These pieces together take :area m², :percent% of your floor.',
        'together_no_room' => 'These pieces together take :area m².',
    ],

    'checkout' => [
        'title'       => 'Checkout',
        'delivery'    => 'Delivery',
        'payment'     => 'Payment',
        'review'      => 'Your order',
        'country'     => 'Country',
        'city'        => 'City',
        'street'      => 'Street address',
        'phone'       => 'Phone',
        'method'      => 'How you would like to pay',
        'cod'         => 'Cash on delivery',
        'cod_note'    => 'Pay the courier when the piece arrives.',
        'card'        => 'Card',
        'card_name'   => 'Name on card',
        'card_number' => 'Card number',
        'card_expiry' => 'Expiry',
        'card_cvc'    => 'CVC',
        'place'       => 'Place order',
        'securing'    => 'Placing your order',
        'doorway'     => 'Doorway check',
        'doorway_note'=> 'One or more pieces are wider than a standard 80 cm doorway on every face. Please measure your entrance before delivery.',
    ],

    'thankyou' => [
        'title'    => 'Order placed',
        'heading'  => 'Thank you. Your order is in.',
        'body'     => 'We have emailed a copy of this to you. Your pieces are being prepared.',
        'number'   => 'Order',
        'placed'   => 'Placed',
        'total'    => 'Total',
        'status'   => 'Status',
        'items'    => 'What you ordered',
        'continue' => 'Keep looking',
    ],

    /* ── Editorial pages ─────────────────────────────────────────────── */

    'about' => [
        'title'      => 'Our story',
        'lede'       => 'We measure first.',
        'body_one'   => 'Diwan began with a returned sofa. It was the right colour, the right price, and eleven centimetres too long for the wall it was bought for.',
        'body_two'   => 'So we built the shop the other way round. Every piece is measured before it is photographed, and those numbers reach you before the picture does.',
        'alt_one'    => 'A tape measure lying across an oak plank on a workbench.',
        'principle'  => 'How we work',
        'p_one_t'    => 'Measured, then photographed',
        'p_one_b'    => 'No piece is listed until its width, depth, and height are on file. If they are missing, the listing says so instead of guessing.',
        'p_two_t'    => 'Made once, properly',
        'p_two_b'    => 'Joints fitted, edges eased, surfaces oiled. We would rather make fewer things and stand behind all of them.',
        'p_three_t'  => 'Priced without theatre',
        'p_three_b'  => 'One price, shown plainly. No crossed-out numbers, no countdowns, no invented discounts.',
        'cta'        => 'See the catalogue',
    ],

    'lookbook' => [
        'title'   => 'Rooms',
        'lede'    => 'Pieces, in place.',
        'body'    => 'Real rooms with real pieces. Tap a marker to see what it is and what it measures.',
        'view'    => 'View piece',
        'empty'   => 'No rooms published yet.',
        'scene_living'  => 'A living room, measured',
        'scene_work'  => 'A place to work',
        'scene_dining'  => 'Where people eat',
        'marker'  => 'Show :name',
    ],

    'testimonials' => [
        'title'  => 'Reviews',
        'lede'   => 'What buyers said.',
        'body'   => 'Every review here is from a verified purchase and published only after the buyer approved it.',
        'empty'  => 'No approved reviews yet. When buyers leave one, it appears here.',
        'on'     => 'on :product',
        'rating' => ':rating out of 5',
    ],

    'contact' => [
        'title'    => 'Contact',
        'lede'     => 'Ask us anything.',
        'body'     => 'Measurements, materials, delivery, or a piece you cannot find. We answer within one working day.',
        'name'     => 'Your name',
        'email'    => 'Email',
        'subject'  => 'Subject',
        'message'  => 'Message',
        'send'     => 'Send message',
        'sent'     => 'Thank you. Your message has been sent.',
        'measure'  => 'Measuring help',
        'measure_b'=> 'Not sure how to measure your room? Width is the shorter wall, length the longer one. Measure at floor level, skirting board to skirting board.',
    ],

    /* ── Auth ────────────────────────────────────────────────────────── */

    'auth' => [
        'sign_in_title'    => 'Sign in',
        'sign_in_lede'     => 'Welcome back.',
        'sign_in_body'     => 'Your bag, your saved pieces, and your room measurements are where you left them.',
        'register_title'   => 'Create account',
        'register_lede'    => 'Start a room.',
        'register_body'    => 'Save pieces into named rooms and see what they add up to before you buy.',
        'name'             => 'Name',
        'email'            => 'Email',
        'password'         => 'Password',
        'password_confirm' => 'Confirm password',
        'remember'         => 'Keep me signed in',
        'forgot'           => 'Forgot your password?',
        'submit_login'     => 'Sign in',
        'submit_register'  => 'Create account',
        'no_account'       => 'No account yet?',
        'have_account'     => 'Already have an account?',
        'side_alt'         => 'A walnut armchair beside a window, drawn to scale.',
        'side_caption'     => 'Every piece on Diwan carries its real measurements.',
    ],

    /* ── Shared ──────────────────────────────────────────────────────── */

    'error' => [
        '404_title' => 'That page is not here',
        '404_body'  => 'The link may be old, or the piece may have been retired. Search the catalogue, or start again from one of these.',
    ],

    'common' => [
        'currency'     => '$',
        'from'         => 'from',
        'loading'      => 'Loading',
        'error'        => 'Something went wrong.',
        'required'     => 'Required',
        'optional'     => 'Optional',
        'back'         => 'Back',
        'next'         => 'Next',
        'previous'     => 'Previous',
        'page'         => 'Page :page',
        'skip'         => 'Skip to content',
    ],

    'piece_count' => '{0} no pieces|{1} one piece|[2,*] :count pieces',

];
