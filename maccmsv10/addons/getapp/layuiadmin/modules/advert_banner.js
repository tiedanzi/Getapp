
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  //轮播图广告
  table.render({
    elem: '#LAY-advert-banner-list'
    , url: './view_advert_list_json?position=2' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' }
      , { field: 'name', title: '名称', width: 200 }
      , {
        field: 'status', title: '状态', templet: function (d) {
          if (d.status == 0) {
            return "禁用";
          }
          if (d.status == 1) {
            return "启用";
          }
          return "错误";
        }
      }
      , {
        field: 'req_content', title: '跳转链接', width: 200
      }
      , { field: 'position_text', title: '位置', width: 200 }
      , { field: 'ui_mode_text',  title: '模式', width: 200 }
      , { field: 'start_time',  title: '生效时间' , width: 200}
      , { field: 'end_time', title: '截止时间' , width: 200}
      , { field: 'sort',  title: '排序', width: 100 }
      , { title: '操作', Width: 250, align: 'center', fixed: 'right', toolbar: '#table-advert-banner-list' }
    ]]
    , page: true
    , limit: 10
    , limits: [10, 15, 20, 25, 30]
    , text: {
      none: '暂无相关数据'
    }
  });

  //监听工具条
  table.on('tool(LAY-advert-banner-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此广告？', function (index) {
        $.ajax({
          url: "advert_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-advert-banner-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'edit') {
      layer.open({
        type: 2
        , title: '编辑广告'
        , content: 'view_advert_form?id=' + data.id + '&key=banner'
        , maxmin: true
        , area: ['100%', '100%']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    } else if (obj.event === 'sort') {
      layer.open({
        type: 2
        , title: '修改排序'
        , content: 'view_advert_sort?id=' + data.id + '&key=banner'
        , maxmin: true
        , area: ['300px', '200px']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }
  });

  exports('advert_banner', {})
});
