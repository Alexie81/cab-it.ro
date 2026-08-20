<?php
declare(strict_types=1);

require_once __DIR__ . '/import-premium-seo-batch.php';

function cabit_humanize_premium_content(bool $dryRun = false): array
{
    $pdo = cms_db();
    $rows = $pdo->query('SELECT * FROM articles ORDER BY id')->fetchAll();
    $changedRows = [];
    $paragraphsChanged = 0;
    $faqAnswersChanged = 0;
    $updatedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);

    foreach ($rows as $row) {
        $metadata = cms_article_metadata($row);
        if (($metadata['batch'] ?? '') !== 'seo-premium-2026-500') {
            continue;
        }
        $article = [
            'slug' => (string) $row['slug'],
            'title' => (string) $row['title'],
            'h1' => (string) $row['title'],
            'primary_keyword' => (string) ($metadata['primary_keyword'] ?? ''),
            'cluster' => (string) ($metadata['cluster'] ?? ''),
            'pillar' => (string) ($metadata['pillar'] ?? ''),
        ];
        $changedInArticle = 0;
        $content = preg_replace_callback('~<p\b([^>]*)>(.*?)</p>~is', static function (array $matches) use ($article, &$changedInArticle): string {
            $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
            $blockSeed = abs((int) crc32($article['slug'] . '|' . $plain));
            $rewritten = cabit_premium_humanized_block($article, $plain, $blockSeed);
            if ($rewritten === $plain) {
                return $matches[0];
            }
            $changedInArticle++;
            return '<p' . $matches[1] . '>' . htmlspecialchars($rewritten, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }, (string) $row['content']) ?? (string) $row['content'];

        $metadataChanged = 0;
        if (is_array($metadata['faqs'] ?? null)) {
            foreach ($metadata['faqs'] as $faqIndex => $faq) {
                $answer = trim((string) ($faq['a'] ?? $faq['answer'] ?? ''));
                $blockSeed = abs((int) crc32($article['slug'] . '|' . $answer));
                $rewritten = cabit_premium_humanized_block($article, $answer, $blockSeed);
                if ($rewritten !== $answer) {
                    if (array_key_exists('a', $faq)) {
                        $metadata['faqs'][$faqIndex]['a'] = $rewritten;
                    } else {
                        $metadata['faqs'][$faqIndex]['answer'] = $rewritten;
                    }
                    $metadataChanged++;
                }
            }
        }

        if ($changedInArticle > 0 || $metadataChanged > 0) {
            $row['content'] = $content;
            $row['updated_at'] = $updatedAt;
            $row['seo_metadata'] = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $changedRows[] = $row;
            $paragraphsChanged += $changedInArticle;
            $faqAnswersChanged += $metadataChanged;
        }
    }

    if (!$dryRun && $changedRows !== []) {
        $statement = $pdo->prepare('UPDATE articles SET content = ?, seo_metadata = ?, updated_at = ? WHERE id = ?');
        $pdo->beginTransaction();
        try {
            foreach ($changedRows as $row) {
                $statement->execute([$row['content'], $row['seo_metadata'], $row['updated_at'], $row['id']]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        foreach ($changedRows as $row) {
            cms_generate_article($row);
        }
        cms_refresh_indexes($pdo);
    }

    return [
        'dry_run' => $dryRun,
        'articles_changed' => count($changedRows),
        'paragraphs_changed' => $paragraphsChanged,
        'faq_answers_changed' => $faqAnswersChanged,
    ];
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $dryRun = in_array('--dry-run', $argv, true);
    echo json_encode(cabit_humanize_premium_content($dryRun), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
}
