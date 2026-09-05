{{--
  Hallmark · genre: modern-minimal · theme: custom (Diwan) · design-system: design.md

  A standalone error page for the failures where the app itself may be the
  problem — 500 and 503.

  It deliberately does NOT extend layouts.furnish. That layout queries
  categories for the mega-menu, counts the bag, and reads the session for the
  room. If the database or the session store is what broke, rendering it throws
  a second time and the visitor gets Laravel's raw whoops screen instead of
  ours. An error page must not depend on the thing that might be down.

  So: no Vite, no database, no session, no translation files. The colours and
  type are inlined from tokens.css and are the only duplication in the project —
  deliberate, and worth it for a page that has to render when nothing else can.

  @param  string  $code
  @param  string  $heading
  @param  string  $body
--}}
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }} — DIWAN</title>
    <meta name="robots" content="noindex">

    <style>
        /* Inlined from tokens.css. See the note above: this page cannot rely on
           the asset pipeline having survived whatever caused the error. */
        :root {
            --paper:  oklch(97.5% 0.006 75);
            --rule:   oklch(88.0% 0.008 72);
            --muted:  oklch(46.0% 0.012 68);
            --ink:    oklch(19.0% 0.018 62);
            --accent: oklch(46.0% 0.150 255);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            background: var(--paper);
            color: var(--ink);
            font-family: "Geist", ui-sans-serif, system-ui, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        main { width: 100%; max-width: 34rem; }

        .code {
            font-family: ui-monospace, "SFMono-Regular", monospace;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            margin: 0 0 0.5rem;
        }

        h1 {
            font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.011em;
            line-height: 1.28;
            margin: 0 0 0.75rem;
        }

        p { color: var(--muted); margin: 0 0 2rem; max-width: 46ch; }

        hr { border: 0; border-top: 1px solid var(--rule); margin: 0 0 1.5rem; }

        a {
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid currentColor;
            padding-bottom: 1px;
        }

        a:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
    </style>
</head>

<body>
    <main>
        <p class="code">{{ $code }}</p>
        <h1>{{ $heading }}</h1>
        <p>{{ $body }}</p>
        <hr>
        <a href="/">DIWAN</a>
    </main>
</body>

</html>
