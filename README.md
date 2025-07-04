# 收集小红书de吃瓜笔记
这个项目是一个基于 PHP 的轻量级 CMS 系统，具有类似“小红书”风格的前端展示，采用 Markdown 文件进行内容管理，前后端界面现代、简洁、响应式。以下是完整功能总结：

⸻

## ✅ 项目概况
```
•	语言/架构：纯 PHP + 本地文件系统（无需数据库）
•	主要依赖：Parsedown（Markdown 渲染库）
•	目录结构清晰：支持 Markdown 文件夹存储、点赞/评论/浏览记录以 JSON 保存
```
⸻

## 🔐 用户与权限
```
•	支持多用户登录，用户名密码配置在 config.php
•	登录后可发布、编辑、删除内容
•	非登录用户只能浏览内容，无法点赞或评论
```
⸻

## ✍️ 内容管理功能（后台）
```
•	支持 Markdown 格式编辑（基于 EasyMDE 编辑器）
•	每篇文章支持：
•	标题、作者、发布时间
•	标签（自动格式化和去重）
•	封面图（通过 URL 设定）
•	支持文章列表管理（dashboard.php）：
•	按时间排序
•	一键编辑、删除文章
```
⸻

## 🌈 前端展示功能（index.php）
```
•	类“小红书”卡片瀑布流布局（支持封面、摘要、标签）
•	支持以下功能：
•	🔍 关键词搜索
•	🔖 标签高亮显示
•	👍 点赞统计（最大显示 10w+）
•	👁️ 阅读量统计（最大显示 10w+）
•	⏬ Ajax 加载更多（滚动到底自动加载）
•	📷 封面优先展示
•	✍️ 登录用户可从首页直接跳转编辑文章
```
⸻

## 📄 文章详情页（post.php）
```
•	支持 Markdown 渲染
•	自动生成目录导航（基于 H1~H3 标题）
•	支持以下功能：
•	👍 点赞按钮（Ajax 增加）
•	👁️ 阅读统计（自增）
•	💬 评论功能（本地存储、未登录不可评论）
•	↪️ 评论可快速回复
•	🔗 上一篇 / 下一篇文章导航
•	📌 相关文章推荐（基于标签智能匹配）
•	✍️ “添加文章”按钮（登录后可见）
```
⸻

## 📦 存储结构
```
•	data/posts/*.md：Markdown 文章文件
•	data/likes/*.json：每篇文章点赞数
•	data/views/*.json：每篇文章阅读量
•	data/comments/*.json：每篇文章评论列表
```
⸻

## 💡 可扩展方向（下一步可做）
```
功能	描述
上传封面图	支持图像上传而非仅填写 URL
标签云/标签筛选	支持点击标签筛选相关文章
评论多层级	评论支持回复评论
夜间模式切换	提升用户体验
支持分页	首页支持分页跳转替代无限加载
SEO 优化	标题、描述、OpenGraph 等设置
```

## 项目截图

### 首页
<img src="https://archive.biliimg.com/bfs/archive/91a4c57fe4c7ea4066f26657f1b585f6124bb9f4.png" alt="登陆" referrerpolicy="no-referrer">

### 文章详情页
 <img src="https://archive.biliimg.com/bfs/archive/d1f329035e50421b17fbecdb544f4f7e288306d7.png" alt="登录页" referrerpolicy="no-referrer">


### 登陆页
 <img src="https://archive.biliimg.com/bfs/archive/49471995b214154bf55aa5061bcb46e6468396b1.png" alt="登录页" referrerpolicy="no-referrer">

### 后台
<img src="https://archive.biliimg.com/bfs/archive/0e2535147dc0402a7a56ee2719349295334bf08d.png" alt="后台" referrerpolicy="no-referrer">


### 编辑文章页
<img src="https://archive.biliimg.com/bfs/archive/a18d6d428ce59691e38237a4fa2f6601643a7fba.png" alt="编辑文章页" referrerpolicy="no-referrer">


### 一键分享
<img src="https://archive.biliimg.com/bfs/archive/bdfedd05030512fb0ce57325aab89b307c8da216.png" alt="一键分享" referrerpolicy="no-referrer">


### 图片预览
<img src="https://archive.biliimg.com/bfs/archive/2bc91e756bc9a22020d54fe3f40fad4d0c90601c.png" alt="图片预览" referrerpolicy="no-referrer">








 