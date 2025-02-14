<?php
/*

 $goodsCategory = $this->goodsCategory->optionsForSelect();
	    foreach ($goodsCategory as $article){
			
			if($article->id>11){
			insert into  taobao_tbk_dg_material_optional (goods_category_id,start_dsr,page_size,page_no,platform,end_tk_rate,start_tk_rate,end_price,start_price,is_overseas,is_tmall,sort,itemloc,cat,q,has_coupon,ip,adzone_id,need_free_shipment,need_prepay,include_pay_rate_30,include_good_rate,include_rfd_rate,npx_level,created_at,updated_at) values 	
				$sql="({$article->id},5000,20,'',2,'',1234,'',5,false,false,null,null,{$article->id},'{$article->name}',true,null,49812664,false,false,null,null,null,1,'2019-06-24 09:10:55','2019-06-24 09:10:55'),";
            echo $sql;
			
			
			echo "update  goods_categorys   set image='/upload/images/goodsCategory/2019-06-22/{$article->id}.png' where id={$article->id};";
			echo '<br/>';
            }
        }
		
die;
var_dump($goodsCategory);
*/
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\GoodsCategoryService;
use Auth;

class GoodsCategoryController extends Controller
{
    protected $goodsCategory;
    protected $pageSize = 50;

    public function __construct(GoodsCategoryService $goodsCategory)
    {
      $this->middleware('auth');
      $this->goodsCategory = $goodsCategory;
    }

    public function index()
    {
      $this->isAdmin();
      $title = '商品分类列表';
      $goodsCategories = $this->goodsCategory->get($this->pageSize);
      $couponRules = $this->goodsCategory->manyCouponRules($goodsCategories);

      return view('admin.goodsCategory.index', compact('title', 'goodsCategories', 'couponRules'));
    }

    public function show($id)
    {
      $this->isAdmin();
      $title = '商品分类信息详情';
      $goodsCategory = $this->goodsCategory->find($id);

      if (!$goodsCategory) {
        return redirect()->route('goodsCategorys.index')->with('warning', '要查看的信息不存在');
      }

      return view('admin.goodsCategory.show', compact('goodsCategory', 'title'));
    }

    public function create()
    {
      $this->isAdmin(); 
	
      $title = '增加栏目分类';
      $options = $this->goodsCategory->optionsForSelect();

      return view('admin.goodsCategory.create', compact('title', 'options'));
    }

    public function store(Request $request)
    {
      $this->validate($request, [
        'name' => 'required|min:1|max:30',
        'parent_id' => 'required|min:1',
        'order' => 'required|min:0|max:99',
        'is_shown' => 'required|boolean',
        'is_recommended' => 'required|boolean',
        'font_icon' => 'nullable|min:6|max:250',
        'image' => 'required|image',
      ]);

      $this->isAdmin();

      if ($this->goodsCategory->store($request)) {
        return back()->with('success', '成功增加栏目分类！');
      } else {
        return back()->with('warning', '增加栏目分类失败！');
      }
    }

    public function edit($id)
    {
      $this->isAdmin();
      $title = '修改商品分类信息';
      $goodsCategory = $this->goodsCategory->find($id);

      if (!$goodsCategory) {
        return redirect()->route('goodsCategorys.index')->with('warning', '准备删除的信息不存在');
      }

      $options = $this->goodsCategory->optionsForSelect();

      return view('admin.goodsCategory.edit', compact('title', 'goodsCategory', 'options'));
    }

    public function update($id, Request $request)
    {
      $this->isAdmin();

      $this->validate($request, [
        'name' => 'required|min:1|max:30',
        'parent_id' => 'required|min:1',
        'order' => 'required|min:0|max:99',
        'is_shown' => 'required|boolean',
        'is_recommended' => 'required|boolean',
        'font_icon' => 'nullable|min:6|max:250',
        'image' => 'nullable|image',
      ]);

      $result = $this->goodsCategory->updateById($id, $request);

      if (!$result) {
        return redirect()->route('goodsCategorys.index')->with('warning', '更新信息失败，请重新操作！');
      }

      return back()->with('info', '更新信息成功！');
    }

    public function destroy(Request $request)
    {
      $this->isAdmin();

      if (empty($request->goods_category_id)) {
        return redirect()->route('goodsCategorys.index')->with('warning', '删除信息失败，请重新操作！');
      }

      $result = $this->goodsCategory->destroyById($request->goods_category_id);

      if (!$result) {
        return back();
      }

      return back()->with('success', '成功删除id为'.$request->goods_category_id.'的商品分类信息！');
    }

    // 判断是否是管理员
    public function isAdmin()
    {
      $this->authorize('isAdmin', Auth::user());
    }
}
