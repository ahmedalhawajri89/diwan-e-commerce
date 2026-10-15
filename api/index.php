<?php

/*
 * Vercel's entry point. Vercel runs PHP as a function from api/, so every
 * request that is not a static file (vercel.json routes) comes here and is
 * handed to Laravel's own front controller, unchanged.
 */
require __DIR__.'/../public/index.php';
