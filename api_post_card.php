<?php
$config = require 'config.php';
require_once 'lib/parsedown/Parsedown.php';
$file = basename($_GET['file'] ?? '');
$path = $config['post_dir'] . '/' . $file;

if (!preg_match('/\.md$/', $file) || !file_exists($path)) {
    http_response_code(404);
    exit('Not found');
}

$Parsedown = new Parsedown();
$content = file_get_contents($path);
$title = '未命名';
$date = date('Y-m-d H:i:s', filemtime($path));
$tags = [];

if (preg_match('/^---\s*(.*?)---\s*(.*)/s', $content, $matches)) {
    $meta = parse_ini_string($matches[1]);
    $title = $meta['title'] ?? $title;
    $date = $meta['date'] ?? $date;
    $body = $matches[2];
    if (isset($meta['tags'])) {
        $tags = array_map('trim', explode(',', $meta['tags']));
    }
    $cover = $meta['cover'] ?? '';
} else {
    $body = $content;
    $cover = '';
}

$excerpt = mb_substr(strip_tags($Parsedown->text($body)), 0, 100) . '...';

// 统计
$likeFile = __DIR__ . "/data/likes/$file.json";
$likes = file_exists($likeFile) ? (json_decode(file_get_contents($likeFile), true)['likes'] ?? 0) : 0;
$likeDisplay = $likes >= 100000 ? '10w+' : $likes;

$viewFile = __DIR__ . "/data/views/$file.json";
$views = file_exists($viewFile) ? (int)file_get_contents($viewFile) : 0;
$viewDisplay = $views >= 100000 ? '10w+' : $views;

?>
<div>
  <a href="post.php?file=<?= urlencode($file) ?>" class="card" style="text-decoration: none; color: inherit;">
    <?php if ($cover): ?>
        <img src="<?= htmlspecialchars($cover) ?>" alt="cover" style="width:100%; border-radius: 6px; margin-bottom: 10px;">
    <?php endif; ?>
    <h3><?= htmlspecialchars($title) ?></h3>
    <small><?= htmlspecialchars($date) ?></small>
    <div class="excerpt"><?= $excerpt ?></div>
    <?php if (!empty($tags)): ?>
        <div class="tags">
            <?php foreach ($tags as $tag): ?>
                <span style="font-size:12px; color:#555; background:#eee; padding:2px 6px; margin:2px; border-radius:4px;">
                    #<?= htmlspecialchars($tag) ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div style="margin-top: 8px; font-size: 13px; color: #666;">
        👍 <?= $likeDisplay ?> &nbsp; 👁️ <?= $viewDisplay ?>
    </div>
  </a>
</div>