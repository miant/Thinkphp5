<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Validate;
// use app\admin\model\Event;

class Events extends Controller
{
	/**
	 * 展示品牌
	 * @return [type] [description]
	 */
	public function indexAction(){
		// $cond查询条件
		$cond = $filter = [];
		// $filter['filter_title'] = input('param.filter_title', '', 'trim');
		
		// if($filter['filter_title'] != ''){
		// 	$cond['title'] = ['like' , '%'.$filter['filter_title'].'%'];//适当考虑索引问题
		// }
		//分配筛选数据到模板 
		$this -> assign('filter',$filter);
		// 排序(考虑用户字段和排序方式)可考虑索引
		$order['field'] = input('param.field','event_id','trim');
		$order['type'] = input('param.type','asc','trim');

		$sort = [$order['field'] => $order['type']];
		
		// 分配排序数据
		$this -> assign('order',$order);
		
		$list = model('Event')
					->where($cond)
					->order($sort)
					->paginate(10);
		return view('index',['data'=>$list]);
	}
	/**
	 * 添加操作
	 */
	public function addAction(){
		if(request()->isPost()){
				$data = input('post.','','trim');
				// dump($data);die;
				$rules = [
					'title' => 'require',
					'sort_number' => 'number'	
				];
				$msg = [
					'title.require' => '名称必须填写',
					// 'title.unique' => '名称不能重复',
					'sort_number.number' => '排序必须为整数',
				];
				//调用静态方法实例化对象
				$validate =Validate::make($rules, $msg);
				$result = $validate->batch()->check($data);
				if(true !== $result){
					// 验证失败 输出错误信息
					$errors = $validate->getError();
					$this->error($errors);
				}
		
				// $res = Event::create($data);
				$res = model('Event')->save($data);	
				if($res){
					$this->redirect('index');
					
				}
				
		}else{
			return view();
		}
	}
	/**
	 * 编辑动作
	 */
	public function editAction()
	{
		$event_id =input('param.event_id','','trim');
		
		$this -> assign('row', model('Event')->find($event_id));
		// $this -> assign('row', Event::get($event_id));
		return view();
	}
	/**
	 * 更新操作
	 */
	public function updateAction()
	{
		$data = input('post.','','trim');
		// $result = Event::update($data);
		$result = model('Event')->update($data);
		if(!$result){
			$this -> error('数据更新失败:');
		}
		$this -> redirect('index');
	}
	/**
	 * 删除动作
	 */
	public function deleteAction()
	{
		//确定动作
		$operate = input('post.operate','delete','trim');
		//确定ID列表
		$selected = input('post.selected/a');
		 // 如果为空数组, 表示没有选择, 则立即跳转回列表页.
        if (empty($selected)) {
            $this->redirect('list', [], 0);
            return ;
        }
		switch($operate){
			case 'delete':
				// Event::destroy($selected);
				model('Event')->where('event_id','in',$selected)->delete();
				$this -> redirect('index');
				break;
			default:
				break;

		}
	}
	/**
	 * ajax验证
	 */
	public function ajaxAction(){
		 $operate = input('param.operate', null, 'trim');	
		if (is_null($operate)) {
			return;
		}
		switch($operate){
			//验证品牌唯一性名称操作
			case 'checkEventUnique':
			//获取填写的品牌名称 
				$title = input('request.title','');

				$cond['title'] = $title;
				// 判断是否传递了event_id
				$event_id = input('request.event_id',null);
				if(!is_null($event_id)) {
					//存在则匹配与当前记录不相同的ID
					$cond['event_id'] = ['neq',$event_id];
				}
				//获取模型后利用条件来查询匹配数
				$count = model('Event')->where($cond)->count();
				//如果记录大于零，则为真，说明存在记录，重复，验证未通过。响应false
				return $count ? false :true;
				break;
		}
	}
}