<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2018/6/17
 * Time: 下午8:48
 */

namespace app\admin\controller;


use think\Exception;

class Login extends Base
{
    protected $noAuthAction = ['index', 'logout'];
    public function index()
    {
        if($this->request->isPost())
        {
            try{
                $param = input('param.');
                $result = $this->validate(
                    $param,
                    [
                        'username'  => 'require',
                        'password'   => 'require',
                    ],
                    [
                        'username.require' => '请输入用户名',
                        'password.require'     => '请输入密码',
                    ]
                );
                if ($result !== true ) {
                    throw new Exception($result);
                }

                $keepLogin = input('param.keepLogin', 0);
                app('adminAuth')->login($param['username'], $param['password'], $keepLogin);
                return redirect('index/index');
            }catch (Exception $e)
            {
                $this->error($e->getMessage(), 'Login/index');
            }
        }
        return $this->fetch();
    }

    public function logout()
    {
        try{
            app('adminAuth')->logout();
            $this->success('退出登录成功', 'Login/index', '', 1);
        }catch (Exception $e)
        {
            $this->error('退出登录失败,'.$e->getMessage());
        }
    }
}