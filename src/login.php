<?php
/**
 * Created by dh2y.
 * Blog: http://blog.csdn.net/sinat_22878395
 * Date: 2018/4/26 0026 16:26
 * For: 登录模块
 */

namespace dh2y\login;


use think\Config;
use think\crypt\Crypt;
use think\Db;
use think\Validate;

class login
{
    protected $config = [
        'crypt' => 'dh2y',      //Crypt加密秘钥
        'auth_uid' => 'authId',      //用户认证识别号(必配)
        'not_auth_module' => 'index', // 无需认证模块
        'user_auth_gateway' => 'index/login', // 默认网关
    ];

    protected $model;          //登录模型
    protected $member;         //后台用户
    protected $error;

    /**
     * 加载配置
     * login constructor.
     * @param $model
     */
    public function __construct($model = 'admin'){
        if ($config = Config::get('login')) {
            $this->config = array_merge($this->config,$config);
        }

        $this->model = $model;
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function __get($name)
    {
        return $this->config[$name];
    }

    /**
     * 记住登录账户密码
     */
    public function remember(){
        if(!cookie('remember')){
            return false;
        }
        $remember = Crypt::decrypt(cookie('remember'),$this->config['crypt']);
        return unserialize($remember);
    }

    /**
     * 登录操作
     * @param $data
     * @return array
     */
    public function doLogin($data){
        $result = $this->checkMember($data);
        if ($result['status']==false){
            return $result;
        }
        $result = $this->checkPass($data['password']);
        if($result['status']==true){

            session($this->config['auth_uid'], $this->member['id']);
            session("username", $this->member['username']);

            //登录日志更新
            $this->member['last_login'] = time();
            $this->member['login_ip'] = LoginHelper::get_client_ip(0,true);
            Db::name($this->model)->where('id',$this->member['id'])->update($this->member);

            //如果记住账号密码-vue.js复选框传的是true和false字符串
            if($data['remember']=='true'){
                $member['username'] = $data['username'];
                $member['password'] = $data['password'];
                $member['remember'] = $data['remember'];
                $remember = Crypt::encrypt(serialize($member),$this->config['crypt']);
                cookie('remember', $remember);//记住我
            }else{
                cookie('remember', null);
            }

        }
        return $result;
    }

    /**退出
     * @return array
     */
    public function logout(){
        session($this->config['auth_uid'], null);
        session("username", null);
        session(null);
        return ['status'=>true,'message'=>'成功退出！'];
    }

    /**
     * 登录验证
     * @param $data
     * @return bool
     */
    public function validate($data){
        $rule = [
            ['username','require','用户名必须！'], //默认情况下用正则进行验证
            ['password','require|length:6,16','密码不能为空！|请输入6~16位有效字符'],
            ['verify','require|captcha:login','验证码不能为空！|验证码错误！'],
        ];
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if($result){
            return true;
        }else{
            $this->setError($validate->getError());
            return false;
        }

    }

    /**
     * 检验用户
     * @param $data
     * @return array
     */
    public function checkMember($data){
        $validate = $this->validate($data);
        if(!$validate){
            return ['status'=>false,'message'=>$this->getError()];
        }

        $map['username'] = $data['username'];
        $map['status'] = 1;
        $this->member = Db::name($this->model)->where($map)->find();
        if ( $this->member){
            return ['status' => true, 'data' =>  $this->member];
        }
        return ['status'=>false,'message'=>'用户不存在或被禁用'];
    }

    /**
     * 检查密码是否正确
     * @param $password
     * @return array
     */
    public function checkPass($password){
        if( md5($password.$this->member['token'])!= $this->member['password']){
            return ['status'=>false,'message'=>'密码错误'];
        }
        return ['status'=>true,'message'=>'恭喜！密码正确'];
    }


    /**设置错误信息
     * @param $message
     */
    public function setError($message){
        $this->error = $message;
    }

    /**获取错误信息
     * @return mixed
     */
    public function getError(){
        return $this->error;
    }
}