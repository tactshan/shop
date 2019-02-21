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


class AddMaterialController extends Controller
{
    use HasResourceActions;
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    /**
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
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
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
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WexinLasingMaterial);

        $grid->id('Id');
        $grid->openid('Openid');
        $grid->add_time('Add time');
        $grid->msg_type('Msg type');
        $grid->media_id('Media id');
        $grid->format('Format');
        $grid->msg_id('Msg id');
        $grid->file_name('File name');
        $grid->file_path('File path');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WexinLasingMaterial::findOrFail($id));

        $show->id('Id');
        $show->openid('Openid');
        $show->add_time('Add time');
        $show->msg_type('Msg type');
        $show->media_id('Media id');
        $show->format('Format');
        $show->msg_id('Msg id');
        $show->file_name('File name');
        $show->file_path('File path');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WexinLasingMaterial);

        $form->text('openid', 'Openid');
        $form->text('add_time', 'Add time');
        $form->text('msg_type', 'Msg type');
        $form->text('media_id', 'Media id');
        $form->text('format', 'Format')->default('1');
        $form->text('msg_id', 'Msg id');
        $form->text('file_name', 'File name');
        $form->textarea('file_path', 'File path');

        return $form;
    }

    //微信群发
    public function groupSending(Content $content)
    {
        return $content
            ->header('微信群发')
            ->description('description')
            ->body($this->group_sending_grid());
    }
    //视图显示
    public function group_sending_grid(){
        $form = new Form(new WexinLasingMaterial);
        $form->textarea('content', '群发内容');
        return $form;
    }
    //接收群发内容
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
