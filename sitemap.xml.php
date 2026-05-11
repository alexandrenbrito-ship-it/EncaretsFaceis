<?php
header('Content-Type: application/xml');

if (!file_exists(__DIR__ . '/config/config.php')) {
    echo '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

echo '<url>';
echo '<loc>' . APP_URL . '/</loc>';
echo '<changefreq>daily</changefreq>';
echo '<priority>1.0</priority>';
echo '</url>';

echo '<url>';
echo '<loc>' . APP_URL . '/landing-page/</loc>';
echo '<changefreq>weekly</changefreq>';
echo '<priority>0.9</priority>';
echo '</url>';

echo '<url>';
echo '<loc>' . APP_URL . '/lojista/login.php</loc>';
echo '<changefreq>monthly</changefreq>';
echo '<priority>0.5</priority>';
echo '</url>';

if (defined('DB_PREFIX')) {
    try {
        $db = Database::getConnection();
        
        $stmt = $db->query("SELECT subdominio FROM enc_lojistas WHERE status_assinatura IN ('ativa', 'trial')");
        $lojistas = $stmt->fetchAll();
        
        foreach ($lojistas as $lojista) {
            echo '<url>';
            echo '<loc>' . APP_URL . '/public/?s=' . $lojista['subdominio'] . '</loc>';
            echo '<changefreq>daily</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }

        $stmt = $db->query("SELECT e.slug, l.subdominio FROM enc_encartes e JOIN enc_lojistas l ON e.lojista_id = l.id WHERE e.publicado = 1");
        $encartes = $stmt->fetchAll();
        
        foreach ($encartes as $encarte) {
            echo '<url>';
            echo '<loc>' . APP_URL . '/public/encarte.php?lojista=' . $encarte['subdominio'] . '&amp;slug=' . $encarte['slug'] . '</loc>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.7</priority>';
            echo '</url>';
        }

    } catch (Exception $e) {
    }
}

echo '</urlset>';