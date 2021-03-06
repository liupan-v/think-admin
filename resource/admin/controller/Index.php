<?php
namespace app\admin\controller;

use app\admin\validate\AdminValidate;
use app\admin\util\ArrayToolkit;
use think\Db;
use think\Exception;

class Index extends Base
{
    public $noAuthAction = ['profile', 'myInfo'];
    public function index()
    {
        $auth = app('adminAuth');
        $userCount = '***';
        $visitCount = '***';
        $todayRegister = '***';
        $todayLogin = '***';
        $totalOrder =  '***';
        $agencyRecharge = '***';
        $todayPaidOrder = '***';
        $noPaidOrder = '***';
        $this->assign('userCount', $userCount);
        $this->assign('todayRegister', $todayRegister);
        $this->assign('visitCount', $visitCount);
        $this->assign('todayLogin', $todayLogin);
        $this->assign('totalOrder', $totalOrder);
        $this->assign('agencyRecharge', $agencyRecharge);
        $this->assign('todayPaidOrder', $todayPaidOrder);
        $this->assign('noPaidOrder', $noPaidOrder);
        $this->assign('canViwChart', $auth->check('dashboard/order_trend_chart'));
        $this->assign('canViwTable', $auth->check('dashboard/order_trend_table'));
        return $this->fetch();
    }

    public function profile()
    {
        if($this->request->isPost())
        {
            try{
                Db::startTrans();
                $adminId = request()->currentUser['id'];
                $adminModel = model('admin/Admin');
                $data = $adminModel->get($adminId);
                $param = input('post.');
                $validateRule = new AdminValidate();
                if (!$validateRule->scene('profile')->check($param))
                {
                    throw new Exception($validateRule->getError());
                }
                $oldPassword = isset($param['oldPassword']) ? $param['oldPassword'] : '';
                $password = isset($param['password']) ? $param['password'] : '';
                $updateData = ArrayToolkit::fields($param, ['nickname', 'mobile', 'qq', 'wechat', 'email']);
                if($password != '')
                {
                    if($oldPassword == '') throw new Exception('请输入原密码');
                    if($data['password'] != $adminModel->encryptPassword($oldPassword, $data['salt'])) throw new Exception('原密码不正确');
                }
                $rzt = $adminModel->allowField(true)->save($updateData, ['id' => $adminId]);
                if(!$rzt) throw new Exception('修改失败');
                if($password != '')
                {
                    $rzt = $adminModel->resetPassword($adminId, $password, $data['salt']);
                    if(!$rzt) throw new Exception('修改密码失败');
                }
                Db::commit();
                return $this->successReturn('修改成功');
            }catch (Exception $e)
            {
                Db::rollback();
                return $this->failReturn($e->getMessage());
            }
        }
        return $this->fetch();
    }


    public function myInfo()
    {
        try{
            $adminId = request()->currentUser['id'];
            $adminModel = model('admin/Admin');
            $data = $adminModel->get($adminId);
            if(empty($data))
                throw new Exception('获取信息失败');
            $this->successReturn('请求成功', $data);
        }catch (Exception $e)
        {
            $this->failReturn($e->getMessage());
        }
    }
}
