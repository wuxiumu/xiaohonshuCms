<?php
require 'auth.php';
require_login();
$config = require 'config.php';

$postDir = $config['post_dir'];
$filename = basename($_GET['file'] ?? '');
$filepath = $filename ? "$postDir/$filename" : '';
// 初始化变量
$content = '';
$title = '';
$tags = '';
$author = '';
$date = '';
$cover = '';

if ($filename && file_exists($filepath)) {
    $raw = file_get_contents($filepath);
    if (preg_match('/^---\s*(.*?)---\s*(.*)/s', $raw, $matches)) {
        $meta = parse_ini_string($matches[1]);
        $title = $meta['title'] ?? '';
        $author = $meta['author'] ?? '';
        $date = $meta['date'] ?? '';
        $tags = $meta['tags'] ?? '';
        $cover = $meta['cover'] ?? '';
        $content = $matches[2];
    } else {
        $content = $raw;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? $_SESSION['user']);
    $date = trim($_POST['date'] ?? date('Y-m-d H:i:s'));
    $tags = $_POST['tags'] ?? '';
    $tags = implode(',', array_unique(array_filter(array_map('trim', preg_split('/[,\s]+/', $tags)))));
    $cover = trim($_POST['cover'] ?? '');

    if ($title && $body) {
        $meta = "---\ntitle = \"$title\"\ndate = \"$date\"\nauthor = \"$author\"\ntags = \"$tags\"\ncover = \"$cover\"\n---\n\n";
        $final = $meta . $body;
        $name = $filename ?: date('YmdHis') . '-' . uniqid() . '.md';
        file_put_contents("$postDir/$name", $final);
        header("Location: dashboard.php");
        exit;
    }
}
$datetimeAttr = $date ? date('Y-m-d\TH:i', strtotime($date)) : date('Y-m-d\TH:i'); // 日期时间属性
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>发布文章</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/easymde.min.css">
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: auto; }
        input[type="text"] { width: 90%; padding: 10px; font-size: 16px; margin-bottom: 10px; }
        button { padding: 10px 20px; font-size: 16px; }
        textarea { height: 400px;width: 100%; }
        .btn-primary{
            background-color: #4CAF50;    /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<h2><?= $filename ? '编辑文章' : '发布新文章' ?></h2>
<?php if (isset($_SESSION['user'])): ?>
     <p style="margin-top: 10px;">欢迎，<a href="profile.php?user=<?= $_SESSION['user'] ?>"><?= $_SESSION['user'] ?></a>！<a href="logout.php">登出</a></p>
    <p><a href="dashboard.php">管理后台</a> ｜ <a href="post.php?file=<?= urlencode($filename) ?>">👁️ 查看</a> ｜ <a href="edit.php?file=<?= urlencode($filename) ?>" class="back">✏️ 编辑</a> ｜ <a href="edit.php" class="back">✍️ 添加文章</a> ｜ <a class="back" href="index.php">← 返回首页</a></p>
<?php endif; ?>
<a class="btn btn-primary" target="_blank" href="https://service-8zqb5ngm-1253419200.gz.apigw.tencentcs.com/bilibili/upload.html?key=RJM3KAADRKFfDzqm6S">图片上传</a>
<form method="post">
    <input type="text" name="title" placeholder="请输入标题" value="<?= htmlspecialchars($title) ?>" required>
    <input type="text" name="cover" placeholder="封面图 URL" value="<?= htmlspecialchars($cover) ?>">
    <?php if ($cover): ?>
        <div style="margin-bottom:10px;"><img src="<?= htmlspecialchars($cover) ?>" alt="封面图" style="max-width:100%;"></div>
    <?php endif; ?>
    <input type="text" name="author" placeholder="作者" value="<?= htmlspecialchars($author) ?>" required>
    <input type="datetime-local" name="date" value="<?= $datetimeAttr ?>" required>
    <input type="text" name="tags" placeholder="标签（用逗号分隔）" value="<?= htmlspecialchars($tags) ?>">
    <textarea name="content" id="editor"><?= htmlspecialchars($content) ?></textarea>
    <br>
    <button type="submit">保存</button>
</form>

<script src="assets/easymde.min.js"></script>
<script>
    new EasyMDE({ element: document.getElementById("editor") });
    const tagInput = document.querySelector('input[name="tags"]');
    tagInput?.addEventListener('input', () => {
      tagInput.value = tagInput.value.replace(/\s+/g, ',').replace(/,+/g, ',');
    });
</script>
</body>
</html>