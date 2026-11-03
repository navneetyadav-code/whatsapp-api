<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$urls = [
    [
        'loc' => public_url(''),
        'priority' => '1.0',
        'changefreq' => 'daily',
    ],
    [
        'loc' => public_url('docs'),
        'priority' => '0.9',
        'changefreq' => 'weekly',
    ],
    [
        'loc' => public_url('login'),
        'priority' => '0.8',
        'changefreq' => 'monthly',
    ],
    [
        'loc' => public_url('register'),
        'priority' => '0.7',
        'changefreq' => 'monthly',
    ],
    [
        'loc' => public_url('privacy'),
        'priority' => '0.5',
        'changefreq' => 'yearly',
    ],
    [
        'loc' => public_url('terms'),
        'priority' => '0.5',
        'changefreq' => 'yearly',
    ],
];

header('Content-Type: application/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= e($url['loc']) ?></loc>
        <changefreq><?= e($url['changefreq']) ?></changefreq>
        <priority><?= e($url['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
