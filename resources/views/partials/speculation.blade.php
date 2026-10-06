{{--
  Fetch the next page while the cursor rests on its link, so the click opens
  a page that has already arrived. Chromium reads this; other browsers ignore
  it and navigate as they always have.

  "moderate" waits for a short hover or a pointer-down, so a cursor crossing
  the page does not fetch everything it passes. Never fetched ahead: links
  that change something on a GET (the language switch, e-mail and password
  links), the checkout and the bag (their state is the visitor's, now), and
  anything that opts out with data-no-prefetch.
--}}
<script type="speculationrules" nonce="{{ $cspNonce ?? '' }}">
{
    "prefetch": [{
        "where": {
            "and": [
                { "href_matches": "/*" },
                { "not": { "href_matches": "/locale/*" } },
                { "not": { "href_matches": "/logout" } },
                { "not": { "href_matches": "/verify-email*" } },
                { "not": { "href_matches": "/reset-password/*" } },
                { "not": { "href_matches": "/checkout*" } },
                { "not": { "href_matches": "/cart*" } },
                { "not": { "selector_matches": "[data-no-prefetch], [target=_blank], [download]" } }
            ]
        },
        "eagerness": "moderate"
    }]
}
</script>
