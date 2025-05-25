<?php
require 'auth.php';
require_login();
$config = require 'config.php';

$postDir = $config['post_dir'];


$keyword = trim($_GET['q'] ?? '');
$files = glob("$postDir/*.md");
if ($keyword !== '') {
    $files = array_filter($files, function($file) use ($keyword) {
        return stripos(file_get_contents($file), $keyword) !== false;
    });
}
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$total = count($files);
$totalPages = ceil($total / $perPage);
$files = array_slice($files, ($page - 1) * $perPage, $perPage);

usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a); // 最新的在前
});
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>内容管理 - <?= $config['site_name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 900px; margin: auto; }
        .post { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 5px; background: #fff; }
        .post h3 { margin: 0 0 5px; }
        .post small { color: #888; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007BFF; }
    </style>
</head>
<body>
<h2>后台管理 - <?= $config['site_name'] ?></h2>
<p><a href="logout.php">退出登录</a> | <a href="edit.php">发布新文章</a> | <a href="index.php">查看前台</a></p>
<form method="get" style="margin: 10px 0;">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="搜索标题或内容..." style="padding: 6px; width: 200px;">
    <button type="submit">🔍 搜索</button>
</form>
<p>共 <?= $total ?> 篇文章，每页 <?= $perPage ?> 篇，当前第 <?= $page ?> 页，共 <?= $totalPages ?> 页。</p>
<div style="margin-bottom: 20px;">
<?php if (empty($files)): ?>
    <p>暂无内容。</p>
<?php else: ?>
    <?php foreach ($files as $file): ?>
        <?php
            $content = file_get_contents($file);
            $title = '未命名';
            $date = date('Y-m-d H:i:s', filemtime($file));
            if (preg_match('/^---\s*(.*?)---/s', $content, $match)) {
                $meta = parse_ini_string($match[1]);
                $tags = [];
                if (isset($meta['tags'])) {
                    $tags = array_map('trim', explode(',', $meta['tags']));
                }
                $title = $meta['title'] ?? $title;
                $date = $meta['date'] ?? $date;
            }
            $author = $meta['author'] ?? '未知作者';
            $basename = basename($file);
        ?>
        <div class="post">
            <h3><?= htmlspecialchars($title) ?></h3>
            <small><?= htmlspecialchars($date) ?></small><br>
            <p><small>作者：<?= htmlspecialchars($author) ?></small></p>
            <?php if (!empty($tags)): ?>
                <p>
                <?php foreach ($tags as $tag): ?>
                    <span style="display:inline-block; background:#eee; color:#333; padding:2px 6px; border-radius:4px; margin:2px;">#<?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
                </p>
            <?php endif; ?>
            <div class="actions">
                <a href="post.php?file=<?= urlencode($basename) ?>">👁️ 查看</a>
                <a href="edit.php?file=<?= urlencode($basename) ?>">✏️ 编辑</a>
                <a href="delete.php?file=<?= urlencode($basename) ?>" onclick="return confirm('确定删除？')">🗑️ 删除</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if ($totalPages > 1): ?>
<div style="margin-top: 20px;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?= $i == $page
            ? "<strong style='margin: 0 5px;'>$i</strong>"
            : "<a href='?q=" . urlencode($keyword) . "&page=$i' style='margin: 0 5px;'>$i</a>" ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
</body>
</html>