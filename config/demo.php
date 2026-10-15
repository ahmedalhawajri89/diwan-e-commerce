<?php

return [

    /*
    | The public demo's accounts, from the environment of the deployment that
    | wants them; empty everywhere else, so no other copy of the shop has them.
    |
    | The viewer sees the whole panel and changes nothing (PanelReadOnly), so
    | its password can be printed on the sign-in page for anyone to try. The
    | owner is a real administrator: its password is never printed anywhere.
    */
    'viewer_email' => env('DEMO_VIEWER_EMAIL'),
    'viewer_password' => env('DEMO_VIEWER_PASSWORD'),

    'owner_name' => env('OWNER_NAME', 'Shop owner'),
    'owner_email' => env('OWNER_EMAIL'),
    'owner_password' => env('OWNER_PASSWORD'),

];
