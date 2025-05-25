<?php
session_start();
$config = require 'config.php';
require_once 'lib/parsedown/Parsedown.php';

$file = basename($_GET['file'] ?? '');
$path = $config['post_dir'] . '/' . $file;

if (!preg_match('/\.md$/', $file) || !file_exists($path)) {
    die("文章不存在");
}

$allFiles = glob($config['post_dir'] . '/*.md');
usort($allFiles, fn($a, $b) => filemtime($b) - filemtime($a));
$currentIndex = array_search($path, $allFiles);
$prevFile = $allFiles[$currentIndex - 1] ?? null;
$nextFile = $allFiles[$currentIndex + 1] ?? null;

$viewFile = __DIR__ . "/data/views/$file.json";
@mkdir(dirname($viewFile), 0777, true);
$views = file_exists($viewFile) ? (int)file_get_contents($viewFile) : 0;
$views++;
file_put_contents($viewFile, $views);
$viewsDisplay = $views >= 100000 ? '10w+' : $views;

$content = file_get_contents($path);
$title = '未命名';
$date = date('Y-m-d H:i:s', filemtime($path));
$Parsedown = new Parsedown();

if (preg_match('/^---\s*(.*?)---\s*(.*)/s', $content, $matches)) {
    $meta = parse_ini_string($matches[1]);
    $tags = [];
    if (isset($meta['tags'])) {
        $tags = array_map('trim', explode(',', $meta['tags']));
    }
    $title = $meta['title'] ?? $title;
    $date = $meta['date'] ?? $date;
    $body = $Parsedown->text($matches[2]);
} else {
    $body = $Parsedown->text($content);
}

