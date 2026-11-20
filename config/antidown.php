<?php

/*
 * Anti-downloader / anti-clone configuration.
 *
 * guard_enabled: when true, the public layout ships a small script that checks
 * window.location.hostname against allowed_hosts. On any other host the page
 * wipes itself and shows an "unauthorized copy" notice. Enable in production.
 *
 * allowed_hosts: comma separated list of hosts (or domain suffixes) that are
 * allowed to serve the site. Subdomains of an allowed entry are allowed too,
 * so 'houseroof.eu' covers 'www.houseroof.eu' and 'blog.houseroof.eu'.
 */

return [
    'guard_enabled' => (bool) env('ANTI_CLONE_GUARD', true),

    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'ANTI_CLONE_ALLOWED_HOSTS',
        'localhost,127.0.0.1,houseroof.eu,houseroof.com'
    ))))),

    // Path of the trap served to known downloader/copier user agents
    // (and linked from the hidden honeypot). Never linked from the UI.
    'trap_path' => '/site-archive',
];
