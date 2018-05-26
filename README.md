# think-login
The ThinkPHP5 login

## 安装

### 一、执行命令安装
```
composer require dh2y/think-login
```

或者

### 二、require安装
```
"require": {
        "dh2y/think-login":"*"
},
```

或者
###  三、autoload psr-4标准安装
```
   a) 进入vendor/dh2y目录 (没有dh2y目录 mkdir dh2y)
   b) git clone 
   c) 修改 git clone下来的项目名称为think-login
   d) 添加下面配置
   "autoload": {
        "psr-4": {
            "dh2y\\login\\": "vendor/dh2y/think-login/src"
        }
    },
    e) php composer.phar update
```


## 多用户配置
在配置目录里面新建login_去除前缀表名
如admin登录   login_admin

```
return [
    'crypt' => 'dh2y',      //Crypt加密秘钥
    'auth_uid' => 'adminXx',      //用户认证识别号(必配)
    'not_auth_module' => 'index', // 无需认证模块
    'user_auth_gateway' => 'index/login', // 默认网关
];
```

## 使用
记住用户名和密码
```
$member = new login('admin');    //admin 表示表名称-默认带前缀
return $member->remember();
或
$member = new login();
return $member->remember();
```

登录操作
```
$login = new login('admin');
$data = request()->post();
return $login->doLogin($data);
```

