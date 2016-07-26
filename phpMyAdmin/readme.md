### 用户中心-OAuth2.0

####phpMyAdmin接入指南
* 下载`ucenter_login.php`文件，保存于项目源码的根目录；
* 修改源码根目录下的`index.php`文件，增加`require_once('ucenter_login.php')`，不同版本的位置可能不一样；结果如下：

        // UCenter登陆
        require_once('ucenter_login.php');

