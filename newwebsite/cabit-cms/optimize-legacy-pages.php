<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$oldAnalytics = '<link rel="preconnect" href="https://www.googletagmanager.com"><script async src="https://www.googletagmanager.com/gtag/js?id=AW-18343038330"></script><script>window.__cabitGoogleTagLoaded=true;window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config","G-QPKXFL2GW9");gtag("config","AW-18343038330");gtag("config","AW-11509007584");gtag("config","AW-11509007584/6OY0CIeMyfsZEOCJ9u8q",{phone_conversion_number:"+40 771 532 949"});window.gtag_report_conversion=function(url){var done=false;var go=function(){if(!done&&url){done=true;window.location.href=url}};gtag("event","conversion",{send_to:"AW-11509007584/GqVQCOudyvsZEOCJ9u8q",event_callback:go});setTimeout(go,900);return false};</script>';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(CABIT_PUBLIC_ROOT, FilesystemIterator::SKIP_DOTS));
$updated = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'html') {
        continue;
    }
    $path = $file->getPathname();
    $html = (string) file_get_contents($path);
    $optimized = str_replace($oldAnalytics, cms_google_tag_head(), $html);
    $optimized = (string) preg_replace(
        '~\s*<link rel="preconnect" href="https://www\.googletagmanager\.com"><script async src="https://www\.googletagmanager\.com/gtag/js\?id=AW-18343038330"></script>\s*<script>\s*window\.__cabitGoogleTagLoaded=true;\s*window\.dataLayer=window\.dataLayer\|\|\[\];\s*function gtag\(\)\{dataLayer\.push\(arguments\)\}\s*gtag\("js",new Date\(\)\);\s*gtag\("config","G-QPKXFL2GW9"\);\s*gtag\("config","AW-18343038330"\);\s*gtag\("config","AW-11509007584"\);\s*gtag\("config","AW-11509007584/6OY0CIeMyfsZEOCJ9u8q",\{phone_conversion_number:"\+40 771 532 949"\}\);\s*</script>~s',
        cms_google_tag_head(),
        $optimized
    );
    $optimized = str_replace('cabit-next.css?v=20260819-3', 'cabit-next.min.css?v=20260819-4', $optimized);
    $optimized = str_replace('cabit-next.js?v=20260819-4', 'cabit-next.min.js?v=20260819-4', $optimized);
    $optimized = str_replace('20260819-3', '20260819-4', $optimized);
    $optimized = str_replace('og:site_name" content="Cab-IT Expert', 'og:site_name" content="CAB-IT Expert', $optimized);

    if ($optimized !== $html) {
        cms_write_file($path, $optimized);
        $updated++;
    }
}

echo "Optimized {$updated} legacy HTML pages.\n";
