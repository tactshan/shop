<?php

namespace App\Admin\Controllers;

use App\Model\WeixinUser;
use App\Model\WexinLasingMaterial;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;


class AddMaterialController extends Controller
{
    use HasResourceActions;
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    /**
     * index素材首页
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('微信永久素材管理中心')
            ->description('永久素材列表')
            ->body($this->grid());
    }

    /**
     * show详情
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('详情')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * edit修改
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    protected function edit($id,Content $content)
    {
        return $content
            ->header('修改')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * create添加
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->addMaterial());
    }

    /**
     * grid类表展示表单
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WexinLasingMaterial);

        $grid->id('Id');
        $grid->media_id('Media id');
        $grid->material_url('Material url')->display(function ($material_url){
            return "<img src=".$material_url." style='width:100px heigth:100px'>";
        });
        $grid->add_time('Add time');

        return $grid;
    }

    /**
     * detail修改表单
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WexinLasingMaterial::findOrFail($id));

        $show->id('Id');
        $show->media_id('Media id');
        $show->material_url('Material url');
        $show->add_time('Add time');

        return $show;
    }

    /**
     * 添加表单
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WexinLasingMaterial);

        $form->text('media_id', 'media_id');
        $form->text('material_url', 'material_url');
        $form->text('add_time', 'add_time');
        return $form;
    }

    /**
     * 素材添加视图
     * @return Form
     */
    public function addMaterial(){
        $form = new Form(new WexinLasingMaterial);
        $form->image('media', '素材');
        return $form;
    }

    /**
     * 接收素材
     * @param Request $request
     */
    public function getMaterial(Request $request){
        $material=$request->file('media');
        //获取文件名称
        $file_name=$material->getClientOriginalName();
        //获取文件扩展名
        $file_ext=$material->getClientOriginalExtension();
        //文件重命名
        $file_new_name=str_random(15). '.'.$file_ext;
        //保存文件
        $save_file_path = $request->media->storeAs('material_images',$file_new_name);       //返回保存成功之后的文件路径

        //将图片上传至永久素材
        $save_lasing_material_data=$this->save_lasing_material($save_file_path);
        //将数据保存到数据库
        $res=$this->saveMaterialDataDb($save_lasing_material_data);
        if($res){
            echo '上传成功';exit;
        }else{
            echo '上传失败';exit;
        }
    }

    /**
     * 上传永久素材
     * @param $file_path
     * @return mixed
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function save_lasing_material($file_path){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        //拼接url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$access_token.'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'media',
                    'contents' => fopen($file_path, 'r')
                ],
            ]
        ]);
        $body = $response->getBody();
        $data = json_decode($body,true);
        return $data;
    }

    /**
     * 将上传永久素材后的数据保存到数据库
     * @param $data
     * @return bool
     */
    public function saveMaterialDataDb($data){

        $insertData=[
          'media_id'=>$data['media_id'],
            'material_url'=>$data['url'],
            'add_time'=>time()
        ];
        $res=WexinLasingMaterial::insertGetId($insertData);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取素材列表
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function getMaterialList(){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$access_token;
//调用微信接口
        $client=new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "type"=>'image',
            "offset"=>0,
            "count"=>10,
        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

//解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        var_dump($request_arr);die;
    }

    /**
     * 微信群发
     * @param Content $content
     * @return Content
     */
    public function groupSending(Content $content)
    {
        return $content
            ->header('微信群发')
            ->description('description')
            ->body($this->group_sending_grid());
    }

    /**
     * 群发视图显示
     * @return Form
     */
    public function group_sending_grid(){
        $form = new Form(new WexinLasingMaterial);
        $form->textarea('content', '群发内容');
        return $form;
    }

    /**
     * 接收群发内容
     * @param Request $request
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function group_content(Request $request){
        $group_content=$request->input('content');
        //获取access_token
        $access_token=$this->getWXAccessToken();
        //拼接url
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        //拼接数据
        $userInfo=WeixinUser::all()->toArray();
        foreach ($userInfo as $k=>$v){
            $openid[]=$v['openid'];
        }
        $data=[
            'touser'=>$openid,
            "msgtype"=>"text",
            "text"=>["content"=>$group_content],
        ];
        $res=$client->request('POST', $url, ['body' => json_encode($data,JSON_UNESCAPED_UNICODE)]);
        $res_arr=json_decode($res->getBody(),true);
        if($res_arr['errcode']==0){
            echo '群发成功';
        }else{
            echo '群发失败！错误码'.$res_arr['errmsg'];
        }
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {
        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }
}