$commentFile = __DIR__ . "/data/comments/$file.json";
@mkdir(dirname($commentFile), 0777, true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index']) && isset($_SESSION['user'])) {
    $comments = file_exists($commentFile) ? json_decode(file_get_contents($commentFile), true) : [];
    $i = (int)$_POST['delete_index'];
    if (isset($comments[$i])) {
        array_splice($comments, $i, 1);
        file_put_contents($commentFile, json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment']) && isset($_SESSION['user'])) {
    $comment = trim(strip_tags($_POST['comment']));
    $comments = file_exists($commentFile) ? json_decode(file_get_contents($commentFile), true) : [];
    $comments[] = ['text' => $comment, 'time' => date('Y-m-d H:i:s')];
    file_put_contents($commentFile, json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="referrer" content="no-referrer">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: auto; }
        h1 { font-size: 28px; }
        .meta { color: #888; font-size: 14px; margin-bottom: 20px; }
        .content img { max-width: 100%; border-radius: 8px; margin: 10px 0; }
        .content { line-height: 1.6; font-size: 26px; }
        a.back { display: inline-block; margin-top: 30px; color: #007BFF; }
    </style>
</head>
<body>
<?php if (isset($_SESSION['user'])): ?>
     <p style="margin-top: 10px;">欢迎，<a href="profile.php?user=<?= $_SESSION['user'] ?>"><?= $_SESSION['user'] ?></a>！<a href="logout.php">登出</a></p>
    <p><a href="dashboard.php">管理后台</a> ｜ <a href="edit.php?file=<?= urlencode($file) ?>" class="back">✏️ 编辑</a> ｜ <a href="edit.php" class="back">✍️ 添加文章</a> ｜ <a class="back" href="index.php">← 返回首页</a></p>
<?php endif; ?>
<h1><?= htmlspecialchars($title) ?></h1>
<div class="toc" style="margin: 20px 0; padding: 10px; background: #f1f1f1;">
  <strong>📑 目录</strong>
  <ul id="tocList" style="margin-top:10px;"></ul>
</div>
<div class="meta"><?= htmlspecialchars($date) ?></div>
<div class="content"><?= $body ?></div>
<?php
echo '<h3>📌 相关文章</h3><ul>';
$relatedCount = 0;
foreach ($allFiles as $recommend) {
    if ($recommend === $path) continue;
    $recon = file_get_contents($recommend);
    if (!preg_match('/^---\s*(.*?)---/s', $recon, $m)) continue;
    $meta = parse_ini_string($m[1]);
    $reTags = isset($meta['tags']) ? array_map('trim', explode(',', $meta['tags'])) : [];
    if (count(array_intersect($tags, $reTags)) > 0) {
        $reTitle = $meta['title'] ?? basename($recommend);
        echo '<li><a href="post.php?file=' . urlencode(basename($recommend)) . '">' . htmlspecialchars($reTitle) . '</a></li>';
        $relatedCount++;
        if ($relatedCount >= 5) break;
    }
}
echo '</ul>';
?>
<?php if (!empty($tags)): ?>
    <div class="tags">
        <?php foreach ($tags as $tag): ?>
            <?php $color = '#' . substr(md5($tag), 0, 6); ?>
            <span style="font-size:28px; color:white; background:<?= $color ?>; padding:2px 6px; margin:2px; border-radius:4px;">
                #<?= htmlspecialchars($tag) ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<div style="margin-top: 10px; margin-bottom: 10px;">
  👁️ 浏览 <?= $viewsDisplay ?>
  <button onclick="sharePage()" style="margin-top: 20px;">🔗 分享</button>
</div>
<div id="qr" style="margin-top: 10px;"></div>
<form id="likeForm" style="margin-bottom: 20px;">
    <button type="submit">👍 点赞 (<span id="likeCount">0</span>)</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1/build/qrcode.min.js"></script>
<script>
  function sharePage() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
      const div = document.createElement('div');
      div.style.position = 'fixed';
      div.style.width ="80%";
      div.style.top = '50%';
      div.style.left = '50%';
      div.style.transform = 'translate(-50%, -50%)';
      div.style.backgroundColor = 'white';
      div.style.padding = '10px';
      div.style.borderRadius = '8px';
      div.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
      div.innerText = '链接已复制到剪贴板！';
      document.body.appendChild(div);
      setTimeout(() => {
//         document.body.removeChild(div);
      }, 3000);
    });
    QRCode.toCanvas(document.createElement('canvas'), url, function (err, canvas) {
      if (!err) {
        const qrDiv = document.getElementById('qr');
        qrDiv.innerHTML = '';
        qrDiv.appendChild(canvas);
      }
    });
  }
</script>
<?php
$comments = file_exists($commentFile) ? json_decode(file_get_contents($commentFile), true) : [];
foreach ($comments as $i => $c): ?>
    <div style="margin-bottom: 10px; padding: 10px; background: #f0f0f0; border-left: 4px solid #ccc; border-radius: 6px;">
        <div style="font-size: 15px;"><?= htmlspecialchars($c['text']) ?></div>
        <div style="font-size: 12px; color: #888; margin-top: 5px;"><?= $c['time'] ?></div>
        <div style="font-size: 13px; color: #007BFF; margin-top: 5px; cursor: pointer;" onclick="replyTo('<?= addslashes($c['text']) ?>')">↪️ 回复</div>
        <?php if (isset($_SESSION['user'])): ?>
            <form method="post" style="display:inline;" onsubmit="return confirm('确认删除这条评论？');">
                <input type="hidden" name="delete_index" value="<?= $i ?>">
                <button type="submit" style="margin-left: 10px; color: red;">🗑 删除</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<h3>💬 评论</h3>
<?php if (!isset($_SESSION['user'])): ?>
    <p style="color: red;">请先 <a href="login.php">登录</a> 后发表评论。</p>
<?php else: ?>
    <form method="post" style="margin-bottom: 30px;">
        <textarea name="comment" placeholder="说点什么..." required style="width:100%;height:80px;"></textarea>
        <button type="submit">提交评论</button>
    </form>
<?php endif; ?>
<script>
// ajax请求获取点赞数
    fetch('like.php?view=1&file=<?= urlencode($file) ?>')
        .then(r => r.json())
        .then(data => {
        console.log(data);
            document.getElementById('likeCount').textContent = data;
         });
document.getElementById('likeForm').onsubmit = function(e) {
    e.preventDefault();
    fetch('like.php?file=<?= urlencode($file) ?>')
        .then(r => r.json())
        .then(data => {
            document.getElementById('likeCount').textContent = data.likes;
        });
};
const content = document.querySelector('.content');
const toc = document.getElementById('tocList');
if (content && toc) {
  const headers = content.querySelectorAll('h1, h2, h3');
  headers.forEach((h, idx) => {
    const id = 'h' + idx;
    h.id = id;
    const li = document.createElement('li');
    li.style.marginBottom = '5px';
    li.innerHTML = `<a href="#${id}">${h.innerText}</a>`;
    toc.appendChild(li);
  });
}
function replyTo(text) {
  const area = document.querySelector('textarea[name="comment"]');
  area.value = `@${text}\n` + area.value;
  area.focus();
}
</script>
<a class="back" href="index.php">← 返回首页</a>
<div style="margin-top: 40px;">
    <?php if ($prevFile): ?>
        <a href="post.php?file=<?= urlencode(basename($prevFile)) ?>">← 上一篇</a>
    <?php endif; ?>
    <?php if ($nextFile): ?>
        <a href="post.php?file=<?= urlencode(basename($nextFile)) ?>" style="float: right;">下一篇 →</a>
    <?php endif; ?>
</div>
</body>
</html>