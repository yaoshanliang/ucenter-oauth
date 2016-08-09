### 用户中心-OAuth2.0

####dokuwiki 接入指南
* 下载`dokuwiki-ucenter-login.php`文件，保存于dokuwiki源码的根目录；
* 修改源码根目录下的`doku.php`文件，在引入初始化文件的下一行， 加上
`require_once(DOKU_INC . 'doku-ucenter-login.php');`，结果如下：

```php
……
// load and initialize the core system
require_once(DOKU_INC . 'inc/init.php');
require_once(DOKU_INC . 'doku-ucenter-login.php');
……
```