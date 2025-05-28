<?php
$config = require 'config.php';
require_once 'lib/parsedown/Parsedown.php';
require_once 'auth.php';

$postDir = $config['post_dir'];
$files = glob("$postDir/*.md");
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $files = array_filter($files, function($file) use ($keyword) {
        $content = file_get_contents($file);
        return stripos($content, $keyword) !== false;
    });
}

usort($files, function($a, $b) { //
    return filemtime($b) - filemtime($a);
});
$initialFiles = array_slice($files, 0, 10);

$Parsedown = new Parsedown();


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $config['site_name'] ?></title>
    <meta name="referrer" content="no-referrer">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; padding: 20px; margin: 0; background: #f9f9f9; }
        h1 { text-align: center; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card h3 {
            margin: 0 0 10px;
        }
        .card small {
            color: #888;
            font-size: 12px;
        }
        .card .excerpt {
            font-size: 14px;
            color: #333;
        }
        .card a {
            margin-top: 10px;
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1><?= $config['site_name'] ?></h1>
<form method="get">
    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="搜索文章..." style="padding: 8px; width: 60%;">
    <button type="submit" style="padding: 8px;">🔍 搜索</button>
    <?php if (isset($_SESSION['user'])): ?>
        <p><a href="edit.php" style="display:inline-block; margin-top:10px; font-weight:bold;">✍️ 添加文章</a></p>
    <?php endif; ?>
</form>
<div class="grid">
<?php foreach ($initialFiles as $file): ?>
    <?php
        $content = file_get_contents($file);
        $title = '未命名';
        $date = date('Y-m-d H:i:s', filemtime($file));
        if (preg_match('/^---\s*(.*?)---\s*(.*)/s', $content, $matches)) {
            $meta = parse_ini_string($matches[1]);
            $tags = [];
            if (isset($meta['tags'])) {
                $tags = array_map('trim', explode(',', $meta['tags']));
            }
            $title = $meta['title'] ?? $title;
            $date = $meta['date'] ?? $date;
            $body = $matches[2];
        } else {
            $meta = [];
            $tags = [];
            $body = $content;
        }
        $excerpt = mb_substr(strip_tags($Parsedown->text($body)), 0, 100) . '...';
        $basename = basename($file);
        // 封面图
        $cover = $meta['cover'] ?? '';
        // 点赞数
        $likeFile = __DIR__ . "/data/likes/$basename.json";
        $likes = file_exists($likeFile) ? (json_decode(file_get_contents($likeFile), true)['likes'] ?? 0) : 0;
        $likeDisplay = $likes >= 100000 ? '10w+' : $likes;
        // 阅读量
        $viewFile = __DIR__ . "/data/views/$basename.json";
        $views = file_exists($viewFile) ? (int)file_get_contents($viewFile) : 0;
        $viewDisplay = $views >= 100000 ? '10w+' : $views;
    ?>
    <div>
      <a href="post.php?file=<?= urlencode($basename) ?>" class="card" style="text-decoration: none; color: inherit;">
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
      <?php if (is_logged_in()): ?>
        <a href="edit.php?file=<?= urlencode($basename) ?>" style="margin-top:5px; font-size: 13px; color:#555;">✏️ 编辑</a>
      <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>
<script>
  const allFiles = <?= json_encode(array_map('basename', $files)) ?>;
</script>
<div id="loadMoreWrap" style="text-align:center; margin: 30px 0;">
  <button id="loadMoreBtn" style="padding:10px 20px;">加载更多</button>
</div>
<script>
  let offset = 10;
  const grid = document.querySelector('.grid');
  document.getElementById('loadMoreBtn').onclick = async () => {
    const batch = allFiles.slice(offset, offset + 10);
    if (!batch.length) return;
    for (let fname of batch) {
      const res = await fetch("api_post_card.php?file=" + encodeURIComponent(fname));
      const html = await res.text();
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html;
      grid.appendChild(wrapper);
    }
    offset += 10;
    if (offset >= allFiles.length) {
      document.getElementById('loadMoreWrap').style.display = 'none';
    }
  };
</script>
<script>
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting && offset < allFiles.length) {
      document.getElementById('loadMoreBtn').click();
    }
  });
}, {
  rootMargin: '0px',
  threshold: 1.0
});

observer.observe(document.getElementById('loadMoreWrap'));
</script>
</body>
</html>